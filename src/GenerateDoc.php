<?php

namespace AutoGen;

use Illuminate\Support\Facades\Route;
use ReflectionClass;

/**
 * @OA\Info(title="API DOCUMENTATION", version="0.1")
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * ),
 *
 * @OA\Security(
 *     security={{"bearerAuth": {}}}
 * )
 */

class GenerateDoc
{
    const DEFAULT_MODULES_PATH = 'App';

    public function generate()
    {
        $routeCollection = Route::getRoutes();

        if ($routeCollection) {
            foreach ($routeCollection as $route) {
                $action = $route->getActionName();
                $swaggerAnnotation = '';
                $rules = [];

                if (str_contains($action, self::DEFAULT_MODULES_PATH)) {
                    $classObject = app($action);

                    if (method_exists($classObject, 'rules')) {
                        $rules = $classObject->rules();
                    }

                    $swaggerAnnotation .= $this->setContent($route, $rules, $action);
                    $this->addSwaggerAnnotationToActionClass($classObject, $swaggerAnnotation);

                    print("$action - completed successfully!\n");
                }
            }
        } else {
            print("No routes found !!");
        }
    }

    private function setContent($route, array $rules, string $action)
    {
        $routePath       = "/" . $route->uri();
        $tag             = explode("/", $routePath)[1];
        $properties      = $this->setSwaggerProperties($rules);
        $method          = $this->extractMethod($route->methods());
        $parameter       = $this->setSwaggerParameter($routePath);
        $responseCode    = $this->setResponseCode($method);
        $refactorMethod  = ucfirst(strtolower($method));

        $summaryArray    = explode("\\", $action);
        $summary = end($summaryArray);

        $content = <<<PHP
        @OA\\$refactorMethod(
            path="$routePath",
            summary="$summary",
            tags={"$tag"},
            $parameter
            $properties
            @OA\\Response(
                response="$responseCode - $summary",
                description="Successful operation",
            ),
            security={{"bearerAuth": {}}},
        ),
        PHP;

        $formattedContent = preg_replace('/^(?!\/\*\*)/m', ' * ', $content);
        $annotation = "/**\n" . $formattedContent . "\n */";

        return $annotation;
    }

    private function addSwaggerAnnotationToActionClass($classObject, string $swaggerAnnotation)
    {
        $classFileName = (new ReflectionClass($classObject))->getFileName();

        if ($classFileName) {
            $classCode = file_get_contents($classFileName);

            $existingAnnotation = '/\/\*\*(.*?)\*\//s';
            preg_match($existingAnnotation, $classCode, $matches);

            $classCode = str_replace($matches[0] ?? null, $swaggerAnnotation, $classCode);
            file_put_contents($classFileName, $classCode);
        }
    }

    private function setSwaggerParameter(string $routePath)
    {
        $parameter = "";

        $pattern = '/\{([^{}]+)\}/';
        preg_match($pattern, $routePath, $matches);

        if (!empty($matches[1])) {
            $parameterName = $matches[1];
            $defaultParameterType = $parameterName == 'id' ? 'type="integer", format="int64"' : 'type="string"';

            $parameter .= <<<PHP
            @OA\\Parameter(
                name="$parameterName",
                in="path",
                description="Parameter of route",
                required=true,
                @OA\\Schema($defaultParameterType)
            ),
        PHP;
        }
        return $parameter;
    }

    private function setSwaggerProperties($rules)
    {
        $properties = "";
        $requestBodyAnnotation = "";

        if (!empty($rules)) {
            foreach ($rules as $property => $type) {

                if (is_array($type)) {
                    $propertyType = $type[0];
                } else {
                    $propertyType = $this->extractType($type);
                }
                $properties .= str_repeat(" ", 12) . "@OA\\Property(property=\"$property\", type=\"$propertyType\"),\n";
            }

            $requestBodyAnnotation .= <<<PHP
                @OA\\RequestBody(
                    required=true,
                    @OA\\JsonContent(
            $properties
                    )
                ),
            PHP;
        }
        return $requestBodyAnnotation;
    }

    private function extractType($type)
    {
        $types = explode("|", $type);
        $firstType = current($types);

        if ($firstType == "required" && isset($types[1])) {
            return $types[1];
        }
        return $firstType;
    }

    private function extractMethod(array $data)
    {
        return current($data);
    }

    private function getActionRules($classObject)
    {
        if (method_exists($classObject, 'rules')) {
            return $classObject->rules();
        }
    }

    private function setResponseCode(string $method)
    {
        switch ($method) {
            case 'POST':
                $responseCode = "201";
                break;
            default:
                $responseCode = "200";
                break;
        }
        return $responseCode;
    }
}
