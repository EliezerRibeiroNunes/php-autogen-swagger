# PHP-AUTOGEN-SWAGGER

Gerador de documentação de API automática utilizando a biblioteca darkaonline/l5-swagger

# REQUISITOS 
https://github.com/DarkaOnLine/L5-Swagger/wiki/Installation-&-Configuration

## INSTALAÇÃO

    composer require php-autogen/swagger

## UTILIZAÇÃO

### Passo 01:

    No framework Laravel rode o comando:

    php artisan gen-swagger-doc

### Passo 02:

    As "Rules" das "Actions" ou "Controllers" devem seguir o mesmo padrão para que funcione com exito:

        public function rules()
        {
            return [
                'password' => 'required',
                'email'      => 'required|string'
            ];
        }
### Passo 03:

    

### Passo 04:

     Adicione a seguinte anotação "INFO" dentro de alguma anotação criada automaticamente

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
    *

    EXEMPLO:
    /**
    *
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
    *
    * @OA\Post(
    *     path="/auth",
    *     summary="Authenticate",
    *     tags={"auth"},
    *
    *         @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="password", type="required"),
    *             @OA\Property(property="cpf", type="required"),
    *
    *         )
    *     ),
    *     @OA\Response(
    *         response="201 - Authenticate",
    *         description="Successful operation",
    *     ),
    *     security={{"bearerAuth": {}}},
    * ),
    */
