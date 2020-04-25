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
function validateRequest($method = null)
{
    // TODO → Extraer el token de la aplicación y track de git cuando
    // establezca la versión para producción.

    $token = '4e91d347780812cfa0d2dc38ed115c8e79493a24afec5aeed56ed90b52ae4976';

    if (!isset($_GET['token']) || ($_GET['token'] != $token)) {
        //return false;
    }

    // if ($method === 'GET') {
    if (true) {
        return true;
    }

    return false;
}

/**
 * Prepara una respuesta con error, ya sea por autenticación o externo.
 * @return [type] [description]
 */
function errorResponse($msg = 'En estos momentos no se pueden obtener los datos')
{
    return [
        'status' => 'ko',
        'results' => 0,
        'total_results' => 0,
        'data' => [],
        'message' => $msg,
    ];
}

## Aquí valido si tiene permisos para trabajar con esta API y devuelve datos.
if (validateRequest(isset($REQUEST_METHOD) ? $REQUEST_METHOD : null)) {
    ## Instancio objeto con datos de Aplicación.

    try {
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
    } catch (\Exception $e) {
        return json_encode(error_response('Problema con la DB'));
        die();
    }

    if (! $DB) {
        return json_encode(error_response('Problema con la DB'));
        die();
    }




    // TODO → COmprobar parámetros por $_GET[] en la conexión a la db.




    ## Búsqueda por título o contenido.
    if (isset($_GET['search'])) {
        $DB->search = $_GET['search'];
    }

    ## Página de contenido (paginado según el límit por petición).
    if (isset($_GET['page'])) {
        $DB->page = $_GET['page'];
    }

    ## Categoría de la que traer los posts.
    if (isset($_GET['category'])) {
        $DB->category = $_GET['category'];
    }

    ## Devuelve las categorías.
    if (isset($_GET['categories']) && ($_GET['categories'] === 'true')) {
        $DB->limit = 200;
        $categories = $DB->getCategories();

        if (! $categories) {
            return json_encode(error_response('No hay Categorías'));
        }

        echo json_encode([
            'status' => 'ok',
            'results' => count($categories['data']),  ## Resultados que se envían
            'total_results' => $categories['results'],  ## Total de resultados en DB
            'data' => $categories['data']
        ]);

        die();
    }

    ## Devuelve los posts filtrados y paginado por una categoría concreta.
    if ($DB->category) {
        $postByCategory = $DB->getPostsByCategory();

        if (! $postByCategory) {
            return json_encode(error_response('Error al obtener posts para esta categoría'));
        }

        echo json_encode([
            'status' => 'ok',
            'results' => count($postByCategory['data']),  ## Resultados que se envían
            'total_results' => $postByCategory['results'],  ## Total de resultados en DB
            'data' => $postByCategory['data']
        ]);

        die();
    }

    ## Devuelve los posts filtrados y paginado.
    if (true) {
        $posts = $DB->getPosts();

        if (! $posts) {
            return json_encode(error_response());
        }

        echo json_encode([
            'status' => 'ok',
            'results' => $posts['results'],  ## Resultados que se envían
            'total_results' => $posts['totalResults'],  ## Total de resultados en DB
            'data' => $posts['data']
        ]);

        die();
    }

    $DB->close();
} else {
    echo json_encode(errorResponse());
}

die();

?>
