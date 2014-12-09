<?php
require 'PHPMailer/PHPMailerAutoload.php';
$mail             = new PHPMailer();
require 'comicmail.config.php';


$xml=simplexml_load_file('https://comics.io/my/feed/?key='.$key);
$xml=json_decode(json_encode($xml),true);
//print_r($xml);
//$day='-1 days 00:00';
//$day='last tuesday';
if(!isset($argv[1]))
	$day='today';
else
	$day=$argv[1];

echo date('dmY',strtotime($day))."\n";

$body='';

foreach ($xml['entry'] as $entry)
{
	preg_match('^published (.*)^',$entry['title'],$date);
	echo $entry['title']."<br>\n";
	echo strtotime($date[1]).'=='.strtotime($day)."<br>\n";
	if(strtotime($date[1])==strtotime($day))
	{
		$body.=$entry['title']."\n";
		$body.=$entry['summary'];
		$datetext=$date[1];
	}
}




//$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP(); // telling the class to use SMTP

$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only


//$mail->AddReplyTo("name@yourdomain.com","First Last");

$mail->Subject    = $xml['title'].' '.$datetext;

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

//preg_match('^Comics for (.*)^',$xml['title'],$address);
$mail->AddAddress($address, $address);
//echo $body;
echo $xml['title'].' '.$datetext;
if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}