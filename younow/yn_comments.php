<?php

// Mir gehn die ganzen trolle/hater bei younow aufn Sack.
// Der folgende Code ist ein Prototy zum Abfragen von Kommentaren (bei aktiven Broadcastern) 
// Ausblick: Community gepflegte negativ listen / #TrollWatch Bot 
// Mar2018 Marcedo@habMalNeFrage.de

$user = "Tho.";
$url = "https://api.younow.com/php/api/broadcast/info/curId=0/user=".$user;

// Init Curl
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//curl_setopt($ch, CURLOPT_VERBOSE, 1);

$num_comments=0;
$content="";
$page = curl_exec($ch);

// younows Api returns json so parse that here
$json=json_decode($page);

// Above Interface is only available when the user does a broadcast. 
print("Waiting for User ". $user."'s Stream...");
while ($json->errorCode!=0) {
	time_sleep_until(microtime(true)+1); // non-Blocking 1second timer
	print("😂");
}

while(property_exists ($json,"comments")){ 
	time_sleep_until(microtime(true)+1); // non-Blocking 1second timer
		// retrieve comments from php api.
		$json=json_decode( curl_exec($ch));
		// we have new comments ?
		if( count ($json->comments)>$num_comments){
			$num_comments=count ($json->comments);
			// Append the comment
				foreach($json->comments as $key=>$comment) {
				$line="{ ".$comment->name." }"." ' ".$comment->comment."'".PHP_EOL;
				print($line);
				$content=$content.$line;
				file_put_contents("yn_comments.txt", $content);
			}
		}
}
print("Fin..");
curl_close($ch);

?>