<?php
include_once(BIRDY_TEMPLATE_BASE.DS."page-parts".DS."main".DS."klips_sql.php");
if (!empty($_POST['ajax_session'])) $_SESSION = json_decode($_POST['ajax_session']);

$klips_sql = klips_sql();
$requested_page = isset($_POST['page_num']) ? $_POST['page_num'] : 1;
$actual_count	= isset($_POST['actual_count']) ? $_POST['actual_count'] : 10;
$start = ($requested_page-1)*$actual_count;
$klips = $db->loadObjectlist("SELECT * FROM klips WHERE ".$klips_sql[0]." ORDER BY id DESC LIMIT ".$start.",".$actual_count,$klips_sql[1]);

		if (isset($_SESSION['klipper_search'])) {
			$total = count($_SESSION['klipper_search']);
			for ($i=$start; $i<$actual_count; $i++) {
				$klipper = $_SESSION['klipper_search'][$i];
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klipper-block.php');
				echo "<div class='clear'></div>";
			}
		} elseif ($klips) {
			$total = count($klips);
			for ($i=$start; $i<$actual_count; $i++) {
				$klip = $klips[$i];
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block.php');
				echo "<div class='clear'></div>";
			}
		}
?>