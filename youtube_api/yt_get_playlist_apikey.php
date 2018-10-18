<?php

// Simple and Fast YouTube PlaylistItems fetcher.
// https://github.com/arjunae/youtube_playlist_localhost
// alpha test version v0.11 :) - Marcedo@habMalNeFrage.de
// License BSD3Clause 

// PlaylistItems API Description :
// https://developers.google.com/youtube/v3/docs/playlistItems/list

	require_once "class.http.api.php";
	$apiPlaylistUrl="https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=40&playlistId=";
	$apiVideosUrl="https://www.googleapis.com/youtube/v3/videos?part=snippet&id=";
	
	// php runtime variables
	ini_set("default_charset", "UTF-8");
	//ini_set("memory_limit", "4000M");
	//ini_set("max_execution_time", 3600*5);

	// NOTE: That APIv3 Key was created using the following guide: [youtubeapi-v3](https://developers.google.com/youtube/v3/getting-started)
		$apikey = "AIzaSyBeeymyfYDFB1xaiHDH4lYtbSeeA0dG-Gg";
	
	if($apikey =="") { 
		print(" yt_get_playlist_apikey".PHP_EOL);
		print ("To avoid abuse, Feel Free to insert your own Youtube apikey :)".PHP_EOL);
		print("see https://developers.google.com/youtube/v3/getting-started".PHP_EOL);
		exit(101);
	}

	// Parse argv - require console and minimum one parameter. 
	if(PHP_SAPI !== 'cli') { 
		exit(0);
	}

	if (count($argv)>1) {
		$url =$argv[1];
	} else {
		$url = '';
		print("no URL given");
		exit(102); 
	}	

	// Differenciate a single Video ID from a Playlist ID
	// https://linuxpanda.wordpress.com/2013/07/24/ultimate-best-regex-pattern-to-get-grab-parse-youtube-video-id-from-any-youtube-link-url/

	// Video id : 11 characters in length
	$video_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
	$videoId = (preg_replace($video_pattern, '$1', $url));

	// Playlist id : 12 or more characters in length
	$playlist_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
	$playlistId = (preg_replace($playlist_pattern, '$1', $url));

	// BUG: Playlist_pattern  interprets a 11 char video id as a playlist.
	// Check and cleanse Link
	if(strlen($playlistId) <12) $playlistId="";
	if(strpos($playlistId,"http") !==false) $playlistId="";
	if(strpos($playlistId,$videoId) !== false) $videoId="";	

	// Handle videoId / playlistId
	if ($videoId !== "" && $playlistId=="") {
		print $videoId.PHP_EOL;
		exit(0); // only videoid - just print and bail. 
	} else if ($videoId=="") { // Playlist: So set feed URL	
		$feedURL = 'https://www.youtube.com/playlist?list='.$playlistId;
	}
	
	// Now, ask Youtubes v3Api about the Playlists contents.
	$http=new httpServicesAPI();
	$http->do_set_options();

	// Now iterate and parse through all resultsets from the response.
	$restquery = $apiPlaylistUrl.$playlistId."&key=".$apikey;
	$json=[];
	$nextPageToken=0;
	do {
				$json=json_decode($http->get($restquery));
				//print_r($json);
				if (!property_exists($json,"items")) {exit(102);}
				foreach($json->items as $key) {
					print ($key->snippet->resourceId->videoId.";'".$key->snippet->title."'".PHP_EOL);
				}
				if (property_exists($json,"nextPageToken")) {
					$nextPageToken=$json->nextPageToken;
					$restquery = $playlistApi.$playlistId."&pageToken=".$nextPageToken."&key=".$apikey;
				} else { 
					break; 
				}
	} while ($nextPageToken=!0);
		
	/*	
	// Option -> create a hash from above and fill with the data gathered here:
	$restquery=$apiVideosUrl."g3ml_WCpbsg"."&key=".$apikey;
	$json=json_decode($http->get($restquery));

	if (!property_exists($json,"items")) {exit(101);}
	foreach($json->items as $key) {
		print_r($key->snippet->thumbnails);
	}	
	*/
	
	$http->close();
		
?>