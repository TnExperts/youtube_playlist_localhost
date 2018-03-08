<?php
//  Simple and Fast YouTube Playlist fetcher which does not depend on a YouTube API Key. 
//  Beta 0.9 - Marcedo@habMalNeFrage.de < doesnt really like php.
//
// Handles 1 from 2 Playlist types:
// 'https://www.youtube.com/playlist?list='   ~ Knows about side attached Playlist
// > Playlists which are fetched via xhr are not handled by that script. 
// ~ some error tolerance and error recovery. Codes:100=NoUrl, 101=ApiError
// ~ Output Format:  videoID;'VideoTitle' 

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

	//https://linuxpanda.wordpress.com/2013/07/24/ultimate-best-regex-pattern-to-get-grab-parse-youtube-video-id-from-any-youtube-link-url/

	// Video id is 11 characters in length
	$video_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{11})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
	$videoID = (preg_replace($video_pattern, '$1', $url));

	// Playlist id is 12 or more characters in length
	$playlist_pattern = '~(?:http|https|)(?::\/\/|)(?:www.|)(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/ytscreeningroom\?v=|\/feeds\/api\/videos\/|\/user\S*[^\w\-\s]|\S*[^\w\-\s]))([\w\-]{12,})[a-z0-9;:@#?&%=+\/\$_.-]*~i';
	$playlistID = (preg_replace($playlist_pattern, '$1', $url));

	// ^^ above stuff seems to interpret a 11 video id as a playlist ?!
	// Check and cleanse Link
	if(strlen($playlistID) <12) $playlistID="";
	if(strpos($playlistID,$videoID) !== false) $videoID="";
		
	//hm. only a VideoLink. So just print and bail.
	if ($videoID !== "" && $playlistID=="") {
		print $videoID;
		exit(0);
	} 
	
	// Set feed URL	
	if ($videoID==""){
		$feedURL = 'https://www.youtube.com/playlist?list='.$playlistID;
	} else { 
		$feedURL= $url;
	}
	
	// Init Curl
	$ch = curl_init($feedURL);
	curl_setopt($ch, CURLOPT_USERAGENT, 'YetAnotherPlaylistParser');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	//curl_setopt($ch, CURLOPT_VERBOSE, 1);

	libxml_use_internal_errors(true);
	$doc = new DOMDocument();
	$doc->loadHTML(curl_exec($ch));

	if (!$doc) {
	 foreach (libxml_get_errors() as $error) {
	 print(PHP_EOL);
	//  echo "Libxml error: {$error->message}\n";
    }
	}
	
	// # Debug Dump HTML 
	print($doc->saveHTML());
	
	// Note back if the playlist doesnt exist.
	// Also happens when searching for some Abo content Playlists.
	
		
	// Note back if the playlist doesnt exist.
	// Also happens when searching for some Abo content Playlists.
	$page=$doc->getElementById("page");
	foreach( $page->attributes as $searchNode )
	{
		if(strpos($searchNode->nodeValue,"oops-content")!==false) {
			print ($playlistID.";Api_Error");
			exit(101);
		}
	} 

	// Wow. what a rudimentary HTML Parser... 
	// For now just search for links in the side attached list.
	
	$classname="yt-uix-scroller-scroll-unit";
	$finder = new DomXPath($doc);
	$spaner = $finder->query("//*[contains(@class, '$classname')]");
	//	print($spaner->length." Items found");

	foreach( $spaner as $searchNode )
	{
		$video_id = $searchNode->getAttribute('data-video-id');
		$video_title = $searchNode->getAttribute('data-video-title');
		if ($video_id != "") print ($video_id.";'".$video_title."'".PHP_EOL);
	}

	$classname="pl-video";
	$finder = new DomXPath($doc);
	$spaner = $finder->query("//*[contains(@class, '$classname')]");
	//	print($spaner->length." Items found");
	
	foreach( $spaner as $searchNode )
	{
		$video_id = $searchNode->getAttribute('data-video-id');
		$video_title = $searchNode->getAttribute('data-title');
		if ($video_id != "") print ($video_id.";'".$video_title."'".PHP_EOL);
	} 
	
	libxml_use_internal_errors(false);
?>
