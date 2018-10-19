<?php
// From https://github.com/You2php/you2php
// Modified to be command line Invoked on a youTube ID or Link.
// Returns the fully qualified URL to the video source 
// Example: yt_get_prot D2v8kI01xDI
// OKT 2018 
// simplify Code
// Optionally force a specific player origin ->line224
// Add some more verbose Formats
//

require_once "class.http.api.php";


// utils.php
function sig_js_decode($player_html){
/*	
- Extract the signature decryption Instructions 
- Return them as arrray
- used by getInstructions
*/

	// Extract Signature decryption functions Name.
	//	$L=function(a,b,c){b=void 0===b?"":b;c=void 0===c?"":c;var d=new g.cL(a);a.match(/https:\/\/yt.akamaized.net/)||d.set("alr","yes");c&&d.set(b,bL(c));return d};
	// Pattern -> .*;.&&..set(.,XX(.*));.*;};
	$prefix ="/\W*.*;\w\&&\w\.set\(\w,";
	$funcName = "([\$a-zA-Z0-9]{2})";
	$suffix= "\(.*\).*;.*;/";

	if(preg_match($prefix.$funcName.$suffix, $player_html, $matches)){
		$func_name = $matches[1];		
		$func_name = preg_quote($func_name);
	
		// extract code block from that function
		// single quote in case function name contains $dollar sign
		// -> bL=function(a){a=a.split("");aL.jl(a,58);aL.NI(a,2);aL.l5(a,35);aL.NI(a,2);aL.jl(a,45);aL.l5(a,4);aL.jl(a,46);return a.join("")};
	
		if(preg_match('/'.$func_name.'=function\([a-z]+\){(.*?)}/', $player_html, $matches)){
			
			$js_code = $matches[1];
			
			// extract all relevant statement functions Names
			// eg: aL.NI(a,2)
			if(preg_match_all('/([a-z0-9]{2})\.([a-z0-9]{2})\([^,]+,(\d+)\)/i', $js_code, $matches) != false){
				
				// must be identical
				$obj_list = $matches[1];
				$func_list = $matches[2];
				
				// extract javascript code for each one of those statement functions
				preg_match_all('/('.implode('|', $func_list).'):function(.*?)\}/m', $player_html, $matches2,  PREG_SET_ORDER);
				$functions = array();
				
				// translate each statement function according to its use
				foreach($matches2 as $m){
					if(strpos($m[2], 'splice') !== false){
						$functions[$m[1]] = 'splice';						
					} else if(strpos($m[2], 'a.length') !== false){
						$functions[$m[1]] = 'swap';
					} else if(strpos($m[2], 'reverse') !== false){
						$functions[$m[1]] = 'reverse';
					}
				}
				
				// FINAL STEP! concate them to instructions set array
				$instructions = array();
				foreach($matches[2] as $index => $name)
					$instructions[] = array($functions[$name], $matches[3][$index]);
				
				return $instructions;
			}
		}
	}
	
	return false;
}


// YouTube is capitalized twice because that's how youtube itself does it:
// https://developers.google.com/youtube/v3/code_samples/php
class YouTubeDownloader {
		
	private $storage_dir;
	private $cookie_dir;
	private $http;
	
	// currently supported : 17,18 and 36
	private $itag_info = array(
		5 => "FLV 400x240",
		6 => "FLV 450x240",
		13 => "3GP Mobile",
		17 => "3GP 144p",
		18 => "MP4 360p",
		22 => "MP4 720p (HD)",
		34 => "FLV 360p",
		35 => "FLV 480p",
		36 => "3GP 240p",
		37 => "MP4 1080",
		38 => "MP4 3072p",
		43 => "WebM 360p",
		44 => "WebM 480p",
		45 => "WebM 720p",
		46 => "WebM 1080p",
		59 => "MP4 480p",
		78 => "MP4 480p",
		82 => "MP4 360p 3D",
		83 => "MP4 480p 3D",
		84 => "MP4 720p 3D",
		85 => "MP4 1080p 3D",
		91 => "MP4 144p",
		92 => "MP4 240p HLS",
		93 => "MP4 360p HLS",
		94 => "MP4 480p HLS",
		95 => "MP4 720p HLS",
		96 => "MP4 1080p HLS",
		100 => "WebM 360p 3D",
		101 => "WebM 480p 3D",
		102 => "WebM 720p 3D",
		120 => "WebM 720p 3D",
		127 => "TS Dash Audio 96kbps",
		128 => "TS Dash Audio 128kbps"
	);
	
	function __construct(){
		$this->storage_dir = sys_get_temp_dir();
		$this->cookie_dir = sys_get_temp_dir();
		$this->http=new httpServicesAPI();
		$this->http->do_set_options();
	}
	
	public function close(){
		$this->http->close();
	}
	
	private function getInstructions($html){
	// takes: html content of URL watch?v=videoId
	//	used by getDownloadLinks
		
		// check what player version that video is using
		// <script src="//s.ytimg.com/yts/jsbin/player-fr_FR-vflHVjlC5/base.js" name="player/base"></script>
		if(preg_match('@<script\s*src="([^"]+player[^"]+js)@', $html, $matches)){
			
			$player_url = $matches[1];
			
			// relative protocol?
			if(strpos($player_url, '//') === 0){
				$player_url = 'http://'.substr($player_url, 2);
			} else if(strpos($player_url, '/') === 0){
				// relative path?
				$player_url = 'http://www.youtube.com'.$player_url;
			}
			
			// try to find instructions list already cached from previous requests...
			$file_path = $this->storage_dir.'/'.md5($player_url);
			
			if(file_exists($file_path)){
				// unserialize could fail on empty file
				$str = file_get_contents($file_path);
				return unserialize($str);
			} else {
				$js_code = $this->http->get($player_url);
				$instructions = sig_js_decode($js_code);
				
			 // or write them back 	
				if($instructions){
					file_put_contents($file_path, serialize($instructions));
					return $instructions;
				}
			}
		}
		
		return false;
	}
	
	private function selectFirst($links, $selector){
	//
	//	selector by format: mp4 360, 
	//
	
		$result = array();
		$formats = preg_split('/\s*,\s*/', $selector);
		
		// has to be in this order
		foreach($formats as $f){
			foreach($links as $l){
				if(stripos($l['format'], $f) !== false || $f == 'any')
					$result[] = $l;
			}
		}
		
		return $result;
	}
		
	public function extractId($str){
	//
	// extract youtube video_id from any piece of text
	//
	
		if(preg_match('/[a-z0-9_-]{11}/i', $str, $matches)){
			return $matches[0];
		}
		
		return false;
	}
	
	public function getDownloadLinks($id, $selector = false){
	//
	// takes ? , FormatDescriptor
	//

		// force a specific player origin
		// $forceLang="&gl=deDE&hl=de&has_verified=1&bpctr=9999999999");
		$forceLang="";
		$result = array();
		$instructions = array();
		
		// you can input HTML of /watch? page directory instead of id
		if(strpos($id, '<div id="player') !== false){
			$html = $id;
		} else {
			$video_id = $this->extractId($id);
			
			if(!$video_id){
				return false;
			}
			$html = $this->http->get("https://www.youtube.com/watch?v={$video_id}".$forceLang);
		}
		
		// age-gate
		if(strpos($html, 'player-age-gate-content') !== false){
			// nothing you can do folks...
			print("AGE-Gated - stop ").PHP_EOL;
			return false;
		}
		
		// wip: use a more proper way to get the FMT Map:
		$restquery='https://www.youtube.com/get_video_info?video_id='.$video_id.'&eurl=https://youtube.googleapis.com/v/'.$video_id;
		parse_str($this->http->get($restquery),$data);

			if($data) {			
				
				if (Isset($data['status']) && $data['status'] == 'fail') {
					//print("Youtube API Error: ". $data['errorcode']).PHP_EOL;
					print($data['errorcode']);
					return(array($data['errorcode']));
				}
				
				if (isset($data['url_encoded_fmt_stream_map']))  // optional - append $data['adaptive_fmts']
					$fmt_map = explode(',', $data['url_encoded_fmt_stream_map']);
					
			// use fallback solution
			// http://stackoverflow.com/questions/35608686/how-can-i-get-the-actual-video-url-of-a-youtube-live-stream
			} else if(preg_match('@url_encoded_fmt_stream_map["\']:\s*["\']([^"\'\s]*)@', $html, $matches)) { 
				$fmt_map = explode(",", $matches[1]);
			}
			
			if($fmt_map){
			
			foreach($fmt_map as $p){
				$query = str_replace('\u0026', '&', $p);
				parse_str($query, $arr);

				$url = $arr['url'];				
				
				if(isset($arr['sig'])){
					$url = $url.'&signature='.$arr['sig'];
				
				} else if(isset($arr['signature'])){
					$url = $url.'&signature='.$arr['signature'];
				
				} else if((isset($arr['s']))){
					// this is probably a VEVO/ads video... signature must be decrypted first! We need instructions for doing that
					if(count($instructions) == 0){
						$instructions = (array)$this->getInstructions($html);
					}
					
					$dec = $this->sig_decipher($arr['s'], $instructions);
					$url = $url.'&signature='.$dec;
				}
				
				// redirector.googlevideo.com
				//$url = preg_replace('@(\/\/)[^\.]+(\.googlevideo\.com)@', '$1redirector$2', $url);
				
				$itag = $arr['itag'];
				$format = isset($this->itag_info[$itag]) ? $this->itag_info[$itag] : 'Unknown';
				
				$result[$itag] = array(
					'url' => $url,
					'format' => $format
				);
				
			}
		}
		
		// do we want all links or just select few?
		if($selector){
			return $this->selectFirst($result, $selector);
		}
		
		return $result;
	}
	
	private function sig_decipher($signature, $instructions){
	// parse decryption instructions created by sig_js_decode
	// used by getDownloadLinks
	
		foreach($instructions as $opt){
			
			$command = $opt[0];
			$value = $opt[1];
			
			if($command == 'swap'){
				
				$temp = $signature[0];
				$signature[0] = $signature[$value % strlen($signature)];
				$signature[$value] = $temp;
				
			} else if($command == 'splice'){
				$signature = substr($signature, $value);
			} else if($command == 'reverse'){
				$signature = strrev($signature);
			}
		}
		
		return trim($signature);
	}
	
}

$videoList = array();
$quality=18; // 17 will return the smallest sized Video Source.3gp 18/36 are most common 360px Vids.

if(PHP_SAPI === 'cli') { 
	array_shift($argv);
	$videoList = $argv;
}
	
for($i = 0; $i < sizeof($videoList); $i++):
	$yt = new YouTubeDownloader();
	$links = $yt->getDownloadLinks("https://www.youtube.com/watch?v=$videoList[$i]");
	
	if(array_key_exists($quality,$links) !== false) {
		print($links[$quality]["url"]);		
	} else {
		exit(101); // Api Error
	}
	$yt->close();
endfor;

?>