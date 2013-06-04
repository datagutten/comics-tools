<?Php
//A tool to change the slug of a comic
if(!isset($argv[1]) || !isset($argv[2]))
	die("Usage: php changeslug.php [oldslug] [newslug]\n");
require 'config.php';

$result=$db->query("SELECT file,comics_image.id AS image_id FROM comics_image,comics_comic WHERE comics_image.comic_id=comics_comic.id AND slug='$slug'");

$query_comics_comics=$db->prepare("UPDATE comics_comic SET slug=? WHERE slug=?");
$query_comics_comics->execute(array($newslug,$slug));

$updatequery=$db->prepare("UPDATE comics_image SET file=? WHERE id=?");
foreach ($result->fetchall(PDO::FETCH_ASSOC) as $row)
{
	$newfile=str_replace($argv[1],$argv[2],$row['file']);
	//echo $row['file']."\n";
	//echo $newfile."\n";
	$updatequery->execute(array($newfile,$row['image_id']));
}