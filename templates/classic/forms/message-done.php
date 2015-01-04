<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
	$type = trim($what);
	$type = explode(":",$type);
	$type = str_replace("_"," ",$type[0]);
	if (substr($type,-1)=='s') $type = substr($type,0,-1);
	$url = trim($URLtoload);
	$title = trim($title);
	$description = trim($description);
	$author = trim($author);
	$thumbnail = trim($image);
	$bignail = trim($original);
	$user_id = $user->id;
	$privacy = 2;
	$parent_tag = 0;
	$tags = '';
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
	$noticetitle = stripslashes(str_replace($user->used_name.' klipped:', 'Message', $title));
	$birdy->outputNotice($noticetitle." sent successfully!");
	$creation_fields = 'creation_date, creation_timestamp';
	$reklip_value = '';
	$query = $db->prepare("
		INSERT INTO klips 
		(type, url, title, description, author, thumbnail, bignail, user_id, parent_tag, tags, privacy, ".$creation_fields.")
		VALUES
		(:type, :url, :title, :description, :author, :thumbnail, :bignail, :user_id, :parent_tag, :tags, :privacy, :creation_date, :creation_timestamp".$reklip_value.")"
	);
	$query->execute($creation_array);
	
	$klip_id = $db->lastInsertId();
	$query = $db->insert("messages",array(
		"klip_id"=>$klip_id,
		"sender"=>$user_id,
		"receiver"=>intval($_POST['receiver'])
		)
	);
	
	$message_id = $db->lastInsertId();
	$query = $db->update("klips","message=:message","id=:id",array(
		":message"=>$message_id,
		":id"=>$klip_id
		)
	);
	
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
