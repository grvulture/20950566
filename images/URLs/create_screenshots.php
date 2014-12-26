<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
/**
* To get this to work on my server for user "axxis" I had to edit /etc/sudoers and add:
* axxis ALL=(ALL) NOPASSWD: /usr/bin/phantomjs
**/
// Enable Error Reporting
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

include_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'simpleimage.php');

	function getTitle($content)
	{	
		$title	= '';
		// Get title
		$pattern =  "'<title>(.*?)</title>'s";
		preg_match_all($pattern, $content, $matches);
		if($matches)
		{
			$title = $matches[1][0];
		}
			
		return $title;
	}
	
	function getMetas($content)
	{
		libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings
		$doc = new DomDocument();
		$doc->loadHTML($content);
		$xpath = new DOMXPath($doc);
		$query = '//head/meta';//'//*/meta[starts-with(@property, \'og:\')]';
		$metas = $xpath->query($query);
		foreach ($metas as $meta) {
			$property = $meta->getAttribute('name');
			$content = $meta->getAttribute('content');
			$rmetas[$property] = $content;
		}
		if (!empty($rmetas)) {
			//echo "<pre>";print_r($rmetas);echo "</pre>";
			return $rmetas;
		}
		return false;
	}
	
	function getOG($content)
	{
		libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings
		$doc = new DomDocument();
		$doc->loadHTML($content);
		$xpath = new DOMXPath($doc);
		$query = '//*/meta[starts-with(@property, \'og:\')]';
		$metas = $xpath->query($query);
		foreach ($metas as $meta) {
			$property = $meta->getAttribute('property');
			$content = $meta->getAttribute('content');
			$rmetas[$property] = $content;
		}
		if (!empty($rmetas)) {
			//echo "<pre>";print_r($rmetas);echo "</pre>";
			return $rmetas;
		}
		return false;
	}
	
	function createImage($original, $destination) {
		$image = new SimpleImage();
		$image->load($original);
		$image->setQuality(98);
		if (!$image->false && $image->getWidth()>640) $image->resizeToWidth(640);
		$image->save($destination);
	}
	
	function createThumbnail($original, $thumbnail, $url) {
		//echo $original."<br /><br />";
		$image = new SimpleImage();
		if (!file_exists($original)) {
			$parse = parse_url($url);
			if (!$image->image_exist($original)) 
				if (!strstr($original, $parse['host'])) $original = $parse['scheme'].'://'.$parse['host'].$original;
			if (parse_url($original)) {
				if (!strstr($original,".jpg") 
					&& !strstr($original,".gif") 
					&& !strstr($original,".png")
					&& !strstr($original,"githubusercontent.com")
				) {
				//echo "this is a url. grab the contents of it and make to image: <br />";
					$birdy = birdyCMS::getInstance();
					$scripttoload 	= dirname(__FILE__).DIRECTORY_SEPARATOR."whole_page.js";
					$URLtoload = $original;
					$original = str_replace("thumb-".DS,"",$thumbnail);
					//echo $original."<br /><br />";
					$phantom = $birdy->loadPhantom($scripttoload, $URLtoload ,$original);
					//echo "COMMAND: <pre>".$phantom['command']."</pre><br />";
					if (!empty($phantom['errors'])) {
						//$birdy->outputAlert($phantom['errors']);
						return false;
					}
				}
			}
		}
		//echo "<br /> thumbnail to process: <br />".$thumbnail."<br /><br />";
		$image->load($original);
		$image->setQuality(90);
		if (!$image->false) {
			if ($image->getWidth()>256) $image->resizeToWidth(256);
			$image->save($thumbnail);
			$image->close();
		} else {
		 return false;
		}
		return true;
	}
	
// the important stuff...
$what = false;
$URLtoload 		= trim(strip_tags($klip_url));
$parse_url = parse_url($URLtoload);
if (!empty($parse_url)) {
	$code_of_loaded_content = mb_convert_encoding(file_get_contents($URLtoload), 'HTML-ENTITIES', "UTF-8");
	$title = getTitle($code_of_loaded_content);
	if (empty($title) && isset($metas['title'])) $title = $metas['title'];
	$metas = getMetas($code_of_loaded_content);
	$descr = ($metas && isset($metas['description'])) ? $metas['description'] : '';
	$author= isset($metas['author']) ? 'by '.$metas['author'] : '';
	$opengr= getOG($code_of_loaded_content);
	if ($opengr) {
		if (empty($title)) $title = $opengr['og:title'];
		if (empty($descr) && isset($opengr['og:description'])) $descr = $opengr['og:description'];
		if (empty($author) && isset($opengr['og:site_name'])) $author = $opengr['og:site_name'];
		if (isset($opengr['og:type'])) $what = $opengr['og:type']; else $what = 'page';
		if (isset($opengr['og:image'])) $ogimage = $opengr['og:image'];
		if (isset($opengr['og:video'])) $ogoriginal = str_replace("&feature=youtube_gdata_player","",str_replace("https://","http://",$opengr['og:video']));
		if (isset($opengr['og:type']) && strstr($opengr['og:type'],'photo')) $ogoriginal = $opengr['og:image'];
	}
	
	$user_dir = dirname(__FILE__).DS.$user->id;
	//echo "USERDIR: $user_dir <br />";
	$birdy->createDir($user_dir);
	$date = date("Y-m-d");
	$path = $user_dir.DS.$date;
	//echo "PATH: $path <br />";
	$birdy->createDir($user_dir.DS.$date);
	
	$scripttoload 	= dirname(__FILE__).DIRECTORY_SEPARATOR."whole_page.js"; // phantomjs script that loads the url
	
	$file = date("H-i-s").'.png';
	$original = $path.DS.$file;
	$thumbnail = $path.DS.'thumb-'.$file;
	//echo "ORIGINAL: <pre>$original</pre> <br />";
	//echo "THUMBNAIL: <pre>$thumbnail</pre> <br />";
	
	$URLtoCurrentDate = str_replace(DS,"/",str_replace(dirname(__FILE__),"",$path)).'/';
	$URLtoCurrentDateThumbnail = $URLtoCurrentDate.'thumb-';
	//echo "URLtoCurrentDate: ".$URLtoCurrentDate."<br /><br />";
	
	if (empty($ogimage)&&empty($ogoriginal)) {
		if (!file_exists($original)) {
			include_once BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'webthumbnail.php';
			$thumb = new Webthumbnail($URLtoload);
			$thumb
				->setTimeout(1)
				->setWidth(500)
				->setHeight(500)
				->setScreen(1280);
			$success = $thumb->captureToFile($original);
			//success is array([png]=the file,[_statusCode]=200,[_contentType]=image/png]
			if (isset($success->_statusCode) && $success->_statusCode==200) {
				/* the file saved here is not a valid png file! 0.o
				$image = new SimpleImage();
				$image->load($original);
				$image->setQuality(98);
				if (!$image->false) $image->resizeToWidth(640);
				$image->save($original);
				*/
				//echo "Called from Webthumbnail<br />";
				createThumbnail($original,$thumbnail,$URLtoload);
			} else {
				$original = str_replace(".png",".jpg",$original);
				$phantom = $birdy->loadPhantom($scripttoload, $URLtoload ,$original);
				if (!empty($phantom['errors'])) {
					$birdy->outputAlert($phantom['errors']);
					$URLtoCurrentDate = '';
					$URLtoCurrentDateThumbnail = '/';
					$file = 'default.jpg';
					$original = dirname(__FILE__).DS.$file;
				}
				createImage($original,$original);
				//echo "Called from phantomjs<br />";
				createThumbnail($original,$thumbnail,$URLtoload);
			}
		}
	} else {
		if (!empty($ogoriginal) && $what!='video') {
			//echo "Called from ogoriginal<br />";
			createThumbnail($ogoriginal,$thumbnail,$URLtoload);
			if (strstr($ogoriginal,'flickr.com')) {
				createImage($ogoriginal,$original);
			} else {
				$parse = parse_url($URLtoload);
				$image = new SimpleImage;
				if (!$image->image_exist($ogoriginal)) {
					if (!strstr($ogoriginal, $parse['host'])) $ogoriginal = $parse['scheme'].'://'.$parse['host'].$ogoriginal;
				}
				if ($image->image_exist($ogoriginal)) {
					$original = $ogoriginal;
				} else {
					$file = 'default.jpg';
					$original = dirname(__FILE__).DS.$file;
				}
			}
		}
		elseif (!empty($ogimage)) {
			//echo "Called from ogimage<br />";
			createThumbnail($ogimage,$thumbnail,$URLtoload);
			if (strstr($ogimage,'flickr.com')) {
				createImage($ogimage,$original);
			} else {
				$parse = parse_url($URLtoload);
				$image = new SimpleImage;
				if (!$image->image_exist($ogimage)) 
					if (!strstr($ogimage, $parse['host'])) $ogimage = $parse['scheme'].'://'.$parse['host'].$ogimage;
				if ($image->image_exist($ogimage)) {
					$original = $ogimage;
				} else {
					$file = 'default.jpg';
					$original = dirname(__FILE__).DS.$file;
				}
			}
		}
	}
	
	//if (!file_exists(dirname(__FILE__).DS.$file)) $file = 'default.jpg';
	//echo "ORIGINAL: ".BIRDY_URL.'images/URLs'.$URLtoCurrentDate.$file."<br />";
	//echo "THUMB: ".BIRDY_URL.'images/URLs'.$URLtoCurrentDateThumbnail.$file."<br />";
	//echo "THUMB: ".$thumbnail."<br />";
	
	$original_file = $original;
	$file = strstr($original,'.jpg') ? str_replace(".png",".jpg",$file) : $file;
	if (file_exists($original)) {
		$original_file = BIRDY_URL.'images/URLs'.$URLtoCurrentDate.$file;
	} elseif (!parse_url($original)) {
		$original_file = BIRDY_URL.'images/URLs/default.jpg';
	}
	$original = ($what!='video') ? $original_file : $ogoriginal;
	
	$file = str_replace(".jpg",".png",$file);
	$thumbnail_file = BIRDY_URL.'images/URLs'.$URLtoCurrentDateThumbnail.$file;
	if (!file_exists($thumbnail)) {
		//echo "THUMB DOES NOT EXIST!!! Original: ".$original;
		if (strstr($original_file,"default.jpg")) {
			$URLtoCurrentDateThumbnail = '/thumbnails/';
			$file = 'default.jpg';
			$thumbnail_file = BIRDY_URL.'images/URLs'.$URLtoCurrentDateThumbnail.$file;
		} else {
			$thumbnail_file = $original_file;
			if (!strstr($original,BIRDY_URL)) {
				$original = "http://www.klipsam.com/loadinFrame.php?url=".rawurlencode($original);
			}
		}
		//if (!empty($ogimage)) $thumbnail_file = $ogimage;
	}
	$image = /*(empty($ogimage)) ? */$thumbnail_file /*: $ogimage*/;
	//echo $original;
} else {
	$birdy = birdyCMS::getInstance();
	$birdy->outputAlert("Invalid URL!");
	$birdy->loadPage($_SERVER['HTTP_REFERER']);
}
?>