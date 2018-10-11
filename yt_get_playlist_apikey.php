<?php
$apikey = "AIzaSyBeeymyfYDFB1xaiHDH4lYtbSeeA0dG-Gg";

// php runtime variables
ini_set("default_charset", "UTF-8");
ini_set("memory_limit", "4000M");
ini_set("max_execution_time", 3600*5);
	
// some common functions
function doAPIRequest($url) {
	
	$run = true;
	
	while($run == true) {
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		//curl_setopt($ch, CURLOPT_VERBOSE, 1);
		
		$reply = curl_exec($ch);
		
		curl_close($ch);
		
		if($reply != false) {
			$run = false;
			return json_decode($reply);
		} else {
			sleep(1);
		}
	}
}	
				
		$hash = "PLMC9KNkIncKtsacKpgMb0CVq43W80FKvo";
		$restquery = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=10&playlistId=".$hash."&key=".$apikey;
		
		//$hash = "RWOE7_bRXSs";
		//$restquery = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails,snippet,status&id=".$hash."&key=".$apikey;
		
	$json = doAPIRequest($restquery);
	//print($reply->items[0]->snippet->resourceId->kind.PHP_EOL);

	print_r("Results: ".$json->pageInfo->totalResults.PHP_EOL);
	foreach($json->items as $key) {
	print $key->snippet->resourceId->videoId.", ";
	print($key->snippet->title.PHP_EOL);
	
	}
	
	
?>