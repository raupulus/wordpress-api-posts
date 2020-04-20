<?php
/**
 * Clase para gestionar los errores
 */
class ErrorRequest
{
    public $PATH_FILE_ERRORS = './errors.log';

    ## Almacena los errores que se produzcan al validar.
    public $errors = [];


    public function __construct($mesagge = null)
    {

    }

    /**
     * Error con el token de autenticación.
     * @return [type] [description]
     */
    public function token()
    {

    }

    /**
     * Error con el método de la petición.
     * @return [type] [description]
     */
    public function method()
    {

    }
}

?>
