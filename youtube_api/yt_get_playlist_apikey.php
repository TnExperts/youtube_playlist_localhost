<?php

// youtube playlist videoid fetcher
// initial test version v0.01 :)
// License MIT

require_once "class.http.api.php";

// php runtime variables
ini_set("default_charset", "UTF-8");
ini_set("memory_limit", "4000M");
ini_set("max_execution_time", 3600*5);

// insert your youtube APIv3 Key here:
// guide: [youtubeapi-v3](https://developers.google.com/youtube/v3/getting-started)
$apikey = "AIzaSyBeeymyfYDFB1xaiHDH4lYtbSeeA0dG-Gg";
	
$http=new httpServicesAPI();
$http->do_set_options();

// playlist id from https://www.youtube.com/watch?v=g3ml_WCpbsg&list=RDg3ml_WCpbsg
$hash = "RDg3ml_WCpbsg";
$restquery = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=10&playlistId=".$hash."&key=".$apikey;
$json=json_decode($http->get($restquery));
$http->close();

// todo parse next pages in an outer loop
//print_r("Results: ".$json->pageInfo->totalResults.PHP_EOL);
	
foreach($json->items as $key) {
	print $key->snippet->resourceId->videoId.";";
	print("'".$key->snippet->title."'".PHP_EOL);
}

?>