<?php
/**
 * Representa la conexión con la db.
 */

 error_reporting(E_ALL);
 ini_set('display_errors', 'on');


/**
 * Clase que representa la conexión con la db y devuelve las colecciones de
 * consultas con instancias de sus modelos.
 *
 * TODO → Refactorizar getPostsByCategory() que tomo los datos copiando de
 * getPosts() por falta de tiempo queda pendiente.
 */
class DbConnection
{
    ## Almacena la conexión con la DB.
    private $dbh = null;

    ## Prefijo para las tablas, tenerlo en cuenta para las consultas.
    public $table_prefix = 'wp_';

    public $domain = null;
    public $site_name = null;
    public $site_url = null;
    public $site_home = null;
    public $site_description = null;
    public $site_admin_email = null;

    ## Parámetros para conectar.
    private $params = [
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_NAME' => 'database',
        'DB_USER' => 'admin',
        'DB_CHARSET' => 'utf8mb4',
        'DB_COLLATE' => '',
        'DB_PASSWORD' => '',
        'OPTIONS' => [PDO::ATTR_PERSISTENT => true],
        'TABLE_PREFIX' => 'wp_',
    ];

    ## Página solicitada.
    public $page = 1;

    ## Cantidad elementos.
    public $quantity = 20;

    public function __construct($params = [])
    {
        ## Mezclo parámetros de conexión para la DB con los internos.
        $this->params = array_merge($this->params, $params);

        ## Establezco el prefijo de la tabla.
        $this->table_prefix = $this->params['TABLE_PREFIX'];

        ## Creo la conexión con la DB y la almaceno.
        $try = 0;
        do {
            $try++;
            $this->dbh = $this->connect();
            usleep(80);  ## Pausa en milisegundos.
        } while (($this->dbh === null) && ($try <= 10));

        ## Asigno el dominio dónde se aloja la api
        $this->domain = $_SERVER['HTTP_HOST'];

        ## Consulto la tabla de configuraciones para obtener datos de wordpress.
        $table_config = $this->table_prefix . 'options';
        $query = <<<EOL
            SELECT
                option_name,
                option_value
            FROM $table_config
            WHERE option_name
                IN (
                    "siteurl",
                    "home",
                    "blogname",
                    "blogdescription",
                    "admin_email"
                )
EOL;
        $configurations = $this->execute($query, false);

        foreach ($configurations as $config) {
            $name = $config['option_name'];

            switch ($name) {
                case 'siteurl':
                    $this->site_url = $config['option_value'];
                    break;
                case 'home':
                    $this->site_home = $config['option_value'];
                    break;
                case 'blogname':
                    $this->site_name = $config['option_value'];
                    break;
                case 'blogdescription':
                    $this->site_description = $config['option_value'];
                    break;
                case 'admin_email':
                    $this->site_admin_email = $config['option_value'];
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Inicia la conexión con la DB.
     * @return Boolean Indica si se realizó la conexión o no.
     */
    private function connect()
    {
        try {
            $params = $this->params;
            $conn = "mysql:host=" . $params['DB_HOST'] . ";" .
                    "dbname=" . $params['DB_NAME'] . ";" .
                    "port=" . $params['DB_PORT'] . ";";
                    "charset=" . $params['DB_CHARSET'] . ";";

            return new PDO(
                $conn,
                $params['DB_USER'],
                $params['DB_PASSWORD'],
                $params['OPTIONS']
            );
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Ejecuta la query recibida
     * @return [type] [description]
     */
    public function execute($query, $limit = true)
    {
        if ($query) {
            try {
                if ($limit) {
                    $query .= ' LIMIT ' . $this->quantity;
                    $query .= ' OFFSET ' . (($this->page - 1) * $this->quantity);
                }

                $q = $this->dbh->prepare($query);
                $q->execute();
                if ($q) {
                    return $q;
                }
            } catch (\Exception $e) {}
        }

        return null;
    }

    /**
     * Realiza la consulta y devuelve el número de resultados totales.
     */
    private function executeCount($subquery)
    {
        if ($subquery) {
            try {
                $query = <<<EOL
                    SELECT count(*) as total
                    FROM ($subquery) table_virtual
EOL;


                $q = $this->dbh->prepare($query);
                $q->execute();
                if ($q) {
                    return (int)$q->fetchColumn();
                }
            } catch (\Exception $e) {}
        }

        return 0;
    }

    /**
     * Devuelve todos los parámetros de conexión a la DB.
     * @return Array Parámetros de configuración para la DB.
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Cierra la conexión con la db.
     * @return Boolean Devuelve boolean indicando si fue cerrada o no.
     */
    public function close()
    {
        try {
            //$this->dbh->closeCursor();
            $this->dbh = null;
            return true;
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Devuelve la consulta para todos los posts
     * @return Array Colección con el total de resultados y las instancias
     *               de Post().
     *               [
     *                  'results' => Integer,
     *                  'totalResults' => Integer,
     *                  'data' => [
     *                      new Post($data),
     *                      new Post($data),
     *                  ]
     *               ]
     */
    public function getPosts()
    {
        $tablePosts = $this->table_prefix . 'posts';
        $tableUsers = $this->table_prefix . 'users';
        $tablePostMeta = $this->table_prefix . 'postmeta';
        $tableTermRelationship = $this->table_prefix . 'term_relationships';
        $tableTermTaxonomy = $this->table_prefix . 'term_taxonomy';
        $tableTerms = $this->table_prefix . 'terms';

        $query = <<<EOL
            SELECT
                posts.ID,
                posts.post_date_gmt,
                posts.post_content,
                posts.post_title,
                posts.post_excerpt,
                posts.comment_status,
                posts.post_name,
                posts.post_modified_gmt,
                posts.post_parent,
                posts.guid,
                posts.comment_count,
                users.ID as user_id,
                users.display_name as user_displayname,
                users.user_nicename as user_username,
                users.user_email,
                users.user_url as user_web,
                users.user_registered as user_registered_at,
                postsThumbnail.guid as image,
                postsThumbnail.post_mime_type as image_type,
                terms.term_id as category_id,
                terms.slug as category_slug,
                terms.name as category_name,
                term_taxonomy.description as category_description

            FROM $tablePosts posts
            LEFT JOIN $tableUsers users ON posts.post_author = users.ID
            LEFT JOIN $tablePostMeta postmeta
                ON
                    posts.ID = postmeta.post_id
                AND
                    postmeta.meta_key = "_thumbnail_id"

            LEFT JOIN $tablePosts postsThumbnail
                ON postmeta.meta_value = postsThumbnail.ID


            LEFT JOIN $tableTermRelationship as tr
                ON
                    posts.ID = tr.object_id
                AND
                    tr.term_taxonomy_id = (
                        SELECT MAX(sub_t.term_taxonomy_id)
                        FROM $tableTermTaxonomy sub_t
                        WHERE
                                sub_t.term_id IS NOT NULL
                            AND
                                sub_t.term_taxonomy_id IS NOT NULL
                            AND
                                sub_t.taxonomy = "category"
                            AND tr.term_taxonomy_id = sub_t.term_taxonomy_id

                        GROUP BY sub_t.term_taxonomy_id
                        ORDER BY sub_t.count DESC

                        LIMIT 1
                    )

            LEFT JOIN $tableTermTaxonomy term_taxonomy
                ON
                    tr.term_taxonomy_id = term_taxonomy.term_taxonomy_id
                AND
                    term_taxonomy.taxonomy = "category"

            LEFT JOIN $tableTerms as terms ON term_taxonomy.term_id = terms.term_id

            WHERE
                    posts.ID IS NOT NULL
                AND posts.post_status = "publish"
                AND posts.post_type = "post"
                AND posts.post_title IS NOT NULL
                AND posts.post_content IS NOT NULL
                AND (
                        posts.post_password IS NULL
                        OR
                        posts.post_password = ""
                    )
                AND posts.post_name IS NOT NULL

            GROUP BY posts.ID
            ORDER BY posts.post_modified_gmt DESC, posts.post_date_gmt DESC,
                     category_id DESC, category_slug DESC
EOL;

        $posts = $this->execute($query);

        $collection = [];

        foreach ($posts as $idx => $post) {
            $collection[$idx] = new Post(array_merge($post, [
                'domain' => $this->domain,
                'site_name' => $this->site_name,
                'site_home' => $this->site_home,
                'site_url' => $this->site_url,
                'site_description' => $this->site_description,
                'site_admin_email' => $this->site_admin_email,
            ]), ++$idx);
        }

        return [
            'results' => count($collection),
            'totalResults' => $this->executeCount($query),
            'data' => $collection,
        ];
    }

    /**
     * Busca todas las categorías de la aplicación y las devuelve en
     * una colección representando cada elemento una instancia de Category().
     * @return Array Colección con el total de resultados y las instancias
     *               de Category().
     *               [
     *                  'results' => Integer,
     *                  'totalResults' => Integer,
     *                  'data' => [
     *                      new Category($data),
     *                      new Category($data),
     *                  ]
     *               ]
     */
    public function getCategories()
    {
        $tableTermTaxonomy = $this->table_prefix . 'term_taxonomy';
        $tableTerms = $this->table_prefix . 'terms';

        $query = <<<EOL
            SELECT
                term_taxonomy.term_taxonomy_id,
                terms.term_id as category_id,
                terms.slug as category_slug,
                terms.name as category_name,
                term_taxonomy.description as category_description
            FROM $tableTermTaxonomy term_taxonomy
            LEFT JOIN $tableTerms as terms
                ON term_taxonomy.term_id = terms.term_id
            WHERE
                    term_taxonomy.taxonomy = "category"
                AND terms.slug IS NOT NULL
                AND terms.name IS NOT NULL
            GROUP BY terms.name
            ORDER BY terms.name ASC
EOL;

        $categories = $this->execute($query);

        $collection = [];

        foreach ($categories as $idx => $category) {
            $collection[$idx] = new Category(array_merge($category, [
                'domain' => $this->domain,
                'site_name' => $this->site_name,
                'site_home' => $this->site_home,
                'site_url' => $this->site_url,
                'site_description' => $this->site_description,
                'site_admin_email' => $this->site_admin_email,
            ]), ++$idx);
        }

        return [
            'results' => count($collection),
            'totalResults' => $this->executeCount($query),
            'data' => $collection,
        ];
    }

    /**
     * Devuelve una colección con todos los posts, instancias de Post()
     * según la categoría solicitada.
     *
     * @param  Integer $category_id Es el id de la categoría.
     *
     * @return Array Colección con el total de resultados y las instancias
     *               de Post().
     *               [
     *                  'results' => Integer,
     *                  'totalResults' => Integer,
     *                  'data' => [
     *                      new Post($data),
     *                      new Post($data),
     *                  ]
     *               ]
     */
    public function getPostsByCategory($category_id)
    {
        $tablePosts = $this->table_prefix . 'posts';
        $tableUsers = $this->table_prefix . 'users';
        $tablePostMeta = $this->table_prefix . 'postmeta';
        $tableTermRelationship = $this->table_prefix . 'term_relationships';
        $tableTermTaxonomy = $this->table_prefix . 'term_taxonomy';
        $tableTerms = $this->table_prefix . 'terms';

        $query = <<<EOL
            SELECT
                posts.ID,
                posts.post_date_gmt,
                posts.post_content,
                posts.post_title,
                posts.post_excerpt,
                posts.comment_status,
                posts.post_name,
                posts.post_modified_gmt,
                posts.post_parent,
                posts.guid,
                posts.comment_count,
                users.ID as user_id,
                users.display_name as user_displayname,
                users.user_nicename as user_username,
                users.user_email,
                users.user_url as user_web,
                users.user_registered as user_registered_at,
                postsThumbnail.guid as image,
                postsThumbnail.post_mime_type as image_type,
                terms.term_id as category_id,
                terms.slug as category_slug,
                terms.name as category_name,
                term_taxonomy.description as category_description

            FROM $tableTermRelationship tr

            LEFT JOIN $tablePosts posts
                ON tr.object_id = posts.ID
            LEFT JOIN $tableUsers users ON posts.post_author = users.ID

            LEFT JOIN $tablePostMeta postmeta
                ON
                    posts.ID = postmeta.post_id
                AND
                    postmeta.meta_key = "_thumbnail_id"

            LEFT JOIN $tablePosts postsThumbnail
                ON postmeta.meta_value = postsThumbnail.ID

            LEFT JOIN $tableTermTaxonomy as term_taxonomy
                ON tr.term_taxonomy_id = term_taxonomy.term_taxonomy_id

            LEFT JOIN $tableTerms as terms
                ON term_taxonomy.term_id = terms.term_id

            WHERE
                    term_taxonomy.taxonomy = "category"
                AND terms.term_id = $category_id
                AND posts.ID IS NOT NULL
                AND posts.post_status = "publish"
                AND posts.post_type = "post"
                AND posts.post_title IS NOT NULL
                AND posts.post_content IS NOT NULL
                AND (
                        posts.post_password IS NULL
                        OR
                        posts.post_password = ""
                    )
                AND posts.post_name IS NOT NULL
            GROUP BY posts.ID
            ORDER BY terms.name ASC
EOL;

        $posts = $this->execute($query);

        $collection = [];

        foreach ($posts as $idx => $post) {
            $collection[$idx] = new Post(array_merge($post, [
                'domain' => $this->domain,
                'site_name' => $this->site_name,
                'site_home' => $this->site_home,
                'site_url' => $this->site_url,
                'site_description' => $this->site_description,
                'site_admin_email' => $this->site_admin_email,
            ]), ++$idx);
        }

        return [
            'results' => count($collection),
            'totalResults' => $this->executeCount($query),
            'data' => $collection,
        ];
    }
}

?>
