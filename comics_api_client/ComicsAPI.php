<?php


namespace datagutten\comics_tools\comics_api_client;


use datagutten\comics_tools\comics_api_client\exceptions;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use WpOrg\Requests;

class ComicsAPI
{
    public $site_url;
    public $session;
    /**
     * @var string
     */
    public $site_hostname;

    /**
     * comics_api constructor.
     * @param array $config
     * @throws exceptions\ComicsException
     */
    function __construct(array $config)
    {
        //$fields = ['site_url', 'media_url', 'secret_key'];
        if (empty($config['secret_key']))
            throw new exceptions\ComicsException('Config missing secret_key');
        if (empty($config['site_url']))
            throw new exceptions\ComicsException('Config missing site_url');

        $this->site_url = $config['site_url'];
        $this->site_hostname = parse_url($config['site_url'], PHP_URL_HOST);

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Key ' . $config['secret_key']
        ];

        $this->session = new Requests\Session($config['site_url'] . '/api/v1/', $headers, ['key' => $config['secret_key']], ['timeout'=>30]); // , ['proxy'=>'127.0.0.1:8888']
    }

    /**
     * @param $uri
     * @return mixed
     * @throws exceptions\HTTPError HTTP error
     * @throws exceptions\NoResultsException No results returned
     * @throws exceptions\ComicsException Request error
     */
    function request($uri)
    {
        try
        {
            $response = $this->session->get($uri);
        }
            /** @noinspection PhpRedundantCatchClauseInspection */
        catch (Requests\Exception $e)
        {
            throw new exceptions\ComicsException('Request error', 0, $e);
        }
        if (!$response->success)
        {
            if ($response->status_code == 400)
                throw new exceptions\HTTPError('Bad request, check parameters', $response);
            elseif ($response->status_code == 401)
                throw new exceptions\HTTPError('Invalid secret key', $response);
            else
                throw new exceptions\HTTPError('HTTP error ' . $response->status_code, $response);
        }
        $data = json_decode($response->body, true);
        if (!isset($data['meta']) && !empty($data))
            return $data;
        elseif ($data['meta']['total_count'] > 0)
            return $data['objects'];
        else
            throw new exceptions\NoResultsException($uri, $response);
    }

    function releases_checksum($checksum)
    {
        $releases = $this->request("releases/?images__checksum=$checksum");
        foreach ($releases as $key=>$release)
        {
            $releases[$key]['comic'] = $this->request($release['comic']);
        }
        return $releases;
    }

    /**
     * Get all releases from a year
     * @param string $slug Comic slug
     * @param int $year Year
     * @return array Releases
     * @throws exceptions\HTTPError HTTP error
     * @throws exceptions\NoResultsException No releases found
     * @throws exceptions\ComicsException Request error
     */
    function releases_year($slug, $year)
    {
        if (strlen($year) != 4 || !is_numeric($year))
            throw new InvalidArgumentException('Year must be four digits');
        return $this->request("releases/?comic__slug=$slug&pub_date__year=$year&limit=366");
    }

    /**
     * Get all releases from a month
     * @param string $slug Comic slug
     * @param int $year Year
     * @param int $month Month
     * @return array Releases
     * @throws exceptions\HTTPError HTTP error
     * @throws exceptions\NoResultsException No releases found
     * @throws exceptions\ComicsException Request error
     */
    function releases_month($slug, $year, $month)
    {
        list($start, $end) = self::month($month, $year);
        return $this->request("releases/?comic__slug=$slug&pub_date__gte=$start&pub_date__lte=$end&limit=31");
    }

    /**
     * Get releases for specific date
     *
     * @param string $slug Comic slug
     * @param string $date Release date
     * @return array Releases
     * @throws exceptions\HTTPError HTTP error
     * @throws exceptions\NoResultsException No releases found
     * @throws exceptions\ComicsException Request error
     */
    function releases_date($slug, $date)
    {
        return $this->request("releases/?comic__slug=$slug&pub_date=$date");
    }

    /**
     * Get a single release
     *
     * @param string $slug Comic slug
     * @param string $date Release date
     * @return string Image file name
     * @throws exceptions\HTTPError HTTP error
     * @throws exceptions\NoResultsException No releases found
     * @throws exceptions\ComicsException Request error
     */
    function release_single($slug, $date)
    {
        $release = $this->releases_date($slug, $date);
        return $release[0]['images'][0]['file'];
    }

    /**
     * Find first and last day of month
     * Usage: list($start,$end)=comics_api::month($month,$year)
     *
     * @param int $month Month
     * @param int $year Year
     * @return array First and last date
     */
    public static function month($month, $year)
    {
        if (strlen($year) != 4 || !is_numeric($year))
            throw new InvalidArgumentException('Year must be four digits');

        $start = new DateTime("$year-$month-1");
        $end = clone $start;
        $end->add(new DateInterval('P1M')); //Add 1 month
        $end->sub(new DateInterval('P1D')); //Subtract 1 day to get last day of month

        return array($start->format('Y-m-d'), $end->format('Y-m-d'));
    }

    /**
     * Convert to the release array to a simpler format
     *
     * @param array $releases
     * @param bool $single
     * @return array
     */
    public static function format_releases($releases, $single = false)
    {
        $rows = [];
        foreach (array_reverse($releases) as $release)
        {
            $rows[] = array('date' => str_replace('-', '', $release['pub_date']), 'file' => $release['images'][0]['file']);
        }
        if ($single)
            return $rows[0];
        else
            return $rows;
    }
}
