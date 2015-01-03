<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
//echo "<pre>";print_r($_REQUEST);echo "</pre>";
//@TODO: REMEMBER: ONE REPORT REMOVES THE KLIP! KLIPPERS WITH REPORTED CONTENT SHOULD BE FORBIDDEN TO POST MORE KLIPS
// REPORTERS WITH FALSE REPORTS SHOULD BE FORBIDDEN TO SUBMIT NEW REPORTS
function klips_sql($klipper=false) {
	$db = birdyDB::getInstance();

	$klipper_field = empty($klipper) ? '' : " AND user_id=:user_id ";
	$klipper_value = empty($klipper) ? array() : array(":user_id"=>$klipper->id);
	
//============================================================================
	
	if (!isset($_SESSION['date_field'])) {
		$_SESSION['date_field'] = '';
		$_SESSION['date_value'] = array();
		$_SESSION['date_query'] = '';
	}
	$date_field = isset($_REQUEST['date']) ? " AND creation_date=:date " : $_SESSION['date_field'];
	$date_value = isset($_REQUEST['date']) ? array(":date"=>date("Y-m-d",$_REQUEST['date'])) : $_SESSION['date_value'];
	$_SESSION['date_field'] = $date_field;
	$_SESSION['date_value'] = $date_value;
	$_SESSION['date_query'] = isset($_REQUEST['date']) ? date("F d, Y",$_REQUEST['date']) : $_SESSION['date_query'];
	
//============================================================================
	
	if (!isset($_SESSION['type_field'])) {
		$_SESSION['type_field'] = '';
		$_SESSION['type_value'] = array();
		$_SESSION['type_query'] = '';
	}
	$type_field = isset($_REQUEST['type']) ? " AND type=:type " : $_SESSION['type_field'];
	$type_value = isset($_REQUEST['type']) ? array(":type"=>$_REQUEST['type']) : $_SESSION['type_value'];
	$_SESSION['type_field'] = $type_field;
	$_SESSION['type_value'] = $type_value;
	$_SESSION['type_query'] = isset($_REQUEST['type']) ? htmlentities(ucfirst($_REQUEST['type'])) : $_SESSION['type_query'];
	
//============================================================================

	if (!isset($_SESSION['parent_tag_field']) || (isset($_SESSION['parent_tag_value'][':tag_id']) && $_SESSION['parent_tag_value'][':tag_id']=='[]')) {
		$_SESSION['parent_tag_field'] = '';
		$_SESSION['parent_tag_value'] = array();
		$_SESSION['parent_tag_query'] = '';
	}
	$parent_tag	   = isset($_REQUEST['parent_tag']) ? $db->loadResult("SELECT tag FROM tags WHERE id=:tag_id",array(":tag_id"=>$_REQUEST['parent_tag'])) : '';
	$parent_tag_field = isset($_REQUEST['parent_tag']) ? " AND parent_tag=:tag_id " : $_SESSION['parent_tag_field'];
	$parent_tag_value = isset($_REQUEST['parent_tag']) ? array(":tag_id"=>$_REQUEST['parent_tag']) : $_SESSION['parent_tag_value'];
	$_SESSION['parent_tag_field'] = $parent_tag_field;
	$_SESSION['parent_tag_value'] = $parent_tag_value;
	$_SESSION['parent_tag_query'] = isset($_REQUEST['parent_tag']) ? ucfirst($parent_tag) : $_SESSION['parent_tag_query'];
	
//============================================================================

	if (!isset($_SESSION['tag_field'])) {
		$_SESSION['tag_field'] = '';
		$_SESSION['tag_value'] = array();
		$_SESSION['tag_query'] = '';
	}
	$tag	   = isset($_REQUEST['tag']) ? $db->loadResult("SELECT tag FROM tags WHERE id=:tag_id",array(":tag_id"=>$_REQUEST['tag'])) : '';
	$tag_field = isset($_REQUEST['tag']) ? " AND (tags LIKE :liketag1 OR tags LIKE :liketag2  OR tags LIKE :liketag3 OR tags=:tag) " : $_SESSION['tag_field'];
	$tag_value = isset($_REQUEST['tag']) ? array(":tag"=>$tag,
													":liketag1"=>'%,'.$tag.',%',
													":liketag2"=>$tag.',%',
													":liketag3"=>'%,'.$tag
											) : $_SESSION['tag_value'];
	$_SESSION['tag_field'] = $tag_field;
	$_SESSION['tag_value'] = $tag_value;
	$_SESSION['tag_query'] = isset($_REQUEST['tag']) ? ucfirst(stripslashes($tag)) : $_SESSION['tag_query'];
	
//============================================================================
	
	if (isset($_REQUEST['tag'])) {
		$_SESSION['request_tag'][$_REQUEST['tag']] = $tag;
	} else {
		unset($_SESSION['request_tag']);
	}
	if (isset($_REQUEST['parent_tag'])) {
		$_SESSION['request_parent_tag'][$_REQUEST['parent_tag']] = $parent_tag;
	} else {
		unset($_SESSION['request_parent_tag']);
	}
	
//============================================================================
	$search_field = isset($_SESSION['search_field']) ? $_SESSION['search_field'] : '';
	$search_value = isset($_SESSION['search_value']) ? $_SESSION['search_value'] : array();
//============================================================================
	
	$remove_filter = (isset($_REQUEST['remove_filter'])) ? $_REQUEST['remove_filter'] : "";
	if (empty($_SESSION['date_query'])) $remove_filter='date';
	//if (!empty($_SESSION['date_query'])) {
		if ($remove_filter=='date') {
			$date_field = '';
			$date_value = array();
			unset($_SESSION['date_field']);
			unset($_SESSION['date_value']);
			unset($_SESSION['date_query']);
		} else {
			echo "<h2 style='font-size:20px;' class='style top1'>Klips from ".$_SESSION['date_query']."<br />
				<a class='klip-submit klip-button remove-filter' href='?remove_filter=date'>remove this filter</a></h2>
				<div class='ui divider'>
				</div>
				";
		}
	//}
	
	$remove_filter = (isset($_REQUEST['remove_filter'])) ? $_REQUEST['remove_filter'] : $remove_filter;
	if (empty($_SESSION['type_query'])) $remove_filter='type';
	//if (!empty($_SESSION['type_query'])) {
		if ($remove_filter=='type') {
			$type_field = '';
			$type_value = array();
			unset($_SESSION['type_field']);
			unset($_SESSION['type_value']);
			unset($_SESSION['type_query']);
		} else {
			echo "<h2 style='font-size:20px;' class='style top1'>".$_SESSION['type_query']."s<br />
				<a class='klip-submit klip-button remove-filter' href='?remove_filter=type'>remove this filter</a></h2>
				<div class='ui divider'>
				</div>
				";
		}
	//}
	
	$remove_filter = (isset($_REQUEST['remove_filter'])) ? $_REQUEST['remove_filter'] : $remove_filter;
	if (empty($_SESSION['parent_tag_query'])) $remove_filter='parent_tag';
	//if (!empty($_SESSION['parent_tag_query'])) {
		if ($remove_filter=='parent_tag') {
			$parent_tag_field = '';
			$parent_tag_value = array();
			unset($_SESSION['parent_tag_field']);
			unset($_SESSION['parent_tag_value']);
			unset($_SESSION['parent_tag_query']);
		} else {
			echo "<h2 style='font-size:20px;' class='style top1'>Klips under ".$_SESSION['parent_tag_query']."<br />
				<a class='klip-submit klip-button remove-filter' href='?remove_filter=parent_tag'>remove this filter</a></h2>
				<div class='ui divider'>
				</div>
				";
		}
	//}
	
	$remove_filter = (isset($_REQUEST['remove_filter'])) ? $_REQUEST['remove_filter'] : $remove_filter;
	if (empty($_SESSION['tag_query'])) $remove_filter='tag';
	//if (!empty($_SESSION['tag_query'])) {
		if ($remove_filter=='tag') {
			$tag_field = '';
			$tag_value = array();
			unset($_SESSION['tag_field']);
			unset($_SESSION['tag_value']);
			unset($_SESSION['tag_query']);
		} else {
			echo "<h2 style='font-size:20px;' class='style top1'>Klips tagged as ".$_SESSION['tag_query']."<br />
				<a class='klip-submit klip-button remove-filter' href='?remove_filter=tag'>remove this filter</a></h2>
				<div class='ui divider'>
				</div>
				";
		}
	//}
	
	$remove_filter = (isset($_REQUEST['remove_filter'])) ? $_REQUEST['remove_filter'] : $remove_filter;
	if (empty($_SESSION['search_query'])) $remove_filter='search';
	//if (!empty($_SESSION['search_query'])) {
		if ($remove_filter=='search') {
			$search_field = '';
			$search_value = array();
			unset($_SESSION['search_query']);
			unset($_SESSION['search_field']);
			unset($_SESSION['search_value']);
		} else {
			echo "<h2 style='font-size:20px;' class='style top1'>Search results: ".$_SESSION['search_query']."<br />
				<a class='klip-submit klip-button remove-filter' href='?remove_filter=search'>remove this filter</a></h2>
				<div class='ui divider'>
				</div>
				";
		}
	//}
	
	$fields = "reports<:reports AND message=0".$klipper_field.$date_field.$type_field.$parent_tag_field.$tag_field.$search_field;
	$values = array_merge(
		array(":reports"=>_MAX_REPORTS),
		$klipper_value,
		$date_value,
		$type_value,
		$parent_tag_value,
		$tag_value,
		$search_value
	);
// 	echo "<br />NOW:<br />";
// 	echo $fields;
// 	echo "<pre>";print_r($values);echo "</pre>";
	return array($fields, $values);
}
?>
