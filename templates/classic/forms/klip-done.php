<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
	$type = trim($_POST['what']);
	$type = explode(":",$type);
	$type = str_replace("_"," ",$type[0]);
	if (substr($type,-1)=='s') $type = substr($type,0,-1);
	$url = trim($_POST['url']);
	$title = trim($_POST['klip_title']);
	$description = trim($_POST['klip_description']);
	$author = trim($_POST['author']);
	$thumbnail = trim($_POST['thumbnail']);
	$bignail = trim($_POST['bignail']);
	$user_id = $user->id;
	$privacy = $_POST['privacy'];
	$parent_tag = $_POST['parent_tag'];
	$parent_tag = ucfirst(trim(str_replace(array("[","]",'"',"\\"),"",$parent_tag)));
	if (empty($parent_tag)) $parent_tag = 0;
	if (!empty($parent_tag) && !is_numeric($parent_tag)) {
		$duplicate = $db->loadResult("SELECT id FROM tags WHERE tag=:tag",array(":tag"=>$parent_tag));
		if (empty($duplicate)) {
			$db->insert('tags',array('tag'=>$parent_tag,'created_by'=>$user_id));
			$parent_tag = $db->lastInsertId();
		} else {
			$parent_tag = $duplicate;
		}
	}
	$tags = $_POST['klip_tags'];
	$tags = trim(str_replace(array("[","]",'"',"\\"),"",$tags));
	$tags_table = explode(",",$tags);
	if (count($tags_table)>0) {
		foreach($tags_table as $tag) {
			if (!empty($tag)) {
				$tag = ucfirst($tag);
				$duplicate = $db->loadResult("SELECT tag FROM tags WHERE tag=:tag",array(":tag"=>$tag));
				if (empty($duplicate)) $rows[] = array($tag,$user_id);
			}
		}
		if (!empty($rows)) {
			$db->insertMultiple('INSERT INTO tags (tag,created_by)',$rows);
		}
	}
	$creation_date = date("Y-m-d");
	$creation_array = array(
		':type'=>$type, 
		':url'=>$url, 
		':title'=>$title, 
		':description'=>$description,
		':author'=>$author, 
		':thumbnail'=>$thumbnail, 
		':bignail'=>$bignail, 
		':user_id'=>$user_id, 
		':parent_tag'=>$parent_tag, 
		':tags'=>$tags, 
		':privacy'=>$privacy,
		':creation_date'=>$creation_date,
		':creation_timestamp'=>date("Y-m-d H:i:s",time())
	);
	if ($_POST['reklip']>0) {
		$_POST['edit']=0;
		$creation_array[":reklip"] = $_POST['reklip'];
	}
	$noticetitle = stripslashes(str_replace($user->used_name.' klipped:', 'Klip', $title));
	if ($_POST['edit']>0) {
		$birdy->outputNotice($noticetitle." edited successfully!");
		$creation_fields = 'edit_date=:creation_date, edit_timestamp=:creation_timestamp';
		$query = $db->prepare("
			UPDATE klips SET
			type=:type, url=:url, title=:title, description=:description, author=:author, thumbnail=:thumbnail, bignail=:bignail, 
			user_id=:user_id, parent_tag=:parent_tag, tags=:tags, privacy=:privacy, ".$creation_fields."
			WHERE
			id=:edit"
		);
		$creation_array[':edit'] = $_POST['edit'];
	} else {
		$birdy->outputNotice($noticetitle." klipped successfully!");
		$creation_fields = 'creation_date, creation_timestamp';
		if ($_POST['reklip']>0) {
			$creation_fields.=',reklip';
			$reklip_value = ',:reklip';
		} else $reklip_value = '';
		$query = $db->prepare("
			INSERT INTO klips 
			(type, url, title, description, author, thumbnail, bignail, user_id, parent_tag, tags, privacy, ".$creation_fields.")
			VALUES
			(:type, :url, :title, :description, :author, :thumbnail, :bignail, :user_id, :parent_tag, :tags, :privacy, :creation_date, :creation_timestamp".$reklip_value.")"
		);
	}
	$query->execute($creation_array);
	if (strstr($_SESSION['loadPage'],BIRDY_URL)) {
		$loadPage = $_SESSION['loadPage'];
		unset($_SESSION['loadPage']);
		$birdy->loadPage($loadPage.'?done-klipping=1');
	} else {
		//close the current window. This useful for the bookmarklet!
		$birdy->addScriptToBottom("
			open(location, '_self').close();
		");
	}
