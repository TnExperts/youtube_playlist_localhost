<?php

// Simple and Fast YouTube Playlist videoid fetcher.
//  alpha test version v0.10 :) - Marcedo@habMalNeFrage.de
// License MIT

	require_once "class.http.api.php";

	// php runtime variables
	ini_set("default_charset", "UTF-8");
	ini_set("memory_limit", "4000M");
	ini_set("max_execution_time", 3600*5);

	// NOTE: That APIv3 Key was created using the following:
	// guide: [youtubeapi-v3](https://developers.google.com/youtube/v3/getting-started)
	$apikey = "AIzaSyBeeymyfYDFB1xaiHDH4lYtbSeeA0dG-Gg";
	// To avoid permanet abuse, ill revoke that somwhen in the future. Feel Free to create your own.
	
	// Parse argv - require console and minimum one parameter. 
	if(PHP_SAPI !== 'cli') { 
		exit(0);
	}

	if (count($argv)>1) {
		$url =$argv[1];
	} else {
		$url = '';
		print("no URL given");
		exit(100); 
	}	

	// Differenciate a single Video ID from a Playlist ID
	//https://linuxpanda.wordpress.com/2013/07/24/ultimate-best-regex-pattern-to-get-grab-parse-youtube-video-id-from-any-youtube-link-url/

	// Video id is 11 characters in length
	$video_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
	$videoId = (preg_replace($video_pattern, '$1', $url));

	// Playlist id is 12 or more characters in length
	$playlist_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
	$playlistId = (preg_replace($playlist_pattern, '$1', $url));

	// BUG: above stuff seems to interpret a 11 char video id as a playlist.
	// Check and cleanse Link
	if(strlen($playlistId) <12) $playlistId="";
	if(strpos($playlistId,$videoId) !== false) $videoID="";
		
	//Okay -We have a VideoLink. So just print and bail.
	if ($videoId !== "" && $playlistId=="") {
		print $videoId.PHP_EOL;
		exit(0);
	} 
	
	// Playlist: So set feed URL	
	if ($videoId==""){
		$feedURL = 'https://www.youtube.com/playlist?list='.$playlistId;
	} else { 
		$feedURL= $url;
	}

	// Now, ask Youtubes v3Api about the Playlists contents
	$http=new httpServicesAPI();
	$http->do_set_options();

	// playlist id from https://www.youtube.com/watch?v=g3ml_WCpbsg&list=RDg3ml_WCpbsg
	//$playlistId = "RDg3ml_WCpbsg";
	$restquery = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=10&playlistId=".$playlistId."&key=".$apikey;
	$json=json_decode($http->get($restquery));
	$http->close();

	// TODO: Parse all pages from the response within an outer loop
	//print_r("Results: ".$json->pageInfo->totalResults.PHP_EOL);
	
	foreach($json->items as $key) {
		print $key->snippet->resourceId->videoId.";";
		print("'".$key->snippet->title."'".PHP_EOL);
	}

?>