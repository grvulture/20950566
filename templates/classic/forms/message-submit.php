<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
	//echo "<pre>";print_r($_POST);echo "</pre>";
	if (isset($_REQUEST['external'])) $_POST['klip-url'] = rawurldecode($_REQUEST['external']);
	/**
	* Edits and Reklips are done on klip-submit
	* Editing of klips inside a message is possible, and will be done through klip-submit
	* Reklipping will be possible later "On a private message" & "On another Klipper's profile"
	* And will be done through klip-submit
	*/
	$klip_url = $_POST['klip-url'];
	$klip_note = isset($_POST['klip-note']) ? $_POST['klip-note'] : false;
	$description = '';
	
	if (!empty($klip_url)) {
		if (!strstr($klip_url,'://')) $klip_url = "http://".$klip_url;
		if (parse_url($klip_url)) {//$birdy->url_exists($klip_url)) {
		
			// see if this url exists, phantomjs it, take screenshot, get title, get description
			include(BIRDY_BASE.DS.'images'.DS.'URLs'.DS.'create_screenshots.php');
			//$file = 'http://api.webthumbnail.org?width=400&height=320&format=png&browser=firefox&url='.$what;
			
			$title 	= $title;
			$descr	= $descr;
			$author = $author;
			$description = $descr;
			
			if (empty($what)) $what = 'URL';
			
			$thumbnail = $image;
			
		} else {
			$klip_url = false;
			$klip_note= false;
		}
	}
	
	if (!empty($_FILES['klip-file']) || !empty($klip_note)) {
		if (!empty($_FILES['klip-file']["name"])) {
			$allowedExts = array("gif", "GIF", "jpeg", "JPEG", "jpg", "JPG", "png", "PNG");
			$temp = explode(".", $_FILES["klip-file"]["name"]);
			$extension = end($temp);
			if (!in_array($extension, $allowedExts)) $extension = 'jpg';
	// 		if ((($_FILES["klip-file"]["type"] == "image/gif")
	// 		|| ($_FILES["klip-file"]["type"] == "image/jpeg")
	// 		|| ($_FILES["klip-file"]["type"] == "image/jpg")
	// 		|| ($_FILES["klip-file"]["type"] == "image/pjpeg")
	// 		|| ($_FILES["klip-file"]["type"] == "image/x-png")
	// 		|| ($_FILES["klip-file"]["type"] == "image/png"))
	// 		&& ($_FILES["klip-file"]["size"] < 4000000)
	// 		&& in_array($extension, $allowedExts))
			if ($_FILES["klip-file"]["size"] < 4000000)
			{
				if ($_FILES["klip-file"]["error"] > 0)
				{
				$birdy->outputAlert($_FILES["file"]["error"]);
				}
				else
				{
				/*
				echo "Upload: " . $_FILES["klip-file"]["name"] . "<br>";
				echo "Type: " . $_FILES["klip-file"]["type"] . "<br>";
				echo "Size: " . ($_FILES["klip-file"]["size"] / 1024) . " kB<br>";
				echo "Temp file: " . $_FILES["klip-file"]["tmp_name"] . "<br>";
				*/
				$user_dir = BIRDY_BASE.DS.'images'.DS.'uploads'.DS.$user->id;
				$birdy->createDir($user_dir);
				$date = date("Y-m-d");
				$path = $user_dir.DS.$date;
				$birdy->createDir($user_dir.DS.$date);
				$file = date("H-i-s").'.'.$extension;
				$original = $path.DS.$file;
				$thumbnail = $path.DS.'thumb-'.$file;
				//echo "ORIGINAL: <pre>$original</pre> <br />";
				//echo "THUMBNAIL: <pre>$thumbnail</pre> <br />";
				
				$URLtoCurrentDate = str_replace(DS,"/",str_replace(BIRDY_BASE,"",$path)).'/';
				$URLtoCurrentDateThumbnail = $URLtoCurrentDate.'thumb-';

				$tempFile = $_FILES["klip-file"]["tmp_name"];
				include_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'simpleimage.php');
				$image = new SimpleImage();
				$image->load($tempFile, false);
				$image->setQuality(98);
				if (!$image->false && $image->getWidth()>816) $image->resizeToWidth(816);
				if ($image->image) {
					$image->save($original);
					$image->setQuality(90);
					if (!$image->false && $image->getWidth()>408) $image->resizeToWidth(408);
					$image->save($thumbnail);
				} else {
					$birdy->outputWarning("Not a valid image file. Please try again...");
				}
				
				$original = BIRDY_URL.$URLtoCurrentDate.$file;
				$thumbnail= BIRDY_URL.$URLtoCurrentDate.'thumb-'.$file;
				}
			}
			else
			{
			$birdy->outputAlert("Invalid file! Please upload an image file not larger than 3.5 MBs");
			}
		}
		if (isset($_POST['klip-note']) && !empty($_FILES['klip-file']["name"])) {
			if ($_FILES["klip-file"]["size"] < 4000000 && empty($_FILES["klip-file"]["error"]))
				$_POST['klip-note'] = $_POST['klip-note']."\r\n".'[img]'.$original.'[/img]';
		}
		// maybe here we could add any file types, so people can keep here files they need to store somewhere
		// example PDF, TXT, etc.
		$content = trim($_POST['klip-note']);
		// get it through bbCode!
		$content = birdyComments::BBCode($content,"Article");
		$description = $content."\r\n".$description;
		$descr = $description;
		$image = empty($thumbnail) ? $user->avatar : $thumbnail;
		$original = empty($original) ? $user->avatar : $original;
		$title = isset($title) ? $title : $user->used_name . ' klipped:';
		$author = isset($author) ? $author : '';
		if (empty($what)) {
			$what = empty($_FILES['klip-file']["name"]) ? 'klip-note' : 'klip-photo';
		}
		$URLtoload = isset($URLtoload) ? $URLtoload : BIRDY_URL.'klipper/'.$user->username;
	}
	
	include "message-done.php";
