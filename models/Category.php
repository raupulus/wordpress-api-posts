<?php
/**
 * Representa las categorías de los posts.
 */
class Category
{
    public $id, $slug, $name, $description, $url = null;

    public function __construct($params)
    {
        $this->id = isset($params['category_id']) ?
                                (int)$params['category_id'] :
                                null;
        $this->slug = isset($params['category_slug']) ?
                                  utf8_encode($params['category_slug']) :
                                  null;
        $this->name = isset($params['category_name']) ?
                                  cleanText($params['category_name']) :
                                  null;
        $this->description = isset($params['category_description']) ?
                              cleanText($params['category_description']) :
                              null;
        $this->url = isset($params['domain']) ?
                           $this->generateUrl($params['domain']) :
                           null;
    }

    /**
     * Regenera la URL a partir del slug y el dominio.
     * @return String Devuelve la url generada.
     */
    private function generateUrl($domain)
    {
        if (isset($domain) &&
            $domain &&
            isset($this->slug) &&
            $this->slug) {
            return 'https://' . $domain . '/category/' . $this->slug;
        }

        return null;
    }
}

?>
