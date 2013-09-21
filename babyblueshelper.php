<?Php
error_reporting(E_ALL);
$ch=curl_init();
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0');

if(!isset($_GET['date']))
	$_GET['date']=date('m/d/Y');
preg_match('^(http://safr.kingfeatures.com.+)"^',file_get_contents($url='http://babyblues.com/archive/index.php?formname=getstrip&GoToDay='.$_GET['date']),$result);

curl_setopt($ch, CURLOPT_URL, str_replace(' ','+',$result[1]));

curl_setopt($ch,CURLOPT_REFERER,$url);
$script=curl_exec($ch); //Get the javascript with the image link
preg_match("/'(.+)'/U",$script,$result); //Remove javascript and html tags to get the image link

curl_setopt($ch, CURLOPT_URL,$result[1]);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,false); //Image should ouput directly
header('Content-type: image/gif');
curl_exec($ch);
curl_close($ch);
?>