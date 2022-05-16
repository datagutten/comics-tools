<?Php
//A tool to export comics to files and folders based on date and eventually delete from the database
use datagutten\comics_tools\comics_api_client\ComicsAPI;
use datagutten\comics_tools\comics_api_client\exceptions\ComicsException;
use datagutten\tools\files\files;

require 'vendor/autoload.php';
$config = require 'config.php';
try
{
    $comics = new ComicsAPI($config);
}
catch (ComicsException $e)
{
    die($e->getMessage() . "\n");
}

if (empty($argv[1]))
    die("Usage: php exportcomics.php [comic slug] [year]");

$slug = $argv[1];
$export_folder = 'comics_export';

$releases = $comics->releases_year($slug, $argv[2]);

foreach ($releases as $release)
{
    $pub_date = new DateTime($release['pub_date']);
    $folder = files::path_join($export_folder, $slug, $pub_date->format('Ym'));
    if (!file_exists($folder))
        mkdir($folder, 0777, true);

    $multi_image = count($release['images']) > 1;
    foreach ($release['images'] as $key => $image)
    {
        $extension = pathinfo($image['file'], PATHINFO_EXTENSION);
        if (!$multi_image)
            $name = $pub_date->format('Ymd');
        else
            $name = sprintf('%s-%d', $pub_date->format('Ymd'), $key);

        if (!empty($image['title']))
            file_put_contents(files::path_join($folder, $name . '.txt'), $image['title']);
        if (!empty($image['text']))
            file_put_contents(files::path_join($folder, $name . ' text.txt'), $image['text']);

        $image_file = files::path_join($folder, $name . '.' . $extension);
        if (!file_exists($image_file))
        {
            $response = $comics->session->get($image['file'], [], ['filename' => $image_file]);
            if (!$response->success)
                printf("Failed to download %s\n", $image['file']);
            else
                printf("Saved %s %s\n", $slug, $release['pub_date']);
        }
    }
}