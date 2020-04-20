<?php
/**
 * Limpia el texto recibido de carácteres especiales, textos raros,
 * espacios duplicados, carácteres en idiomas extraños, codificaciones
 * en html (html entity), espacio al inicio y final...
 *
 * @param  String $text Cadena de texto a limpiar.
 * @return String       Devuelve la cadena de texto una vez limpia.
 */
function cleanText($text)
{
    ## Limpio espacios al principio y final.
    $parseText = trim($text);

    ## Convierto entidades HTML especiales de nuevo en caracteres.
    $parseText = htmlspecialchars_decode($parseText);

    ## Convierto todas las entidades HTML a sus caracteres correspondientes
    $parseText = html_entity_decode($parseText);

    ## Retiro las etiquetas HTML y PHP de un string.
    $parseText = strip_tags($parseText);

    ## Fuerzo conversión a utf8.
    $parseText = utf8_encode($parseText);

    ## Limpio saltos de líneas y tabulaciones.
    $parseText = preg_replace("/[\r\n|\n|\r]+/", " ", $parseText);

    ## Vuelvo a limpiar espacios al principio y final.
    $parseText = trim($parseText);

    return $parseText;
}

/**
 * Limpia el texto de forma menos agresiva respecto al html que la función
 * cleanText() de este modelo.
 *
 * @param  String $text Cadena con todo el contenido HTML.
 * @return String       Devuelve el HTML saneado.
 */
function cleanWithHtmlEntity($text)
{
    $parseText = trim($text);

    ## Fuerzo conversión a utf8.
    $parseText = utf8_encode($parseText);

    ## Limpio saltos de líneas y tabulaciones.
    $parseText = preg_replace("/[\r\n|\n|\r\t]+/", "", $parseText);

    ## Permito solo etiquetas básicas.
    $htmlTags = '<p><a><img><ul><li><ol><pre><br><code><h1><h2><h3><h4><h5><h6>';
    $parseText =  strip_tags($parseText, $htmlTags);

    ## Convierto todos los caracteres aplicables a entidades HTML.
    // TODO → Revisar esto si es necesario, parece hacer cosas raras.
    //$parseText = htmlEntities($parseText, ENT_QUOTES);

    ## Vuelvo a limpiar espacios al principio y final.
    $parseText = trim($parseText);

    return $parseText;
}

/**
 * Sanéa carácteres extraños que pudiera tener el slug.
 *
 * @param  String $text Cadena a parsear para tener formato slug.
 * @return String       Cadena con formato slug.
 */
function parseSlug($text)
{
    $parseText = cleanText($text);

    ## Reemplazo todos los espacios en blanco.
    $parseText = str_replace(' ', '-', $parseText);

    ## Solo permito carácteres y números.
    $parseText = preg_replace('/[^A-Za-z0-9\-]/', '', $parseText);

    ## Convierto todo a minúsculas.
    //$parseText = mb_strtolower($parseText, 'UTF-8');

    return $parseText;
}

/**
 * Genera una descripción corta a partir del contenido recibido, útil si no
 * existe una descripción predefinida.
 * @param  String $text Contenido para extraer el comienzo.
 * @return String       Devuelve el texto extraido y saneado.
 */
function generateDescription($text)
{
    $parseText = cleanText($text);

    ## Me quedo con los 200 primeros carácteres de la entrada.
    $parseText = substr($parseText, 0, 200);

    ## Capitalizo la cadena.
    $parseText = ucfirst($parseText);

    return $parseText;
}

/**
 * Genera un Timestamp del momento actual y lo devuelve formateado en español.
 * @param  String $format Formato a convertir.
 * @return String Cadena con el tiempo actual.
 */
function generateTimestampString($format = 'd/m/Y H:i:s')
{
    try {
        return (new \DateTime('NOW'))->format($format);
    } catch (\Exception $e) {}

    return null;
}
?>
