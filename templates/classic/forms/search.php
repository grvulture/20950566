<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
$search_query = htmlentities($_POST['search_query']);
$search_option = htmlentities($_POST['search_option']);
$parent_search = '';
$parent_fields = array();
$tag_search = '';
$tag_fields = array();
$klip_search = '';
$klip_fields = array();
$klipper_search = '';
$klipper_fields = array();

if ($search_option=="parent_tags") {
	$parent_tags = strstr($search_query,',') ? explode(',',$search_query) : array($search_query);
	foreach ($parent_tags as $parent_tag) {
		$parent_ids = $db->loadObjectlist("SELECT id FROM tags WHERE tag LIKE :tag",array(":tag"=>'%'.ucfirst(trim($parent_tag)).'%'));
		if (!empty($parent_ids)) {
			foreach($parent_ids as $parent_id) {
				$parent_id = $parent_id->id;
				if ($parent_id>0) {
					if (empty($parent_search)) $parent_search = " AND (parent_tag=:parent_".$parent_id;
					else $parent_search.= " OR parent_tag=:parent_".$parent_id;
					$parent_fields[":parent_".$parent_id] = $parent_id;
				}
			}
		}
	}
	if (!empty($parent_search)) $parent_search.=")";
}
elseif ($search_option=="tags") {
	$tags = strstr($search_query,',') ? explode(',',$search_query) : array($search_query);
	$i = 0;
	foreach ($tags as $tag) {
		$tag = htmlentities(trim($tag));
		$i++;
		if (empty($tag_search)) $tag_search = " AND (tags LIKE :liketag1_".$i." OR tags LIKE :liketag2_".$i."  OR tags LIKE :liketag3_".$i." OR tags=:tag".$i." ";
		else $tag_search.= " OR tags LIKE :liketag1_".$i." OR tags LIKE :liketag2_".$i."  OR tags LIKE :liketag3_".$i." OR tags=:tag".$i." ";
		$tag_fields[":tag".$i] = $tag;
		$tag_fields[":liketag1_".$i] = '%,'.$tag.',%';
		$tag_fields[":liketag2_".$i] = $tag.',%';
		$tag_fields[":liketag3_".$i] = '%,'.$tag;
	}
	if (!empty($tag_search)) $tag_search.=")";
}
elseif ($search_option=="klips") {
	$tags = strstr($search_query,',') ? explode(',',$search_query) : array($search_query);
	$i = 0;
	foreach ($tags as $tag) {
		$tag = htmlentities(trim($tag));
		$i++;
		if (empty($klip_search)) $klip_search = " AND (title LIKE :title_".$i." OR description LIKE :description_".$i." ";
		else $klip_search.= " OR title LIKE :title_".$i." OR description LIKE :description_".$i." ";
		$klip_fields[":title_".$i] = '%'.$tag.'%';
		$klip_fields[":description_".$i] = '%'.$tag.'%';
	}
	if (!empty($klip_search)) $klip_search.=")";
}
elseif ($search_option=="klippers") {
	if (!$user->isLoggedIn()) $birdy->outputWarning("You need to login to search Klipsam profiles");
	else {
		$names = strstr($search_query,' ') ? explode(' ',$search_query) : array($search_query);
		$i = 0;
		foreach ($names as $name) {
			$name = htmlentities($name);
			$i++;
			if (empty($klipper_search)) $klipper_search = " AND (first_name LIKE :name_".$i." OR last_name LIKE :name_".$i." OR username LIKE :name_".$i." ";
			else $klipper_search.= " OR first_name LIKE :name_".$i." OR last_name LIKE :name_".$i." OR username LIKE :name_".$i." ";
			$klipper_fields[":name_".$i] = '%'.$name.'%';
		}
		if (!empty($klipper_search)) $klipper_search.=")";
		$_SESSION['klipper_search'] = $db->loadObjectlist("SELECT * FROM birdy_users WHERE active=1 AND suspended=0 ".$klipper_search,$klipper_fields);
		$_SESSION['klipper_search']['query'] = $search_query;
		$birdy->loadPage('/');
		return;
		// redirect to index.php
		// in index.php condition if isset $_SESSION['search_klippers'] and show klippers instead of klips
		// klippers box contains klipper image, klipper name, klipper header, # of klips
	}
}
$_SESSION['search_field'] = $parent_search.$tag_search.$klip_search;
$_SESSION['search_value'] = array_merge($parent_fields,$tag_fields,$klip_fields);
$_SESSION['search_query'] = $search_query.' ('.str_replace("_"," ",$search_option).')';
// echo $_SESSION['search_field'];
// echo "<pre>";print_r($_SESSION['search_value']);echo "</pre>";
// return;
$birdy->loadPage('/');