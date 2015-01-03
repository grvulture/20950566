<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
if (!empty($_POST['ajax_session'])) {
	$SESSION = (array) json_decode($_POST['ajax_session']);
	$SESSION = $birdy->recur_replace('dipla-eisag','"',$_SESSION);
}
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
$php_page = $_POST['php_page'];
$klips_sql = array();
$klips_sql[0] = $_POST['klips_sql0'];
$klips_sql[1] = (array) json_decode($_POST['klips_sql1']);
$requested_page = isset($_POST['page_num']) ? $_POST['page_num'] : 1;
$actual_count	= isset($_POST['actual_count']) ? $_POST['actual_count'] : 10;
$start = ($requested_page-1)*$actual_count;

$klips = $db->loadObjectlist("SELECT * FROM klips WHERE ".$klips_sql[0]." ORDER BY id DESC LIMIT ".$start.",".$actual_count,$klips_sql[1]);

		if (isset($_SESSION['klipper_search']) && $php_page!='profile') {
			foreach($_SESSION['klipper_search'] as $klipper) {
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klipper-block.php');
				echo "<div class='clear'></div>";
			}
		} elseif ($klips) {
			foreach($klips as $klip) {
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block.php');
				echo "<div class='clear'></div>";
				?>
				<script type="text/javascript">
					$(document).on('click', '#comment_show'+<?php echo $klip->id; ?>, function(){ 
							style = document.getElementById('comments'+<?php echo $klip->id; ?>).style.display;
							if (style=='none') {
								$('#comments'+<?php echo $klip->id; ?>).slideDown('slow');
								$('#comment'+<?php echo $klip->id; ?>).focus();
								//scrollToAnchor('comments".$klip->id."');
								//document.getElementById('comments".$klip->id."').style.display = 'block';
							} else {
								$('#comments'+<?php echo $klip->id; ?>).slideUp('slow');
								//document.getElementById('comments".$klip->id."').style.display = 'none';
							}
							return false;
					});
				</script>
				<?php
			}
		}
?>
