<?php 
$remoteImage = htmlentities(rawurldecode($_GET['url']));
$imginfo = @getimagesize($remoteImage);
if (!is_array($imginfo)) {
	$remoteImage = "images/URLs/default.jpg";
	$imginfo =  getimagesize("images/URLs/default.jpg");
}
// echo "<pre>";
// print_r($imginfo);
// echo "</pre>";
$image = false;
switch ($imginfo['mime']) {
	case "image/jpeg":
		$image = imagecreatefromjpeg($remoteImage);
		$type = "imagejpeg";
		$filename = time().mt_rand().'.jpg';
		break;
	case "image/png":
		$image = imagecreatefrompng($remoteImage);
		$type = "imagepng";
		$filename = time().mt_rand().'.png';
		break;
	case "image/gif";
		$image = imagecreatefromgif($remoteImage);
		$type = "imagegif";
		$filename = time().mt_rand().'.gif';
		break;
}
if ($image) {

  ob_start();
  header( "Content-type: ".$imginfo['mime'] );
  $type( $image, NULL, 100 );
  imagedestroy( $image );
  $i = ob_get_clean();
  if ($remoteImage == "images/URLs/default.jpg") echo "<p style='width:100%;text-align:center;'>Image not found!</p>";
  echo "<img src='data:image/jpeg;base64," . base64_encode( $i )."'>"; //saviour line!
  
} else {

	header("Location: ".$remoteImage);

}