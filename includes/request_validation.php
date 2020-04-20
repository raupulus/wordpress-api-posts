<?php
## Método de la petición.
$REQUEST_METHOD = $_REQUEST['REQUEST_METHOD'];

## Nombre del servidor.
$SERVER_NAME = $_REQUEST['SERVER_NAME'];

## Dominio.
$HTTP_HOST = $_REQUEST['HTTP_HOST'];

## Navegador del visitante.
$HTTP_USER_AGENT = $_REQUEST['HTTP_USER_AGENT'];

## IP del visitante
$REMOTE_ADDR = $_REQUEST['REMOTE_ADDR'];

## El puerto empleado por la máquina del visitante para comunicarse con el servidor web.
$REMOTE_PORT = $_REQUEST['REMOTE_PORT'];

## El valor dado a la directiva SERVER_ADMIN (de Apache).
$SERVER_ADMIN = $_REQUEST['SERVER_ADMIN'];

## Puerto del servidor.
$SERVER_PORT = $_REQUEST['SERVER_PORT'];

## La URI que se empleó para acceder a la página. Por ejemplo: '/api.php'.
$REQUEST_URI = $_REQUEST['REQUEST_URI'];

?>
