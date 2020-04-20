<?php
## Cabeceras para la respuesta.
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 'on');

## Incluye validación y  log de la request antes de continuar.
//require '../includes/request_validation.php';

require '../../wp-load.php';

## Funciones auxiliares.
require '../includes/functions.php';

## Modelo para la base de datos.
require '../models/DbConnection.php';

## Modelo para las categorías.
require '../models/Category.php';

## Modelo para los posts.
require '../models/Post.php';

## Modelo para los comentarios.
require '../models/Comment.php';

## Modelo para los errores.
require '../models/ErrorRequest.php';

/**
 * Valida la petición a esta API para permitir devolver los datos.
 * @param  [type] $REQUEST_METHOD [description]
 * @return [type]                 [description]
 */
function validateRequest($REQUEST_METHOD = null)
{
    // if ($REQUEST_METHOD === 'GET') {
    if (true) {
        return true;
    }

    return false;
}

/**
 * Prepara una respuesta con error, ya sea por autenticación o externo.
 * @return [type] [description]
 */
function errorResponse()
{
    return [
        'error' => '???',
    ];
}

## Aquí valido si tiene permisos para trabajar con esta API y devuelve datos.
if (validateRequest(isset($REQUEST_METHOD) ? $REQUEST_METHOD : null)) {
    ## Instancio objeto con datos de Aplicación.
    $DB = new DbConnection([
        'DB_NAME' => DB_NAME,
        'DB_HOST' => DB_HOST,
        'DB_PORT' => defined('DB_PORT') ? DB_PORT : '3306',
        'DB_USER' => DB_USER,
        'DB_CHARSET' => DB_CHARSET,
        'DB_COLLATE' => DB_COLLATE,
        'DB_PASSWORD' => DB_PASSWORD,
        'OPTIONS' => [],
        'TABLE_PREFIX' => $table_prefix,
    ]);

    //$categories = $DB->getCategories();
    $posts = $DB->getPosts();
    //$postByCategory = $DB->getPostsByCategory(491);  // 490-Seguridad, 491-Linux

    /*
    echo json_encode([
        'status' => 'ok',
        'results' => count($categories['data']),  ## Resultados que se envían
        'totalResults' => $categories['results'],  ## Total de resultados en DB
        'data' => $categories['data']
    ]);
    */

    /*
    echo json_encode([
        'status' => 'ok',
        'results' => count($postByCategory['data']),  ## Resultados que se envían
        'totalResults' => $postByCategory['results'],  ## Total de resultados en DB
        'data' => $postByCategory['data']
    ]);
    */

    echo json_encode([
        'status' => 'ok',
        'results' => $posts['results'],  ## Resultados que se envían
        'totalResults' => $posts['totalResults'],  ## Total de resultados en DB
        'data' => $posts['data']
    ]);

    $DB->close();
} else {
    echo json_encode('NO VALIDA');
}

die();

?>
