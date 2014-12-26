<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
	//echo "<pre>";print_r($_POST);echo "</pre>";
	$edit = isset($_REQUEST['klip']) ? $_REQUEST['klip'] : 0;
	if (!isset($_POST['advanced_edit'])) $advanced_edit = 0; // for handling requests coming from elsewhere than klipit
	else {
		$advanced_edit = $_POST['advanced_edit'];
		$db->insertUpdate("advanced_edit",array('advanced_edit'=>$advanced_edit),array('user_id'=>$user->id));
	}
	// here we add the javascript to submit the form immediately on page load, if advanced_edit=0
	//
	if (!$advanced_edit && !$edit) {
		$formdisplay = 'none';
		$birdy->addScriptToBottom('
		$(document).ready(function() {
			document.submitform.submit();
		});
		');
		?>
		<span id="loading_image" class="info" style="position:relative;top:12px;padding-left:5px;">Please wait ... <img src="<?php echo BIRDY_URL.'images/icons/loading.gif'; ?>" />&nbsp;&nbsp;<span>
		<?php
	} else {
		$formdisplay = 'block';
	}
	//
	$birdy->addScriptFile(BIRDY_URL.'birdy/js/magicsuggest-1.3.1-min.js');
	$birdy->addStyleSheet(BIRDY_URL.'birdy/styles/magicsuggest-1.3.1-min.css');
	$tags = array();
	$tags = $db->loadObjectlist("SELECT * FROM tags ORDER BY tag");
	
	if (isset($_REQUEST['external'])) $_POST['klip-url'] = rawurldecode($_REQUEST['external']);
	$klipDB = empty($edit) ? false : $db->loadObject("SELECT * FROM klips WHERE id=:edit AND user_id=:current_user",array(":edit"=>$edit, ":current_user"=>$user->id));
	$reklip = isset($_REQUEST['reklip']) ? $_REQUEST['reklip'] : 0;
	$klipDB = empty($reklip) ? $klipDB : $db->loadObject("SELECT * FROM klips WHERE id=:edit",array(":edit"=>$reklip));
	
	$klip_url = isset($klipDB->url) ? $klipDB->url : $_POST['klip-url'];
	if (isset($klipDB->url)) {
		if ($klipDB->type=='klip-note' || $klipDB->type==='klip-photo') {
			$klip_url = '';
			$klip_note = $klipDB->type;
		}
	} else {
		$klip_note = isset($_POST['klip-note']) ? $_POST['klip-note'] : false;
	}
	
	echo '<div id="main">';
	echo '<div class="artical-commentbox">';
	echo '<div class="table-form">';
	
	if (!empty($klip_url)) {
		if (!strstr($klip_url,'://')) $klip_url = "http://".$klip_url;
		if (parse_url($klip_url)) {//$birdy->url_exists($klip_url)) {
		
			// see if this url exists, phantomjs it, take screenshot, get title, get description
			include(BIRDY_BASE.DS.'images'.DS.'URLs'.DS.'create_screenshots.php');
			//$file = 'http://api.webthumbnail.org?width=400&height=320&format=png&browser=firefox&url='.$what;
			
			$title 	= isset($klipDB->title) ? stripslashes($klipDB->title) : $title;
			$descr	= isset($klipDB->description) ? stripslashes($klipDB->description) : $descr;
			$author = isset($klipDB->author) ? stripslashes($klipDB->author) : $author;
			$description = $descr;
			if (isset($_POST['klipprivacy'])) $privacy = $_POST['klipprivacy'];
			else {
				$privacy= isset($klipDB->privacy) ? $klipDB->privacy : 0;
				if ($privacy==0) {
					$lastprivacy = $db->loadResult("SELECT privacy FROM klips WHERE user_id=:current_user ORDER BY id DESC LIMIT 1",
					array(":current_user"=>$user->id));
					if (!empty($lastprivacy)) $privacy = $lastprivacy;
				}
			}
			
			$content = '
			<div class="klip-image">
			<img src="'.$image.'" style="max-width:100%" />
			</div>
			<div style="float:right;width:66%">
				<h2 id="title_text" class="klip-heading">'.$title.'</h2>';
			if ($author) {
				$content.='
				<p class="info" style="font-size:11px;margin-bottom:8px;float:right;">'.$author.'</p>
				<div class="clear"></div>';
			}
			$content.='<p id="description_text">'.$descr.'</p></div>';
			
			if (empty($what)) $what = 'URL';
			
		} else {
			birdySession::init();
			$birdy->outputAlert("This URL does not exist!");
			$birdy->loadPage($_SERVER['HTTP_REFERER']);
		}
	}
	elseif (!empty($_FILES['klip-file']) || !empty($klip_note)) {
		if (!empty($_FILES['klip-file']["name"])) {
			$allowedExts = array("gif", "GIF", "jpeg", "JPEG", "jpg", "JPG", "png", "PNG");
			$temp = explode(".", $_FILES["klip-file"]["name"]);
			$extension = end($temp);
			if (!in_array($extension, $allowedExts)) $extension = 'jpg';
	// 		if ((($_FILES["klip-file"]["type"] == "image/gif")
	// 		|| ($_FILES["klip-file"]["type"] == "image/jpeg")
	// 		|| ($_FILES["klip-file"]["type"] == "image/jpg")
	// 		|| ($_FILES["klip-file"]["type"] == "image/pjpeg")
	// 		|| ($_FILES["klip-file"]["type"] == "image/x-png")
	// 		|| ($_FILES["klip-file"]["type"] == "image/png"))
	// 		&& ($_FILES["klip-file"]["size"] < 4000000)
	// 		&& in_array($extension, $allowedExts))
			if ($_FILES["klip-file"]["size"] < 4000000)
			{
			if ($_FILES["klip-file"]["error"] > 0)
				{
				$birdy->outputAlert($_FILES["file"]["error"]);
				}
			else
				{
				/*
				echo "Upload: " . $_FILES["klip-file"]["name"] . "<br>";
				echo "Type: " . $_FILES["klip-file"]["type"] . "<br>";
				echo "Size: " . ($_FILES["klip-file"]["size"] / 1024) . " kB<br>";
				echo "Temp file: " . $_FILES["klip-file"]["tmp_name"] . "<br>";
				*/
				$user_dir = BIRDY_BASE.DS.'images'.DS.'uploads'.DS.$user->id;
				$birdy->createDir($user_dir);
				$date = date("Y-m-d");
				$path = $user_dir.DS.$date;
				$birdy->createDir($user_dir.DS.$date);
				$file = date("H-i-s").'.'.$extension;
				$original = $path.DS.$file;
				$thumbnail = $path.DS.'thumb-'.$file;
				//echo "ORIGINAL: <pre>$original</pre> <br />";
				//echo "THUMBNAIL: <pre>$thumbnail</pre> <br />";
				
				$URLtoCurrentDate = str_replace(DS,"/",str_replace(BIRDY_BASE,"",$path)).'/';
				$URLtoCurrentDateThumbnail = $URLtoCurrentDate.'thumb-';

				$tempFile = $_FILES["klip-file"]["tmp_name"];
				include_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'simpleimage.php');
				$image = new SimpleImage();
				$image->load($tempFile, false);
				$image->setQuality(98);
				if (!$image->false && $image->getWidth()>816) $image->resizeToWidth(816);
				if ($image->image) {
					$image->save($original);
					$image->setQuality(90);
					if (!$image->false && $image->getWidth()>408) $image->resizeToWidth(408);
					$image->save($thumbnail);
				} else {
					$birdy->outputWarning("Not a valid image file. Please try again...");
					$birdy->loadPage();
					return;
				}
				
				$original = BIRDY_URL.$URLtoCurrentDate.$file;
				}
			}
			else
			{
			$birdy->outputAlert("Invalid file! Please upload an image file not larger than 3.5 MBs");
			$birdy->loadPage();
			return;
			}
		}
		if (isset($_POST['klip-note']) && !empty($_FILES['klip-file']["name"])) {
			$_POST['klip-note'] = '[img]'.$original.'[/img]'.$_POST['klip-note'];
		}
		// maybe here we could add any file types, so people can keep here files they need to store somewhere
		// example PDF, TXT, etc.
		if (isset($_POST['klip-note'])) {
			$what = trim($_POST['klip-note']);
			// get it through bbCode!
			$content = birdyComments::BBCode($what,"Article");
		} else {
			$content = stripslashes($klipDB->description);
		}
		$description = $content;
		$descr = $content;
		$image = $user->avatar;
		$original = $user->avatar;
		$title = isset($klipDB->title) ? $klipDB->title : $user->used_name . ' klipped:';
		$author = '';
		if (isset($_POST['klipprivacy'])) $privacy = $_POST['klipprivacy'];
		else {
			$privacy= isset($klipDB->privacy) ? $klipDB->privacy : 0;
			if ($privacy==0) {
				$lastprivacy = $db->loadResult("SELECT privacy FROM klips WHERE user_id=:current_user ORDER BY id DESC LIMIT 1",
					array(":current_user"=>$user->id));
				if (!empty($lastprivacy)) $privacy = $lastprivacy;
			}
		}
			$klip_image_style = $birdy->browser=='web' ? 'style="border-radius:100px;width:150px;"' : 'style="border-radius:50px;width:50px;height:50px;"'; 
			$content = '
			<div class="klip-image" '.$klip_image_style.'>
			<img src="'.$image.'" style="max-width:100%" />
			</div>
			<div style="float:right;width:66%">
				<h2 id="title_text" class="klip-heading">'.$title.'</h2>';
			if ($author) {
				$content.='
				<p class="info" style="font-size:11px;margin-bottom:8px;float:right;">'.$author.'</p>
				<div class="clear"></div>';
			}
			$content.='<p id="description_text">'.$descr.'</p></div>';
		$what = empty($_FILES['klip-file']["name"]) ? 'klip-note' : 'klip-photo';
		if (isset($klipDB->type)) $what = $klipDB->type;
		$URLtoload = BIRDY_URL.'klipper/'.$user->username;
	}
	?>
	<form id="submitform" name="submitform" action="" style="display:<?php echo $formdisplay; ?>" method="POST">
		<span class="">Title:</span>
		<input type="text" class="textbox klip-input" id="klip_title" name="klip_title" value="<?php echo $title; ?>" placeholder="Title" /><br />
		<div class="klip-content klip-input"><?php echo $content; ?></div><br />
		<?php if (!empty($_POST['klip-note']) || !empty($klip_note)) echo "<div style='display:none'>"; ?>
		<span class="">Description:</span>
		<textarea id="klip_description" class="textbox klip-textarea klip-input" style="font-size:13px" name="klip_description" placeholder="Description"><?php echo $description; ?></textarea>
		<div class="divider"></div>
		<?php if (!empty($_POST['klip-note']) || !empty($klip_note)) echo "</div>"; ?>
		<span class="">Tags:</span>
		<input type="text" class="textbox klip-input" name="klip_tags" id="klip-tags" />
		<br />
		<span>Parent:</span>
		<input type="text" class="textbox klip-input" name="parent_tag" id="parent-tag" />
			<?php
			$klip_tags = '[]';
			if (isset($klipDB->tags)) {
				$klip_tags = explode(",",$klipDB->tags);
				for($i=0;$i<count($klip_tags);$i++) {
					$klip_tags[$i] = '"'.ucfirst($klip_tags[$i]).'"';
				}
				$klip_tags = '['.implode(",",$klip_tags).']';
			}
			
			$parent_tag = (isset($klipDB->parent_tag)) ? '['.$klipDB->parent_tag.']' : '[]';
			
			$tags_array = array();
			foreach ($tags as $tag) {
				$tag->tag = stripslashes(str_replace("\\","",$tag->tag));
				if (!empty($tag->tag)) $tags_array[] = '{id:'.$tag->id.',name:"'.ucfirst($tag->tag).'"}';
			}
			$tags_array = '['.implode(",",$tags_array).']';
			?>
		<input type="hidden" name="url" value="<?php echo $URLtoload; ?>" />
		<input type="hidden" name="author" value="<?php echo $author; ?>" />
		<input type="hidden" name="thumbnail" value="<?php echo $image; ?>" />
		<input type="hidden" name="bignail" value="<?php echo $original; ?>" />
		<input type="hidden" name="what" value="<?php echo $what; ?>" />
		<input type="hidden" name="content" value="<?php echo htmlentities($description); ?>" />
		<input type="hidden" name="edit" value="<?php echo $edit; ?>" />
		<input type="hidden" name="reklip" value="<?php echo $reklip; ?>" />
		<p class="para top">
		<span style="position:relative;top:12px;font-weight:bold;">
		Privacy: 
		<?php
		$opacity = ($privacy==0) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="privacy0" onclick="change_privacy(0)" src="'.BIRDY_URL.'images/icons/public.png" style="position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		$opacity = ($privacy==1) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="privacy1" onclick="change_privacy(1)" src="'.BIRDY_URL.'images/icons/friends.png" style="position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		$opacity = ($privacy==2) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="privacy2" onclick="change_privacy(2)" src="'.BIRDY_URL.'images/icons/onlyfriends.png" style="width:16px;position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		$opacity = ($privacy==3) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="privacy3" onclick="change_privacy(3)" src="'.BIRDY_URL.'images/icons/lock.png" style="position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		?>
		<select name="privacy" id="privacy" class="klip-select" style="width: 190px ! important;">
			<?php 
			$selected = ($privacy==0) ? 'selected="selected"' : ''; 
			echo '<option value="0" '.$selected.'>Public</option>';
			$selected = ($privacy==1) ? 'selected="selected"' : ''; 
			echo '<option value="1" '.$selected.'>My followers &amp; friends</option>';
			$selected = ($privacy==2) ? 'selected="selected"' : '';
			echo '<option value="2" '.$selected.'>Only my friends</option>';
			$selected = ($privacy==3) ? 'selected="selected"' : ''; 
			echo '<option value="3" '.$selected.'>Only me</option>';
			?>
		</select>
		</span>
		<input type="hidden" name="klip-done" value="Done" />
		<div class="clear"></div><p>&nbsp;</p>
		<input type="submit" class="klip-submit" style="float:right;margin-right:5px" value="Done" /></p>
		<input type="button" onclick="history.go(-1);" class="klip-submit" style="float:right;margin-right:5px" value="Cancel" /></p>
	</form>
	<?php
	echo "</div>";
	echo "</div>";
	echo "</div>";
	echo '<div class="clear" style="padding:0 0 4% 0"></div>';
	
	$birdy->addScriptToBottom('
	$(document).ready(function() {
		$("#klip_title").keyup(function(){
			document.getElementById("title_text").innerHTML = document.getElementById("klip_title").value;
		});
		$("#klip_description").keyup(function(){
			document.getElementById("description_text").innerHTML = document.getElementById("klip_description").value;
		});
		$("#privacy").change(function(){
			var value = document.getElementById("privacy").value;
			change_privacy(value);
		});
		$("#klip-tags").magicSuggest({
			width: "93%",
			displayField: "name",
			valueField: "name",
			value: '.$klip_tags.',
			data: '.$tags_array.',
			useTabKey: true,
			emptyText: "Add your tags",
			resultAsString: true,
			maxSelection: 8,
			name: "klip_tags"
		});
		$("#parent-tag").magicSuggest({
			width: "93%",
			displayField: "name",
			value: '.$parent_tag.',
			data: '.$tags_array.',
			useTabKey: true,
			emptyText: "Put it under... (Parent Tag)",
			resultAsString: true,
			maxSelection: 1,
			name: "parent_tag"
		});
	} );
	function change_privacy(value) {
		document.getElementById("privacy0").style.opacity = "0.2";
		document.getElementById("privacy1").style.opacity = "0.2";
		document.getElementById("privacy2").style.opacity = "0.2";
		document.getElementById("privacy3").style.opacity = "0.2";
		document.getElementById("privacy"+value).style.opacity = "1";
		document.getElementById("privacy").value = value;
	}
	');
