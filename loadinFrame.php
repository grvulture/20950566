<?php 
$remoteImage = htmlentities(rawurldecode(base64_decode($_GET['url'])));
// $remoteImage = explode("?", $remoteImage);
// $remoteImage = $remoteImage[0];
$imginfo = exif_imagetype($remoteImage);

$image = false;
switch ($imginfo) {
	case "IMAGETYPE_JPEG":
		$image = imagecreatefromjpeg($remoteImage);
		$imginfo = "image/jpeg";
		$type = "imagejpeg";
		$filename = time().mt_rand().'.jpg';
		break;
	case "IMAGETYPE_PNG":
		$image = imagecreatefrompng($remoteImage);
		$imginfo = "image/png";
		$type = "imagepng";
		$filename = time().mt_rand().'.png';
		break;
	case "IMAGETYPE_GIF";
		$image = imagecreatefromgif($remoteImage);
		$imginfo = "image/gif";
		$type = "imagegif";
		$filename = time().mt_rand().'.gif';
		break;
}

if ($image) {

  ob_start();
  header( "Content-type: ".$imginfo );
  $type( $image, NULL, 100 );
  imagedestroy( $image );
  $i = ob_get_clean();
  //if ($remoteImage == "images/URLs/default.jpg") echo "<p style='width:100%;text-align:center;'>Image not found!</p>";
  echo "<img src='data:".$imginfo.";base64," . base64_encode( $i ). "'>"; //saviour line!
  
} else {

	header("Location: ".$remoteImage);

}