<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//=============================================================================
include_once("user_profile.php");
$klipper = birdyUser::getInstance(intval($_REQUEST['klipper']));
$klipper_credentials = birdyConfig::$use_real_name ? trim($klipper->first_name.' '.$klipper->last_name) : $klipper->username;
if (empty($klipper_credentials)) $klipper_credentials = $klipper->email;
//============================================================================
$birdy->pageTitle("Followers of ".$klipper_credentials." | Klip your Surf Jam");
$birdy->pageDescription("Klip everything you find while you surf. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
$friends = $db->loadObjectlist("SELECT user_id AS id FROM following WHERE following=:user_id",array(":user_id"=>$klipper->id));

if ($birdy->browser=='web') {
	userLeftColumn($user,$klipper);
}
?>
	<div class="span2_of_3 profile-right">
	<?php
		if (count($friends)) {
			echo "<h2 class='top style'><a>Followers of ".$klipper_credentials."</a></h2>";
			foreach ($friends as $klipper) {
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klipper-block.php');
				echo "<div class='clear'></div>";
			}
		} else {
			echo "<h2 class='top style'><a>".$klipper_credentials." does not have any followers yet!</a></h2>";
		}
	?>
	</div>
<div class="clear"></div>
