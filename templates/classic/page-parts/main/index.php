<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//=============================================================================
include_once("user_profile.php");

?>
<div class="span3">
	<div class="span1_of_3 profile-left">
		<?php
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

	<div id="result" class="span2_of_3 profile-right">
	<?php
	//echo "SELECT * FROM klips WHERE ".$klips_sql[0]." ORDER BY id DESC<br />";
	//print_r($klips_sql[1]);
		$total = 0;
		$birdy->total_users = $birdy->total_users>15 ? 15 : $birdy->total_users;
		if (isset($_SESSION['klipper_search'])) {
			/*
			echo "<h2 class='style' style='font-size:8px'><a>Search results: ".$_SESSION['klipper_search']['query']." (klippers)</a>
				<a class='klip-submit klip-button remove-filter' style='float:none;display:block !important;' href='?remove_filter=klippers'>remove this search</a></h2>
				</h2>";
			*/
			$total = count($_SESSION['klipper_search']);
			$actual_row_count = $total<$birdy->total_users ? $total : $birdy->total_users;
			for ($i=0; $i<$actual_row_count; $i++) {
				if (isset($_SESSION['klipper_search'][$i])) {
					$klipper = $_SESSION['klipper_search'][$i];
					include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klipper-block.php');
					echo "<div class='clear'></div>";
				}
			}
		} elseif ($klips) {
			$total = count($klips);
			$actual_row_count = $total<$birdy->total_users ? $total : $birdy->total_users;
			for ($i=0; $i<$actual_row_count; $i++) {
				if (isset($klips[$i])) {
					$klip = $klips[$i];
					include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block.php');
					echo "<div class='clear'></div>";
				}
			}
		}
	?>
	</div>
<div class="clear"></div>
