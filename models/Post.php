<?php

/**
 * Representa una noticia concreta.
 */
class Post
{
    ## Almaceno el dominio para componer url's.
    private $domain = null;

    ## El id del post en la aplicación web.
    public $id;

    ## Noticia Padre.
    public $parent_id = null;

    ## Posición del lote de respuesta.
    public $position = 0;

    public $title = '';
    public $description = '';
    public $content = '';
    public $url = '';

    ## Url hacia la imagen de portada.
    public $image = '';

    ## Indica el tipo mime de la imagen.
    public $image_type = '';

    ## Información sobre el autor de la noticia.
    public $author = [
        'id' => null,  ## El id en la web para este usuario.
        'name' => null,  ## Nombre completo del autor.
        'username' => null, ## Nombre de usuario en la web.
        'email' => null,  ## Correo del autor.
        'web' => null,  ## Web personal del autor.
        'url' => null,  ## URL hacia el perfil en la web.
        'registered_at' => null,  ## Fecha del registro.
    ];

    ## Categorías, array de instancias de Category(). En futuro tendrá varias.
    public $categories = [];

    ## Información sobre el origen de la noticia, la web o proyecto en sí.
    public $source = [
        'domain' => null,  // Dominio.
        'code' => null,  // Código que identifica el sitio web.
        'name' => null,  // Nombre del sitio web.
        'home' => null,  // Url a la página principal del sitio.
        'url' => null,  // Url completa hacia la web.
        'description' => null,  // Descripción del sitio web.
        'admin_email' => null,  // Correo del administrador.
    ];

    ## Indica si tiene habilitados los comentarios.
    public $has_enabled_comments = false;

    ## Cantidad de comentarios en la noticia.
    public $n_comments = 0;

    public $created_at, $updated_at, $published_at = null;

    public function __construct(Array $post = [], $position = null)
    {
        if (isset($post) && count($post)) {
            $this->id = isset($post['ID']) ? (int)$post['ID'] : null;
            $this->domain = isset($post['domain']) ? $post['domain'] : null;
            $this->parent_id = isset($post['post_parent']) &&
                               ($post['post_parent'] > 0) ? $post['post_parent'] :
                               null;
            $this->title = isset($post['post_title']) ?
                           cleanText($post['post_title']) :
                           null;
            $this->content = isset($post['post_content']) ?
                             cleanWithHtmlEntity($post['post_content']) :
                             null;
            $this->image = isset($post['image']) ?
                                utf8_encode($post['image']) :
                                null;
            $this->image_type = isset($post['image_type']) ?
                                utf8_encode($post['image_type']) :
                                null;
            $this->has_enabled_comments = isset($post['comment_status']) &&
                                          ($post['comment_status'] === 'open') ?
                                          true :
                                          false;
            $this->n_comments = isset($post['comment_count']) ?
                                (int)$post['comment_count'] :
                                0;
            $this->created_at = isset($post['post_date_gmt']) ?
                                $post['post_date_gmt'] :
                                generateTimestampString();
            $this->updated_at = isset($post['post_modified_gmt']) ?
                                $post['post_modified_gmt'] :
                                generateTimestampString();
            $this->published_at = isset($post['post_modified_gmt']) ?
                                  $post['post_modified_gmt'] :
                                  generateTimestampString();
            $this->description = isset($post['post_excerpt']) &&
                                 $post['post_excerpt'] != '' ?
                                 $post['post_excerpt'] :
                                 generateDescription($post['post_content']);

            $this->slug = isset($post['post_name']) ?
                          parseSlug($post['post_name']) :
                          null;
            $this->url = $this->generateUrl($post['guid']);


            ## Autor.
            $this->author['id'] = isset($post['user_id']) ?
                                  (int)$post['user_id'] :
                                  null;
            $this->author['name'] = isset($post['user_displayname']) ?
                                  cleanText($post['user_displayname']) :
                                  null;
            $this->author['username'] = isset($post['user_username']) ?
                                  cleanText($post['user_username']) :
                                  null;
            $this->author['email'] = isset($post['user_email']) ?
                                  cleanText($post['user_email']) :
                                  null;
            $this->author['web'] = isset($post['user_web']) ?
                                  cleanText($post['user_web']) :
                                  null;
            $this->author['registered_at'] = isset($post['user_registered_at']) ?
                                  $post['user_registered_at'] :
                                  null;
            $this->author['url'] = $this->generateAuthorUrl();


            ## Categoría.
            $this->categories = [
                new Category($post),
            ];


            ## Source (Origen de la noticia).
            $this->source = [
                'domain' => isset($post['domain']) ? $post['domain'] : null,
                'code' => isset($post['domain']) ? md5($post['domain']) : null,
                'name' => isset($post['site_name']) ? cleanText($post['site_name']) : null,
                'home' => isset($post['site_home']) ? $post['site_home'] : null,
                'url' => isset($post['site_url']) ? cleanText($post['site_url']) : $this->generateUrl(''),
                'description' => isset($post['site_description']) ?
                                 cleanText($post['site_description']) :
                                 null,
                'admin_email' => isset($post['site_admin_email']) ?
                                 cleanText($post['site_admin_email']) :
                                 null,
            ];
        }

        if ($position) {
          $this->position = $position;
        }
    }

    /**
     * Regenera la URL a partir del slug y el dominio.
     * @return String Devuelve la url generada.
     */
    private function generateUrl($slug)
    {
        if (isset($this->domain) &&
            $this->domain &&
            isset($this->slug) &&
            $this->slug) {
            return 'https://' . $this->domain . '/' . $this->slug;
        }

        return null;
    }

    /**
     * Genera la URL hacia el autor de la publicación.
     * @param  String $username Nombre del usuario.
     * @return String           URL hacia el perfil del autor.
     */
    public function generateAuthorUrl()
    {
        if (isset($this->domain) &&
            $this->domain &&
            isset($this->author['username']) &&
            $this->author['username']) {
            return 'https://' .
                    $this->domain .
                    '/author/' .
                    $this->author['username'];
        }

        return null;
    }
}
?>
