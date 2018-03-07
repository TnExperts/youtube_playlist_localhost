<?php

// Mir gehn die ganzen trolle/hater bei younow aufn Sack.
// Der folgende Code ist ein Prototyp zum Abfragen von Kommentaren (bei aktiven Broadcastern) 
// Version 0.6 alpha
// Ausblick: Community gepflegte negativ listen / python #TrollWatch Bot 
// Mar2018 Marcedo@habMalNeFrage.de

$user = "Tho.";
$url = "https://api.younow.com/php/api/broadcast/info/curId=0/user=".$user;

// Init Curl
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//curl_setopt($ch, CURLOPT_VERBOSE, 1);

$num_comments=0;
$all_comments="";
$viewers=0;

// younows Api returns json so parse that here
// Above Interface is only available when the user does a broadcast. 
$json=json_decode(curl_exec($ch));

print("Waiting for User ". $user."'s Stream...");
while ($json->errorCode!=0) {
	time_sleep_until(microtime(true)+1); // non-Blocking 1second timer
	print("😂");
	$json=json_decode( curl_exec($ch));
}

print(PHP_EOL."Stream has started.".PHP_EOL);
//print("URL: RTMP://".$json->host.$json->stream);

while(property_exists ($json,"comments")){ 
	time_sleep_until(microtime(true)+1); // non-Blocking 1second timer
	$hash0=(md5(serialize($json)));
	
		// retrieve comments from php api.
		$json=json_decode( curl_exec($ch));
		$hash1=(md5(serialize($json)));
		
		// do we have new comments ?
		if($hash0!==$hash1){			
				// Append the comment
				foreach($json->comments as $key=>$comment) {
				$line="{ ".$comment->name." }"." '".$comment->comment."'".PHP_EOL;
				print($line);
				$all_comments=$all_comments.$line;
			}
				file_put_contents("yn_comments.txt", $all_comments);
				$all_comments="";
		}
		
		// notify if the streams viewer count changes
		if($json->viewers > $viewers){
			$info="viewers: ".$json->viewers.PHP_EOL;
			$viewers=$json->viewers;
			file_put_contents("yn_comments.txt", $viewers,FILE_APPEND );
		}
}
print("Fin..");
curl_close($ch);

?>