<?php


namespace datagutten\comics_tools\comics_api_client;


use datagutten\tools\PDOConnectHelper;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;

class ComicsAPICache extends ComicsAPI
{
    /**
     * @var PDO
     */
    public $db;
    /**
     * @var PDOStatement
     */
    private $st_insert;
    /**
     * @var PDOStatement
     */
    private $st_select;
    /**
     * @var PDOStatement
     */
    private $st_lookup_checksum;

    function __construct(array $config)
    {
        parent::__construct($config);

        $this->db = PDOConnectHelper::connect_db_config($config['db']);
        $this->st_select = $this->db->prepare('SELECT * FROM comics_api_cache WHERE site=? AND comic=? AND pub_date=?');
        $this->st_insert = $this->db->prepare("INSERT INTO comics_api_cache (site, comic,pub_date,file,checksum,fetched) VALUES (?, ?,?,?,?,?)");
        $this->st_lookup_checksum = $this->db->prepare('SELECT * FROM comics_api_cache WHERE site=? AND comic=? AND checksum=?');
    }

    public static function create_table(PDO $db)
    {
        $q = file_get_contents(__DIR__.'/create_cache_table.sql');
        $db->query($q);
    }

    /**
     * Add an image to the cache
     * @param string $slug Comic slug
     * @param string $pub_date Release publication date
     * @param array $image Image info
     * @return void
     */
    protected function cache_add(string $slug, string $pub_date, array $image)
    {
        $this->st_insert->execute([$this->site_hostname, $slug, $pub_date, $image['file'], $image['checksum'], $image['fetched']]);
    }

    /**
     * @param string $slug
     * @param string $date
     * @return array Release information
     * @throws exceptions\HTTPError HTTP error
     * @throws exceptions\NoResultsException No releases found
     * @throws exceptions\ComicsException Request error
     */
    function releases_date_cache($slug, $date)
    {
        if (strpos($date, '-') === false)
            throw new InvalidArgumentException('Date must be Y-M-D format');

        $this->st_select->execute([$this->site_hostname, $slug, $date]);
        if ($this->st_select->rowCount() == 0)
        {
            $release = parent::releases_date($slug, $date)[0];
            if(empty($release['images']))
                throw new exceptions\ComicsException('No image found');
            $image = $release['images'][0];
            try
            {
                $this->st_insert->execute([$this->site_hostname, $slug, $release['pub_date'], $image['file'], $image['checksum'], $image['fetched']]);
            }
            catch (PDOException $e)
            {
                //TODO: Show exception
            }
            return ['comic'=>$slug, 'pub_date'=>$release['pub_date'], 'file'=>$image['file'], 'checksum'=>$image['checksum'], 'fetched'=>$image['fetched']];
        }
        else
        {
            return $this->st_select->fetch(PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param string $slug Comic slug
     * @param string $checksum Image checksum
     * @return array
     * @throws exceptions\HTTPError HTTP error
     * @throws exceptions\NoResultsException No releases found
     * @throws exceptions\ComicsException Request error
     */
    public function lookup_checksum(string $slug, string $checksum): array
    {
        $this->st_lookup_checksum->execute([$this->site_hostname, $slug, $checksum]);
        $releases_db = $this->st_lookup_checksum->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($releases_db))
            return $releases_db;
        else
        {
            $images = [];
            foreach ($this->releases_checksum($checksum) as $release)
            {
                foreach ($release['images'] as $image)
                {
                    $this->cache_add($release['comic']['slug'], $release['pub_date'], $image);
                    if ($release['comic']['slug'] == $slug)
                        $images[] = $image + [
                                'slug' => $slug,
                                'comic' => $slug,
                                'pub_date' => $release['pub_date'],
                            ];
                }
            }
            return $images;
        }
    }
}