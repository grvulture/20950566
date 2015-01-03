<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//=============================================================================
include_once("user_profile.php");

$friend_requests = $db->loadObjectlist("SELECT requester AS id FROM friends WHERE requested=:user_id AND accepted=0",array(":user_id"=>$user->id));

?>
<div class="span3">
	<div class="span1_of_3 profile-left">
<?php
if ($birdy->browser=='web') {
	if (!$user->isLoggedIn()) {
		include("no_user_left.php");
	} else {
		include("yes_user_left.php");
	}
	?>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<?php 
		$klips = left_column('index');
		?>
	</div>
<?php
}

?>
	<div class="span2_of_3 profile-right">
	<?php
		if (count($friend_requests)) {
			echo "<h2 class='top style'><a>friend requests</a></h2>";
			foreach ($friend_requests as $klipper) {
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'friend-request-block.php');
				echo "<div class='clear'></div>";
			}
		} else {
			echo "<h2 class='top style'><a>You do not have any friend requests yet!</a></h2>";
		}
	?>
	</div>
<div class="clear"></div>
