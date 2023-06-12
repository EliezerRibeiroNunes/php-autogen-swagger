<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Routing\Route;

class GenAnnotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gen-annotations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        $controllersDir = app_path('Http/Controllers');
        $controllerFiles = glob($controllersDir . '/*.php');

        foreach ($controllerFiles as $controllerFile) {
            $controllerClassName = 'App\\Http\\Controllers\\' . basename($controllerFile, '.php');
            $controllerClass = new ReflectionClass($controllerClassName);

            if ($controllerClass->isAbstract()) {
                continue;
            }

            foreach ($controllerClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $controllerClassName) {
                    continue;
                }

                $routeAnnotation = $this->getRouteAnnotation($method);
                $this->addAnnotationToMethod($method, $routeAnnotation);
            }
        }

        $this->info('Swagger annotations created successfully!');
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

    protected function getRouteAnnotation(ReflectionMethod $method)
    {
        $routes = app('router')->getRoutes()->getRoutes();

        $route = collect($routes)->first(function (Route $route) use ($method) {
            return $route->getActionName() === $method->class . '@' . $method->name;
        });

        if (!$route) {
            return null;
        }
        $httpMethod = strtolower($route->methods()[0]);

        $routePath = $route->uri();
        $tag             = $routePath;
        //$properties      = $this->setSwaggerProperties($rules);
        //$method          = $this->extractMethod($route->methods());
        $parameter       = $this->setSwaggerParameter($routePath);
        $responseCode    = $this->setResponseCode($method);
        //$refactorMethod  = ucfirst(strtolower($method));


        $summaryArray    = explode("\\", $routePath);
        $summary = end($summaryArray);

        $content = <<<PHP
        @OA\\$httpMethod(
            path="$routePath",
            summary="$summary",
            tags={"$tag"},
            $parameter
            @OA\\Response(
                response="$responseCode - $summary",
                description="Successful operation",
            ),
            security={{"bearerAuth": {}}},
        ),
        PHP;

        $formattedContent = preg_replace('/^(?!\/\*\*)/m', ' * ', $content);
        return $formattedContent;
    }

    protected function addAnnotationToMethod(ReflectionMethod $method, $annotation)
    {
        $content = file_get_contents($method->getFileName());

        $annotationLine = "/**\n  $annotation\n */\n";
        $content = preg_replace(
            '/(public function ' . $method->name . '\([^\{]+\{)/',
            "$annotationLine$1",
            $content
        );

        file_put_contents($method->getFileName(), $content);
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

    private function extractMethod(array $data)
    {
        return current($data);
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
