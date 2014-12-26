<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
$birdy= birdyCMS::getInstance();
//============================================================================
Class birdyComments {
	
	//syntaxHighlighting
	public static function CodeHighlight($str, $type="Comments") {
		if ($type=="Article") $str = str_replace("<br />","\n",$str);
		$simple_replace = array();
		$syntax = 'syntaxHighlighter'.$type;
		$google = 'googleHighlighter'.$type;
		if (birdyConfig::$$syntax) {
			$str = str_replace(array("[code]","<code>"),"<pre class='brush: php;'>",$str); // normally 'plain' but php has at least some highlighting
			$str = str_replace(array("[/code]","</code>"),"</pre>",$str);
			foreach (birdyConfig::$codesToHighlight as $code) {
				$str = str_replace(array("[$code]","<$code>"),"<pre class='brush: $code;'>",$str);
				$str = str_replace(array("[/$code]","</$code>"),"</pre>",$str);
			}
		} elseif (birdyConfig::$$google) {
			$str = str_replace(array("[code]","<code>"),"<pre class='prettyprint'>",$str);
			$str = str_replace(array("[/code]","</code>"),"</pre>",$str);
			foreach (birdyConfig::$codesToHighlight as $code) {
				$str = str_replace(array("[$code]","<$code>"),"<pre class='prettyprint'>",$str);
				$str = str_replace(array("[/$code]","</$code>"),"</pre>",$str);
			}
		}
		return ($str);
	}

	//Bbcode
	public static function BBCode ($str, $type='Comments') {  
		$str = htmlentities($str, ENT_QUOTES, "UTF-8");
	
		$simple_search = array(  
					//added line break  
					'/\\n/is',  
					'/\[b\](.*?)\[\/b\]/is',  
					'/\[i\](.*?)\[\/i\]/is',  
					'/\[u\](.*?)\[\/u\]/is',  
					'/\[url\=(.*?)\](.*?)\[\/url\]/is',  
					'/\[url\](.*?)\[\/url\]/is',  
					'/\[align\=(left|center|right)\](.*?)\[\/align\]/is',  
					'/\[img\](.*?)\[\/img\]/is',  
					'/\[highlight\](.*?)\[\/highlight\]/is',  
					'/\[mail\=(.*?)\](.*?)\[\/mail\]/is',  
					'/\[mail\](.*?)\[\/mail\]/is',  
					'/\[font\=(.*?)\](.*?)\[\/font\]/is',  
					'/\[size\=(.*?)\](.*?)\[\/size\]/is',  
					'/\[color\=(.*?)\](.*?)\[\/color\]/is',  
					//added textarea for code presentation  
					'/\[codearea\](.*?)\[\/codearea\]/is',             
					//added paragraph  
					'/\[p\](.*?)\[\/p\]/is',
				//smilies
				'/\:angry:/is',
					'/\:angel:/is',
					'/\:arrow:/is',
					'/\:at:/is',
					'/\:biggrin:/is',
						'/\:blank:/is',
						'/\:blush:/is',
						'/\:confused:/is',
						'/\:cool:/is',
							'/\:dodgy:/is',
							'/\:exclamation:/is',
							'/\:heart:/is',
							'/\:huh:/is',
								'/\:lightbulb:/is',
								'/\:my:/is',
								'/\:rolleyes:/is',
								'/\:sad:/is',
									'/\:shy:/is',
									'/\:sleepy:/is',
									'/\:smile:/is',
									'/\:tongue:/is',
										'/\:undecided:/is',
										'/\:wink:/is',
			);

		$simple_replace = array(  
					//added line break  
					'<br />',  
					'<strong>$1</strong>',  
					'<em>$1</em>',  
					'<u>$1</u>',  
					// added nofollow to prevent spam  
					'<a href="$1" rel="nofollow" title="$2 - $1">$2</a>',  
					'<a href="$1" rel="nofollow" title="$1">$1</a>',  
					'<div style="text-align: $1;">$2</div>',  
					//added alt attribute for validation  
					'<img src="$1" alt="" />',  
					'<span class="highlight">$1</span>',  
					'<a href="mailto:$1">$2</a>',  
					'<a href="mailto:$1">$1</a>',  
					'<span style="font-family: $1;">$2</span>',  
					'<span style="font-size: $1;">$2</span>',  
					'<span style="color: $1;">$2</span>',  
					//added textarea for code presentation  
					'<textarea class="code_container" rows="20" cols="65">$1</textarea>',  
					//added paragraph  
					'<p>$1</p>',  
					//smilies
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/angry.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/angel.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/arrow.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/at.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/biggrin.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/blank.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/blush.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/confused.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/cool.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/dodgy.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/exclamation.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/heart.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/huh.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/lightbulb.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/my.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/rolleyes.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/sad.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/shy.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/sleepy.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/smile.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/tongue.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/undecided.gif" border="0" alt="" />',
					'<img src="'.BIRDY_URL.'birdy/helpers/comments/smilies/wink.gif" border="0" alt="" />',
					);
		
		// Do simple BBCode's  
		$str = preg_replace ($simple_search, $simple_replace, $str);   
		return self::CodeHighlight(stripslashes($str), $type);
	}  

	//Check Email
	private function check_email_address($email) {
		// First, we check that there's one @ symbol, and that the lengths are right
		if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
			// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
				return false;
			}
		}    
		if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
					return false; // Not enough parts to domain
			}
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
					return false;
				}
			}
		}
		return true;
	}

	public static function getComments( $tutid, $comment_id=0 ) {
		// first check for newly submitted comment, only on first call!
		if (empty($comment_id)) {
			$submission = self::submitComment($tutid);
			if ($submission) {
				$birdy = birdyCMS::getInstance();
				if ($submission['error']) 
					$birdy->outputNotice( /*'<div id="comment_messageBox"><div class="box alert-box">'.*/$submission['error']/*.'</div></div>'*/ );
				else
					$birdy->outputNotice( /*'<div id="comment_messageBox"><div class="box success-box">'.*/$submission['response']/*.'</div></div>'*/ );
			}
		}
		$values = array(':tutid'=>$tutid, ':comment_id'=>$comment_id, ':status'=>1);
		$db = birdyDB::getInstance();
		//fetch all comments from database where the tutorial number is the one you are asking for
		$comments = $db->loadObjectList("SELECT * FROM birdy_comments WHERE article_id=:tutid AND parent_id=:comment_id AND status=:status ORDER BY id",$values);
		
		$i = 0;
		if ($comments) {
			foreach ($comments as $comment) {
				$comments[$i]->text = self::BBCode($comment->text);
				//avatar
				$gravatar_email = md5( strtolower( trim( $comment->user_email ) ) );
				$default = "http://1.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=75";
				$size = 42;
				if ($comment->user_id) {
					$user = birdyUser::getInstance($comment->user_id);
					$comments[$i]->gravatar = str_replace("/avatars/","/avatars/thumbnails/",$user->avatar);
				} else
					$comments[$i]->gravatar = "http://www.gravatar.com/avatar/" . $gravatar_email . "?d=" . urlencode( $default );// . "&s=" . $size;
				$i++;

			}
		}
		return $comments;
	}
	
	public static function displayComments($comments) {
		$output = '';
		$i = 1;
		foreach ($comments as $comment) {
			$output.= self::singleComment($comment,$i);
			$i++;
		}
		return $output;
	}
	
	private function singleComment($comment, $i) {
		// depth defines replies. it should be used in connection to a css class, eg. depth-1, depth-2 etc. for each nested comment
		static $depth=1;
		
		if (file_exists(BIRDY_TEMPLATE_BASE.DS.'cards'.DS.'comment.card'))
			$template = file_get_contents(BIRDY_TEMPLATE_BASE.DS.'cards'.DS.'comment.card');
		else
			$template = file_get_contents(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'comments'.DS.'comment.card');
		
		// reply box..
		$reply_box = strstr($template,'[reply_box]');
		$reply_box = explode('[/reply_box]',$reply_box);
		//
		$element = strstr($reply_box[0],'element="');
		$element = explode('"',str_replace('element="','',$element)); 
		$element = $element[0];
		//
		$class = strstr($reply_box[0],'class="');
		$class = explode('"',str_replace('class="','',$class)); 
		$class = $class[0];
		//
		$template = str_replace($reply_box[0].'[/reply_box]',"",$template);
		$reply_box = '<'.$element.' class="'.$class.'" style="display:none;" id="li-reply-'.$comment->id.'"></'.$element.'>';
		$template = str_replace( '[replies]', '[replies]'.$reply_box, $template );
		
		$reply_icon = '';
		$user_name_linked = (empty($comment->user_url)) ? $comment->user_name : '<a href="'.$comment->user_url.'" rel="external nofollow" class="info url">'.$comment->user_name.'</a>';

		$template = str_replace( '[article_id]', $comment->article_id, $template );
		$template = str_replace( '[comment_id]', $comment->id, $template );
		$template = str_replace( '[gravatar]', $comment->gravatar, $template );
		$template = str_replace( '[user_name_linked]', $user_name_linked, $template );
		$template = str_replace( '[date]', date('F d, Y \a\t g:i a'), $template );
		$template = str_replace( '[comment_number]', $i, $template );
		$template = str_replace( '[comment_text]', $comment->text, $template );
		$template = str_replace( '[user_name]', $comment->user_name, $template );
		$template = str_replace( '[depth]', $depth, $template );
		$template = str_replace( '[reply_link]', "javascript:reply".$comment->article_id."('".$element."', ".$comment->id.", '".$comment->user_name."')", $template );
		
		if ($depth>1) {
			$reply_icon = '<img alt="This is a reply" title="This is a reply" src="'.BIRDY_URL.'images/icons/reply.png" class="reply_icon" />';
		}
		$template = str_replace( '[reply_icon]', $reply_icon, $template );
		
		$comments = self::getComments($comment->article_id,$comment->id);
		if (count($comments>0)) {
			$original_depth = $depth;
			$depth = $original_depth + 1;
			if ($depth>6) $depth=6;
			$template = str_replace( '[replies]', self::displayComments($comments), $template );
			$depth = $original_depth;
		} else {
			$template = str_replace( '[replies]', '', $template );
		}
		
		return $template;
	}
	
	public static function commentForm($commentform, $form_id='') {
		$birdy = birdyCMS::getInstance();
		// wrap our form
		$commentform = '<div id="birdy_respond"><div id="commentform_wrapper'.$form_id.'"><form style="margin-top:0;" method="POST" id="commentform'.$form_id.'">'.$commentform.'</form></div></div>';
		// now process the commentform
		$addagainstSpam = '<p style="display: none;"><input type="email" name="confirm_email" value="" /><input type="password" name="password" value="" /><input type="password" name="confirm_password" value="" /></p>';
		$commentform = str_replace('</form>',$addagainstSpam.'</form>',$commentform);
		// and add our hidden input to be validated by javascript
		$commentform = str_replace('</form>','<input type="hidden" name="birdyCommentSession" value="Birdy'.session_id().'" /></form>',$commentform);
		// ADD ANTISPAM AFTER EVERY COMMENT FORM!!
		if (birdyConfig::$antispam_method=='sblam') {
			$commentForm.= '<script src="'.BIRDY_URL.'birdy/helpers/antispam/sblam.js.php" type="text/javascript"></script>';
		}
		// ADD AJAX
		$canonical = substr(BIRDY_URL,0,-1).BIRDY_SEF_URI; // fix the url because too many // redirects infinitely
		$birdy->addScriptToBottom('
		$(function(){
			$("#commentform'.$form_id.'").submit(function(e){    
				e.preventDefault();  
				$.post("'.$canonical.'", $("#commentform'.$form_id.'").serialize(),
				function(data){
					var source = $("<div>" + data + "</div>");
					$("#comments'.$form_id.'").html(source.find("#comments'.$form_id.'").html());
					// remove the message box from the resulted #comments div...
					//$("#comment_messageBox").html("");
					// and add it before the form
					$("#commentform'.$form_id.'").html("<div class=\'clear\'></div><div id=\'comment_messageBox\'><div class=\'box success-box\'>Your comment has been submitted. Thanks!</div></div><div style=\'width:100%;text-align:center;font-size:18px;\'><p style=\'width:100%;\'>To prevent spam, you will have to reload the page to add another comment...</p><input class=\'reloadButton klip-submit\' type=\'button\' value=\'Reload\' onclick=\'window.location.reload()\' />");
					//$("#commentform'.$form_id.'").prepend(source.find("#comment_messageBox").html());
				},"html");
			});
		});
			function reply'.$form_id.'(element, comment_id, user_name) {
				document.getElementById("comment_parent").value = comment_id;
				document.getElementById("comment'.$form_id.'").innerHTML = "@"+user_name+" ";
				document.getElementById("li-reply-"+comment_id).style.display = "block";
				var commentform = document.getElementById("commentform_wrapper'.$form_id.'").outerHTML;
				document.getElementById("commentform_wrapper'.$form_id.'").outerHTML = "";
				document.getElementById("li-reply-"+comment_id).innerHTML = commentform;
				var aTag = $("#comment-"+comment_id);
				$("html,body").animate({scrollTop: aTag.offset().top},"slow");
			}');
			/*
			function post_a_new_comment() {
				document.getElementById("comment_parent").value = "";
				document.getElementById("comment").innerHTML = "";
				var commentform = document.getElementById("commentform_wrapper'.$form_id.'").outerHTML;
				document.getElementById("commentform_wrapper'.$form_id.'").outerHTML = "";
				document.getElementById("birdy_respond").innerHTML = commentform;
				var aTag = $("div[id=\'birdy_respond\']");
				$("html,body").animate({scrollTop: aTag.offset().top},"slow");
			}
		');*/
		return $commentform;
	}

	function submitComment($article_id){
		if (isset($_POST['article_id']) && $_POST['article_id']==$article_id) { // to prevent comment resubmission on all article display in article list
			// initial antispam... check if hidden fields are entered. If yes, then this is spam.
			if ((isset($_POST['confirm_email'])&&!empty($_POST['confirm_email']))
			|| (isset($_POST['password'])&&!empty($_POST['password']))
			|| (isset($_POST['confirm_password'])&&!empty($_POST['confirm_password']))) return;
			// now continue with the rest of the code
			$db = birdyDB::getInstance();
			if (isset($_REQUEST['birdyCommentSession']) && $_REQUEST['birdyCommentSession']=='Birdy'.session_id()) {
				if (birdyConfig::$allow_comments) {
					$qazi_id = $_POST['article_id'];
					$user_id = $_POST['user_id'];
					$name = $_POST['author'];
					$url = $_POST['url'];
					$email = $_POST['email'];
					$description = $_POST['text'];
					$date = date("Y-m-d H:i:s");
					$parent = $_POST['comment_parent'];
					$status = 1;
					// first check antispam...
					if (birdyConfig::$antispam_method=='sblam') {
						include_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'antispam'.DS."sblamtest.php");
						$antispam = sblamtestpost();
						switch ($antispam) {
							case 0: // signals an error. Post has not been checked.
								return array( 
									'response'	=> '' , 
									'error'		=> 'Comment could not be checked for spam. Please try again in a few minutes...'
								);
								break;
							case 2: // signals certain spam, which can be deleted without hesitation.
								$db->insertUpdate("jos_banned_ips", array( 'ip' => $_SERVER['REMOTE_ADDR'] ));
								return array( 
									'response'	=> '' , 
									'error'		=> 'Your comment has been flagged as spam. Your IP has been banned. If you feel this is wrong, please contact me so I can review it.'
								);
								break;
							case 1: // signals probable spam (which you might want to queue for moderation).
								$status = 0;
								break;
							//case -1: // signals probable non-spam (as above)
							//case -2: // signals certain non-spam, which can be safely published.
						}
					}
					elseif (birdyConfig::$antispam_method=='akismet') {
						include_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'antispam'.DS."akismet.class.php");
						// Load array with comment data.
						$canonical = substr(BIRDY_URL,0,-1).BIRDY_SEF_URI; // fix the url because too many // redirects infinitely
						$comment = array(
							'author' => $name,
							'email' => $email,
							'website' => $url,
							'body' => $description,
							'permalink' => $canonical,
							'user_ip' => $_SERVER['REMOTE_ADDR'], // Optional, if not in array defaults to $_SERVER['REMOTE_ADDR'].
							'user_agent' => $_SERVER['HTTP_USER_AGENT'] // Optional, if not in array defaults to $_SERVER['HTTP_USER_AGENT'].
						);
						// Instantiate an instance of the class.
						$akismet = new Akismet(BIRDY_URL, birdyConfig::$akismetAPIkey, $comment);
						// Test for errors.
						if($akismet->errorsExist()) { // Returns true if any errors exist.
							$akismet->submitSpam();
								if($akismet->isError('AKISMET_INVALID_KEY')) {
									return array( 
										'response'	=> '' , 
										'error'		=> 'The Akismet API key is invalid. Please inform the webmaster.'
									);
								} elseif($akismet->isError('AKISMET_RESPONSE_FAILED')) {
									return array( 
										'response'	=> '' , 
										'error'		=> 'Comment could not be checked for spam. Please try again in a few minutes...'
									);
								} elseif($akismet->isError('AKISMET_SERVER_NOT_FOUND')) {
									return array( 
										'response'	=> '' , 
										'error'		=> 'Akismet server not found! Please inform the webmaster.'
									);
								}
						} else {
							$akismet->submitHam();
								// No errors, check for spam.
								if ($akismet->isSpam()) { // Returns true if Akismet thinks the comment is spam.
									$status = 0;
								}
						}
					}
					//Check form
					if ($name == '' || $email == '' || $description == ''){
						return array( 
							'response'	=> '' , 
							'error'		=> 'Please fill in all fields before submitting the comment.'
						);
					}
					//Check email address
					if (!self::check_email_address($email)) {
						return array( 
							'response'	=> '' , 
							'error'		=> $email . ' is not a valid email address.'
						);
					} 
					//Submit data
					$db->insert('birdy_comments', array(
						'article_id' 		=> intval($qazi_id),
						'text'				=> $description,
						'date'				=> $date,
						'user_id'			=> $user_id,
						'user_email'		=> $email,
						'user_url'			=> $url,
						'user_name'			=> $name,
						'parent_id'			=> $parent,
						'client_ip'			=> $_SERVER['REMOTE_ADDR'],
						'status'			=> $status,
						'send_notification' => 1,
						'normalized_text'	=> strip_tags($description)
					));
					// (`blog_id`) VALUES ('1')";
					if ($status==0) {
						$subject = 'A comment has been flagged as spam, and needs to go under moderation before being published.';
						$response= '';
						$error   = 'Your comment has been flagged as spam, and will go under moderation before being published.';
					} else {
						$subject = 'A new comment has been submitted!';
						$response= 'Your comment has been submitted. Thanks!';
						$error   = '';
					}
					$body = "<pre>".var_export($_POST,true)."</pre>";
					if ($parent) {
						$comment_receiver = $db->loadResult("SELECT user_id FROM birdy_comments WHERE id=:parent",array(':parent'=>$parent));				
					} else {
						$comment_receiver = $db->loadResult("SELECT user_id FROM klips WHERE id=:article_id",array(':article_id'=>intval($qazi_id)));
					}
					if ($comment_receiver) {
						$birdy= birdyCMS::getInstance();
						$birdy->sendMail('Comment Alert', birdyConfig::$site_email, $comment_receiver, $subject, $body);
					}
					return array( 
						'response'	=> $response , 
						'error'		=> $error
					);
				} else {
					return array( 'response'=>'' , 'error'=>'Comments are not allowed!' );
				} 
			}
		}
		return false;
	}

}