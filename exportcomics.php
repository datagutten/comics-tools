<?Php
//A tool to export comics to files and folders based on date and eventually delete from the database
$comic='gele';
$comicspath='/home/comics';
$outfolder='/home/exportedcomics';

$sql="SELECT *,comics_release.id AS releaseid,comics_image.id AS imageid FROM comics_release,comics_image,comics_comic WHERE comics_release.id=comics_image.id AND comics_comic.id=comics_release.comic_id AND slug='$comic'";
require 'config.php';
$result=$db->query($sql);
$rows=$result->fetchall(PDO::FETCH_ASSOC);
//print_r($rows);
foreach ($rows as $issue)
{
	
	$datefolder=str_replace('-','',substr($issue['pub_date'],0,7));
	$folder=$outfolder."/mnt/tegneserier/$comic/$datefolder/";
	if(!file_exists($folder))
		mkdir($folder,0777,true);
	echo $folder.$issue['pub_date']."\n";
	copy($comicspath.'/media/'.$issue['file'],$folder.str_replace('-','',$issue['pub_date']).'.jpg');
	
	//echo "DELETE FROM comics_release_images WHERE release_id={$issue['releaseid']};\n";
	//echo "DELETE FROM comics_image WHERE id={$issue['imageid']};\n";
	//echo "DELETE FROM comics_release WHERE id={$issue['releaseid']};\n";
	
}