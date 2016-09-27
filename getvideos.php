<?php
require_once("Crypto.php");
class getvideos{
	private $url;
	
	function __construct(){
		$request = $_REQUEST['url'];
		$this->setUrl($request);
	}
	
	public function getUrl(){
		return $this->url;
	}
	
	public function setUrl($url_v){
		$this->url = $this->decodeUrl($url_v);
	}
	
	public function run(){		
		if(strpos($this->url , 'photos.google.com') !== false){
			$this->ggPhotos();
		}elseif(strpos($this->url , 'youtube.com') !== false){
			$this->youtube();
		}elseif(strpos($this->url , 'xvideos.com') !== false){
			$this->xvideos();
		}elseif(strpos($this->url , 'xhamster.com') !== false){
			$this->xhamster();
		}elseif(strpos($this->url , 'redtube.com') !== false){
			$this->redtube();
		}elseif(strpos($this->url , 'txxx.com') !== false){
			$this->tubecup();
		}elseif(strpos($this->url , 'youporn.com') !== false){
			$this->youporn();
		}elseif(strpos($this->url , 'pornhub.com') !== false){
			$this->pornhub();
		}elseif(strpos($this->url , 'tube8.com') !== false){
			$this->tube8();
		}elseif(strpos($this->url , 'tv.zing.vn') !== false){
			$this->zingtv();
		}elseif(strpos($this->url , 'v.nhaccuatui.com') !== false){
			$this->nct();
		}elseif(strpos($this->url, 'drive.google.com') || strpos($this->url, 'docs.google.com')){
			$this->ggDrive();
		}
	}
	
	private function decodeUrl($url){
		$key = base64_decode("ov0DVar9m275gKByHTqsYg==");
		
		$_fixUrl = base64_decode(str_replace(' ', '+', $url));
		
		try {
			$real_url = Crypto::Decrypt($_fixUrl, $key);
		} catch (InvalidCiphertextException $ex) {
			die('DANGER! DANGER! The ciphertext has been tampered with!');
		} catch (CryptoTestFailedException $ex) {
			die('Cannot safely perform encryption');
		} catch (CannotPerformOperationException $ex) {
			die('Cannot safely perform decryption');
		}
		
		return $real_url;
	}
	
	private function directLinks($data){
		if (!empty(array_filter($data)))
			echo json_encode($data);
		else
			echo 'No Data';
	}
	
	private function get_id_youtube($url){
        preg_match('/v=([A-z0-9-_]+)/is', $url, $id);
        return $id[1];
    }

	private function get_youtube($url){        
        $id = $this->get_id_youtube($url);
        $video_info = get_content('http://www.youtube.com/get_video_info?video_id=' . $id);
        $url_encoded_fmt_stream_map = '';
        parse_str($video_info);
        $return['title'] = $title;
        if(isset($url_encoded_fmt_stream_map)){
            $my_formats_array = explode(',',$url_encoded_fmt_stream_map);
        }
        else
        {
            return false;
        }
        if (count($my_formats_array) == 0) {
            return false;
        }
        $avail_formats[] = '';
        $i = 0;
        $ipbits = $ip = $itag = $sig = $s = $signature = $quality = $type = $url = '';
        $expire = time();
        foreach($my_formats_array as $format) {
            parse_str($format);
            $avail_formats[$i]['itag'] = $itag;
            $avail_formats[$i]['quality'] = $quality;
            $type = explode(';',$type);
            $avail_formats[$i]['type'] = $type[0];
			
			if($signature != ''){
				if(strpos($url, "signature=") === false){
					$avail_formats[$i]['url'] = urldecode($url) . '&signature=' . $signature;
				}else{
					$avail_formats[$i]['url'] = urldecode($url);
				}	
			}else{
				if($s != ''){
					if(strpos($url, "&s=") === false){
						$avail_formats[$i]['url'] = urldecode($url) . '&s=' . $s;
					}else{
						$avail_formats[$i]['url'] = urldecode($url);
					}	
				}else{
					if($sig != ''){
						if(strpos($url, "signature=") === false){
							$avail_formats[$i]['url'] = urldecode($url) . '&signature=' . $sig;
						}else{
							$avail_formats[$i]['url'] = urldecode($url);
						}	
					}
				}
			}
            
			parse_str(urldecode($url));
            $avail_formats[$i]['expires'] = date("G:i:s T", $expire);
            $avail_formats[$i]['ipbits'] = $ipbits;
            $avail_formats[$i]['ip'] = $ip;
            $i++;
        }
        $return['data'] = $avail_formats;
        return $return;
	}
	
	private function youtube(){
		$data = $this->get_youtube($this->url);
		foreach($data['data'] as $link){
			if($link['quality'] == 'hd720'){
				$itemhd['url'] = $link['url'];
				$itemhd['quan'] = '720';
			}	
			if($link['quality'] == 'medium'){
				$itemlarge['url'] = $link['url'];
				$itemlarge['quan'] = '480';
			}	
			if($link['quality'] == 'small'){
				$itemmedium['url'] = $link['url'];
				$itemmedium['quan'] = '360';
				break;
			}
		}
		
		$mp4link['link'] = [];
		if($itemhd){		
			array_push($mp4link['link'], $itemhd);
		}
		if($itemlarge){	
			array_push($mp4link['link'], $itemlarge);
		}
		if($itemmedium){	
			array_push($mp4link['link'], $itemmedium);
		}
				
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}
	
	private function getContent($url, $encode=false, $proxy=false) {
		$ch = @curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		$head[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		$head[] = "Connection: keep-alive";
		$head[] = "Keep-Alive: 300";
		$head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$head[] = "Accept-Language: en-us,en;q=0.5";
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		if($encode === true)
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip ,deflate');
		if($proxy === true){
			curl_setopt($ch, CURLOPT_PROXY, '113.185.19.192:80');
			//curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}
		
		$page = curl_exec($ch);
		curl_close($ch);
		return $page;
	}
	
	private function ggPhotos(){
		$get = $this->getContent($this->url);
	
		$data = explode('url\u003d', $get);
		$urls = explode('%3Dm', $data[1]);
		$decode = urldecode($urls[0]);
		$count = count($data);
		$item = array();
		$mp4link['link'] = [];
		if($count > 4) {
			$v1080p = $decode.'=m37';
			$v720p = $decode.'=m22';
			$v360p = $decode.'=m18';
			
			$item['url'] = $v1080p;
			$item['quan'] = '1080p';
			array_push($mp4link['link'], $item);
			
			$item['url'] = $v720p;
			$item['quan'] = '720p';
			array_push($mp4link['link'], $item);
			
			$item['url'] = $v360p;
			$item['quan'] = '360p';
			array_push($mp4link['link'], $item);
		}
		if($count > 3 && $count <= 4) {
			$v720p = $decode.'=m22';
			$v360p = $decode.'=m18';
			
			$item['url'] = $v720p;
			$item['quan'] = '720p';
			array_push($mp4link['link'], $item);
			
			$item['url'] = $v360p;
			$item['quan'] = '360p';
			array_push($mp4link['link'], $item);
		}
		if($count > 2 && $count <= 3) {
			$v360p = $decode.'=m18';
			
			$item['url'] = $v360p;
			$item['quan'] = '360p';
			array_push($mp4link['link'], $item);
		}
		
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}
	
	private function xvideos(){
		$data = $this->getContent($this->url);
		
		preg_match_all("/html5player.setVideoUrlHigh\('(.*)'\);/", $data, $match);				

		$mp4link['thumb'] = '';
		$mp4link['link'] = $match[1][0];
		
		$this->directLinks($mp4link);
	}
	
	private function xhamster(){
		$data = $this->getContent($this->url);
	
		$data = explode('video: {', $data);
		$match = explode('},', $data[1]);				
		preg_match('/file: \'(.*)\'/' ,explode(',', $match[0])[0], $link);
		
		$mp4link['link'] = $link[1];
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}
	
	private function tubecup(){
		$data = $this->getContent($this->url);
		
		preg_match('/<div class="download-link" style="display: none;"><a href="(.*)" id/', $data, $match);
		
		$mp4link['link'] = $match[1];
		
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}
	
	private function redtube(){
		$data = $this->getContent($this->url);
	
		preg_match('/sources: {(.*)},/', $data, $src);
		
		$jsonData = json_decode('{'.str_replace('\/', '/', $src[1]).'}', true);
		
		$mp4link['link'] = [];
		if (array_key_exists('720', $jsonData)) {
			$item['url'] = $jsonData['720'];
			$item['quan'] = '720p';
			array_push($mp4link['link'], $item);
		}
		if (array_key_exists('480', $jsonData)) {
			$item['url'] = $jsonData['480'];
			$item['quan'] = '480p';
			array_push($mp4link['link'], $item);
		}
		if(array_key_exists('240', $jsonData)) {
			$item['url'] = $jsonData['240'];
			$item['quan'] = '240p';
			array_push($mp4link['link'], $item);
		}	
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}
	
	private function pornhub(){		
		$data = $this->getContent($this->url);
		preg_match_all('/var player_quality_(.*)\';/', $data, $match);
		
		$arrData = explode(';', $match[0][0]);		
		$arrData = array_filter($arrData);		
		
		$mp4link['link'] = [];
		for($i = 0; $i < count($arrData); $i++){
			$temp = explode(' = ', $arrData[$i], 2);
			$link = trim($temp[1], "'");
			$quan = $temp[0];
			if (strpos($quan, '720p') !== false && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '720p';
				array_push($mp4link['link'], $item);
			}
			if (strpos($quan, '480p') !== false && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '480p';
				array_push($mp4link['link'], $item);
			}
			if (strpos($quan, '240p') !== false && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '240p';
				array_push($mp4link['link'], $item);
			}
		}	
		
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}
	
	private function youporn(){
		$data = $this->getContent($this->url);
	
		$match = explode('}' ,explode('sources: {', $data)[1])[0];
		
		$match = trim(preg_replace('/\s+/', '', $match));
		
		$jsonData = explode(',', $match);		

		$mp4link['link'] = [];
		for($i = 0; $i < count($jsonData); $i++){
			$temp = explode(':', $jsonData[$i], 2);
			$link = trim($temp[1], "'");
			$quan = trim($temp[0], "'");
			if ($quan == '1080_60' && $link != '') {				
				$item['url'] = $link;
				$item['quan'] = '1080HD';
				array_push($mp4link['link'], $item);				
			}			
			if ($quan == '1080' && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '1080p';
				array_push($mp4link['link'], $item);
			}		
			if ($quan == '720_60' && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '720HD';
				array_push($mp4link['link'], $item);
			}
			if ($quan == '720' && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '720p';
				array_push($mp4link['link'], $item);
			}
			if ($quan == '480' && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '480p';
				array_push($mp4link['link'], $item);
			}
			if($quan == '240' && $link != '') {
				$item['url'] = $link;
				$item['quan'] = '240p';
				array_push($mp4link['link'], $item);
			}
		}
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}	
	
	private function tube8(){
		$data = $this->getContent($this->url);
		preg_match('/var flashvars = {(.*)};/', $data, $match);
		
		$jsonData = json_decode('{'.str_replace('\/', '/', $match[1]).'}', true);
				
		$mp4link['link'] = [];
		if (array_key_exists('quality_180p', $jsonData)) {
			$item['url'] = $jsonData['quality_180p'];
			$item['quan'] = '180p';
			array_push($mp4link['link'], $item);
		}
		if (array_key_exists('quality_240p', $jsonData)) {
			$item['url'] = $jsonData['quality_240p'];
			$item['quan'] = '240p';
			array_push($mp4link['link'], $item);
		}
		if (array_key_exists('quality_480p', $jsonData)) {
			$item['url'] = $jsonData['quality_180p'];
			$item['quan'] = '180p';
			array_push($mp4link['link'], $item);
		}
		if (array_key_exists('quality_720p', $jsonData)) {
			$item['url'] = $jsonData['quality_720p'];
			$item['quan'] = '720p';
			array_push($mp4link['link'], $item);
		}
		
		$mp4link['thumb'] = '';
		
		$this->directLinks($mp4link);
	}
	
	
	private function getIdZing($url){
		$regex = '/http\:\/\/tv\.zing\.vn\/video\/(.*)\/(.*).html/';
		preg_match($regex, $url, $getID);
		return $getID[2];
	}
	
	private function decodeCryptZTV($text){
		$key = 'f_pk_ZingTV_1_@z';
		$iv = 'f_iv_ZingTV_1_@z';
		if($text != ''){
			$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv_size = mcrypt_enc_get_iv_size($cipher);
			if(mcrypt_generic_init($cipher, $key, $iv) != -1){
				$char = '';
				for($i=0; $i<strlen($text);$i+=2){
					$char .= chr(hexdec($text{$i}.$text{($i+1)}));
				}
				$cipherText = mdecrypt_generic($cipher,$char);
				mcrypt_generic_deinit($cipher);
				return $cipherText;
			}else{
				return false;
			}
		}
	}
	
	/*Get Zing TV not use API*/
	private function zingtv(){
		$item = array();
		$mp4link['link'] = [];
		
		$idVideo = $this->getIdZing($this->url);
		
		$linkEmbed = 'http://tv.zing.vn/embed/video/'.$idVideo;
		$data = $this->getContent($linkEmbed, true);
		//$data = @file_get_contents('compress.zlib://'.$linkEmbed);
		
		preg_match('/http\:\/\/tv\.zing\.vn\/tv\/xml\/media\-embed\/(.*)"/', $data, $arr_preg);
		$xmlURL = str_replace('"','',$arr_preg[0]);						
		
		/*$xml_data = $this->getContent($xmlURL);
		$xml_data = @file_get_contents('compress.zlib://'.$xmlURL);
		
		$xml_string = str_replace("]]>", "", str_replace("<![CDATA[","",$xml_data));
		$xml_string = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $xml_string);			
		$xml_arr = json_decode(json_encode((array) simplexml_load_string($xml_string)), 1);
		
		if($xml_arr['item']){
			$item = $xml_arr['item'];
			$return['360p'] = trim($this->decodeCryptZTV($item['source']));
			$return['480p'] = trim($this->decodeCryptZTV($item['f480']));
			$return['720p'] = trim($this->decodeCryptZTV($item['f720']));
		}*/
		
		$xml = simplexml_load_file("compress.zlib://".$xmlURL, 'SimpleXMLElement', LIBXML_NOCDATA);
		if ($xml->item->f1080) {
			$item['url'] = trim($this->decodeCryptZTV($xml->item->f1080));
			$item['quan'] = '1080p';
			if(strpos($item['url'], 'http:') !== false)
				array_push($mp4link['link'], $item);
			
		}
		if($xml->item->f720){
			$item['url'] = trim($this->decodeCryptZTV($xml->item->f720));
			$item['quan'] = '720p';
			if(strpos($item['url'], 'http:') !== false)
				array_push($mp4link['link'], $item);
		}
		if($xml->item->f480){
			$item['url'] = trim($this->decodeCryptZTV($xml->item->f480));
			$item['quan'] = '480p';
			if(strpos($item['url'], 'http:') !== false)
				array_push($mp4link['link'], $item);
		}
		if($xml->item->source){
			$item['url'] = trim($this->decodeCryptZTV($xml->item->source));
			$item['quan'] = '360p';
			if(strpos($item['url'], 'http:') !== false)
				array_push($mp4link['link'], $item);
		}		
		
		//var_dump($mp4link);
		
		$mp4link['thumb'] = '';
		$this->directLinks($mp4link);
	}
	
	/*Get Zing TV use API, only for IP Vietnam*/
	private function zingtvAPI(){
		// More API key a34811d0cdc52c769a54647b6bde97de
		$apiKey = 'd04210a70026ad9323076716781c223f';
		$id = $this->getIdZing($this->url);
		
		$apiUrl = 'http://api.tv.zing.vn/2.0/media/info?api_key='.$apiKey.'&media_id='.$id;
		
		$data = $this->getContent($apiUrl);

		$data = json_decode($data, true);
				
		$item = array();
		$mp4link['link'] = [];
		
		if(array_key_exists('Video720', $data['response']['other_url'])){
			$item['url'] = $data['response']['other_url']['Video720'];
			$item['quan'] = '720p';
			array_push($mp4link['link'], $item);
		}
		if(array_key_exists('Video480', $data['response']['other_url'])){
			$item['url'] = $data['response']['other_url']['Video480'];
			$item['quan'] = '480p';
			array_push($mp4link['link'], $item);
		}
		if(array_key_exists('file_url', $data['response'])){
			$item['url'] = $data['response']['file_url'];
			$item['quan'] = '360p';
			array_push($mp4link['link'], $item);
		}		
		
		$mp4link['thumb'] = '';
		$this->directLinks($mp4link);
	}
	
	private function getNtcId ($url){
		preg_match('/key=([A-z0-9-_]+)/is', $url, $id);
		return $id[1];
	}
	
	private function nct(){
		$data = $this->getContent($this->url);
		preg_match('/play_key="([A-z0-9]+)"/', $data, $key);
		
		$xmlURL = 'http://v.nhaccuatui.com/flash/xml?key5='.$key[1];
		$xml = simplexml_load_file($xmlURL, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		$id = $this->getNtcId($this->url);
		
		$item = array();
		$mp4link['link'] = [];		
		foreach ($xml->track->children() as $child) {

			if($child->key == $id){
				$item['url'] = $child->location;
				$item['quan'] = '480p';
				array_push($mp4link['link'], $item);
				
				if($child->highquality->count()){
					$item['url'] = $child->highquality;
					$item['quan'] = '720p';
					array_push($mp4link['link'], $item);
				}
				if($child->lowquality->count()){
					$item['url'] = $child->lowquality;
					$item['quan'] = '360p';
					array_push($mp4link['link'], $item);
				}
				break;
			}
		}
		
		$mp4link['thumb'] = '';
		$this->directLinks($mp4link);
	}
	
	private function ggDrive(){
		$hostSupport = 'http://api.anivn.com/?url=';		
		$data = $this->getContent($hostSupport.$this->url);
		
		$jsonData = json_decode($data, true);
		
		$item = array();
		$mp4link['link'] = [];
		
		if(array_key_exists('1080', $jsonData)){
			$item['url'] = str_replace('app=animevn.biz&', '', $jsonData['1080']);
			$item['quan'] = '1080p';
			array_push($mp4link['link'], $item);
		}
		if(array_key_exists('720', $jsonData)){
			$item['url'] = str_replace('app=animevn.biz&', '', $jsonData['720']);
			$item['quan'] = '720p';
			array_push($mp4link['link'], $item);
		}
		if(array_key_exists('480', $jsonData)){
			$item['url'] = str_replace('app=animevn.biz&', '', $jsonData['480']);
			$item['quan'] = '480p';
			array_push($mp4link['link'], $item);
		}
		if(array_key_exists('360', $jsonData)){
			$item['url'] = str_replace('app=animevn.biz&', '', $jsonData['360']);
			$item['quan'] = '360p';
			array_push($mp4link['link'], $item);
		}
		
		$mp4link['thumb'] = '';
		$this->directLinks($mp4link);
	}
	
	private function ggDriveIpv6(){
		$get = $this->getContent($this->url);
		$cat = explode(',["fmt_stream_map","', $get);
		$cat = explode('"]', $cat[1]);
		$cat = explode(',', $cat[0]);
		foreach($cat as $link){
			$cat = explode('|', $link);
			$links = str_replace(array('\u003d', '\u0026'), array('=', '&'), $cat[1]);
			if($cat[0] == 37) {$f1080p = $links;}
			if($cat[0] == 22) {$f720p = $links;}
			if($cat[0] == 35) {$f480p = $links;}
			if($cat[0] == 43) {$f360p = $links;}
		}
		
		$item = array();
		$mp4link['link'] = [];
		
		if(isset($f1080p)){
			$item['url'] = $f1080p;
			$item['quan'] = '1080p';
			
			$AnimeVN = "<source src=\"".$f1080p."\" type=\"video/mp4\" data-res=\"1080\" />\n<source src=\"".$f720p."\" type=\"video/mp4\" data-res=\"720\" />\n<source src=\"".$f480p."\" type=\"video/mp4\" data-res=\"480\" />\n<source src=\"".$f360p."\" type=\"video/mp4\" data-res=\"360\" />";
		} elseif(isset($f720p)){
			$item['url'] = $f720p;
			$item['quan'] = '720p';
			
			$AnimeVN = "<source src=\"".$f720p."\" type=\"video/mp4\" data-res=\"720\" />\n<source src=\"".$f480p."\" type=\"video/mp4\" data-res=\"480\" />\n<source src=\"".$f360p."\" type=\"video/mp4\" data-res=\"360\" />";
		} elseif(isset($f480p)){
			$AnimeVN = "<source src=\"".$f480p."\" type=\"video/mp4\" data-res=\"480\" />\n<source src=\"".$f360p."\" type=\"video/mp4\" data-res=\"360\" />";
		} elseif(isset($f360p)){
			$AnimeVN = "<source src=\"".$f360p."\" type=\"video/mp4\" data-res=\"360\" />";
		} else {
			$AnimeVN = '<source src="https://lh3.googleusercontent.com/XpE2g3UEIu7WblZ1P-Elc7KFutP13AbO1algeZgqXV0=m37" type="video/mp4" data-res="360" />';
		}
		return $AnimeVN;
	} 
	
	/*private function loginClipvn(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://clip.vn/ajax/login');
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest'));
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('username' => 'levosau0502', 'password' => 'khongnhonua', 'persistent' => 1, 'persistent' => 1));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$COOKIE = curl_exec($ch);
		curl_close($ch);
	}
	
	private function clipvn(){
		$this->loginClipvn()
		
		$get = $this->getContent($this->url);
		preg_match("/Clip.App.clipId(.*)';/U",$get,$id);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://clip.vn/movies/nfo/'.$id[1]);
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('onsite' => 'clip'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);

		preg_match_all("#<enclosure url='(.*?)' duration='([0-9]+)' id='(.*?)' type='(.*?)' quality='([0-9]+)' (.*?) />#is", $data, $data);
		
		for($i=0;$i<3;$i++){ 
			$file = '{file: "'.$data[1][$i].'"},';
		}if($data[1][3] != ''){
			$linkmp4 .= '"1080p" src="'.$data[1][3].'<br>';
		}if($data[1][2] != ''){
			$linkmp4 .= '"720p" src="'.$data[1][2].'<br>';
		}if($data[1][1] != '') {
			$linkmp4 .= '"480p" src="'.$data[1][1].'<br>';
		}if($data[1][0] != '') {
			$linkmp4 .= '"360p" src="'.$data[1][0].'<br>';}
		
		var_dump($linkmp4);
	}*/
}