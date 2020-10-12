<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace datagutten\comics_tools\tests;

use datagutten\comics_tools\comics_api_client\ComicsAPI;
use datagutten\comics_tools\comics_api_client\exceptions;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class comics_apiTest extends TestCase
{
    /**
     * @var ComicsAPI
     */
    public $comics;

    public function setUp(): void
    {
        $config = require 'test_config.php';
        $this->comics = new ComicsAPI($config);
    }

    public function testInvalidKey()
    {
        $this->expectException(exceptions\HTTPError::class);
        $this->expectExceptionMessage('Invalid secret key');
        $comics = new ComicsAPI(['site_url'=>$this->comics->site_url, 'secret_key'=>'bad']);
        $comics->releases_year('pondus', '2020');
    }

    public function testNoReleases()
    {
        $this->expectException(exceptions\NoResultsException::class);
        $this->comics->releases_year('pondus', '2020');
    }

    public function testRelease_single()
    {
        $url = $this->comics->release_single('pondusbt', '2020-10-02');
        $this->assertStringContainsString(
            'a7d71c9f7b7c055a50d4fdbdcde02140ac99aa8b93ced426e9d08e10940df8cf.jpg', $url);
    }

    public function testFormat_releases()
    {
        $releases = $this->comics->releases_year('pondus', '2019');
        $releases = ComicsAPI::format_releases($releases);
        $this->assertEquals(['date'=>'20190102', 'file'=>'https://comics.datagutten.net/media/pondus/b/b62a55c7b5e58353b8276d18b429ef5016137e3358914f92f82295bc5eb424e4.jpg'], $releases[0]);
    }

    public function testMonth()
    {
        list($start, $end) = ComicsAPI::month('10', '2020');
        $this->assertEquals('2020-10-01', $start);
        $this->assertEquals('2020-10-31', $end);
    }

    public function testMonthInvalidYear()
    {
        $this->expectException(InvalidArgumentException::class);
        ComicsAPI::month('10', '20');
    }

    public function testReleases_date()
    {
        $releases = $this->comics->releases_date('pondus', '2018-10-03');
        $this->assertIsArray($releases);
    }

    public function testReleases_month()
    {
        $releases = $this->comics->releases_month('pondus', '2018','10');
        $this->assertIsArray($releases);
    }

    public function testReleases_year()
    {
        $releases = $this->comics->releases_year('pondus', '2018');
        $this->assertIsArray($releases);
    }
}
