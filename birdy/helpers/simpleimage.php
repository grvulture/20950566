<?php
/**
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/
 
class SimpleImage {
   
   var $image;
   var $image_type;
   var $compression = 75;

	function image_exist($url, $check_mime_type = true) {
		if (!file_exists($url)) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);

			$result = curl_exec($ch);

			if (!preg_match('/^HTTP\/1.1 200 OK/i', $result)) {
				// status != 200, handle redirects, not found, forbidden and so on
				return false;
			}
			if ($check_mime_type && !preg_match('/^Content-Type:\\s+image\/.*$/im', $result)) {
				// mime != image/*
				return false;
			}
		}
		return true;
	}


  function getimagesize_remote($image_url,$check_if_exists=true) {
  if ($check_if_exists) $check_if_exists = $this->image_exist($image_url);
  else $check_if_exists = true;
  
	if ($check_if_exists) {
      $gis = array();
      $ext = pathinfo($image_url,PATHINFO_EXTENSION);
      switch ($ext) {
		case 'gif':
		case 'GIF':
			if ($im = imagecreatefromgif($image_url))
			$gis[2] = IMAGETYPE_GIF;
			break;
		case 'png':
		case 'PNG':
			if ($im = imagecreatefrompng($image_url))
			$gis[2] = IMAGETYPE_PNG;
			break;
		case 'jpg':
		case 'jpeg':
		case 'JPG':
		case 'JPEG':
		default:
			if ($im = imagecreatefromjpeg($image_url))
			$gis[2] = IMAGETYPE_JPEG;
			break;
	  }
      if (empty($gis[2])) { return false; }
      $gis[0] = ImageSX($im);
      $gis[1] = ImageSY($im);
	  // array member 3 is used below to keep with current getimagesize standards
      $gis[3] = "width={$gis[0]} height={$gis[1]}";
      ImageDestroy($im);
      return $gis;
     }
     
     return false;
  }
  
  /**
  *	This for future use
  */
  /*
  function validateImage($image) {
  	$mime = array('image/gif' => 'gif',
                    'image/jpeg' => 'jpeg',
                    'image/png' => 'png',
                    'application/x-shockwave-flash' => 'swf',
                    'image/psd' => 'psd',
                    'image/bmp' => 'bmp',
                    'image/tiff' => 'tiff',
                    'image/jp2' => 'jp2',
                    'image/iff' => 'iff',
                    'image/vnd.wap.wbmp' => 'bmp',
                    'image/xbm' => 'xbm',
                    'image/vnd.microsoft.icon' => 'ico');
    // Get File Extension (if any)
    $ext = strtolower(substr(strrchr($image, "."), 1));
    // Check for a correct extension. The image file hasn't an extension? Add one
    $file_info = $this->getimagesize_remote($image);
  
        if(empty($file_info)) // No Image?
        {
        	return false;
        }
        else // An Image?
        {
        	  $file_mime = $file_info['mime'];
            //print_r($file_mime);
           if($file_mime == 'image/jpeg' || $file_mime == 'image/png' || $file_mime == 'image/vnd.microsoft.icon' || $file_mime == 'image/tif' || $file_mime == 'image/bmp' || $file_mime == 'image/gif')
           {
        	   return true;
           }
          return false;
        }
  }
  */
   function setQuality($quality) {
	$this->compression = $quality;
   }
   function load($filename,$check_if_exists=true) {
      $image_info = $check_if_exists ? $this->getimagesize_remote($filename,$check_if_exists) : getimagesize($filename);
      $this->false = ($image_info) ? false : true;
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $permissions=null) {
      $compression = $this->compression;
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }   
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }   
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100; 
      $this->resize($width,$height);
   }
   
	function resize($width,$height) { 
		$new_image = imagecreatetruecolor($width, $height); 
		if( $this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG ) { 
			$current_transparent = imagecolortransparent($this->image); 
			if($current_transparent != -1) { 
				$transparent_color = imagecolorsforindex($this->image, $current_transparent); 
				$current_transparent = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']); 
				imagefill($new_image, 0, 0, $current_transparent); imagecolortransparent($new_image, $current_transparent); 
			} elseif( $this->image_type == IMAGETYPE_PNG) { 
				imagealphablending($new_image, false); 
				$color = imagecolorallocatealpha($new_image, 0, 0, 0, 127); 
				imagefill($new_image, 0, 0, $color); 
				imagesavealpha($new_image, true); 
			} 
		} 
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight()); 
		$this->image = $new_image;	
	}
	
	function close() {
      ImageDestroy($this->image);
   }
}
?>