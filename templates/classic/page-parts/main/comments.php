<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
                            if (birdyConfig::$allow_comments) {
                                
                                echo '<div id="comments'.$klip->id.'" style="display:'.$comments_style_div.'">';
                                // here we will add the foreach comments
								if ($comment_count>0) {
									echo birdyComments::displayComments($comments);
								}
								// the html of the form. DO NOT add the <form> tags!!
								$commentform  = "<div style='float:right;width:90%'>";
								// and our comment form
                                if (!$user->id) {
									$commentform.='
										<p style="line-height:10px;"><label for="author" class="info"><br />Name</label> <input class="klip-input" id="author" name="author" type="text" value="" size="30" aria-required="true" /></p>
										<p style="line-height:10px;"><label for="email" class="info"><br />Email</label> <input class="klip-input" id="email" name="email" type="email" value="" size="30" aria-required="true" /></p>
										<p style="line-height:10px;"><label for="url" class="info"><br />Website</label> <input class="klip-input" style="width:87% !important;" id="url" name="url" type="url" value="" size="30" /></p>
										<p style="line-height:10px;">&nbsp;</p>
										<input id="user_id" name="user_id" type="hidden" value="0" />
									';
								} else {
									$commentform.='
										<input id="author" name="author" type="hidden" value="'.$user->used_name.'" />
										<input id="email" name="email" type="hidden" value="'.$user->email.'" />
										<input id="url" name="url" type="hidden" value="'.birdyConfig::$profile_url.'/'.urlencode($user->username).'" />
										<input id="user_id" name="user_id" type="hidden" value="'.$user->id.'" />
									';
								}
									$commentform.='
										<p class="comment-form-comment"><label for="comment" class="info">Your comment</label><br /><textarea class="klip-textarea" id="comment'.$klip->id.'" name="text" cols="45" rows="1"></textarea></p>
										<div class="clear"></div>
										<p class="form-submit">
											<input class="klip-submit" style="font-size:12px;" name="comment_submit" type="submit" id="submit" value="Post Comment" />
											<input type="hidden" name="article_id" value="'.$klip->id.'" id="comment_post_ID" />
											<input type="hidden" name="comment_parent" id="comment_parent" value="0" />
										</p>
								</div>';
								// WHAT WILL HAPPEN HERE IS: THE COMMENT GETS SUBMITTED IN AN IFRAME. THEN AJAX LOADS THE PAGE AGAIN.
								// THE DIV #BIRDYCOMMENTS OR THE WHOLE BODY IS REPLACED BY THE AJAX ONE!
								echo "<div class='clear comment-clearer'></div>";
								echo $birdy->commentForm($commentform, $klip->id);
								echo "</div>";
                            } else {
								?>
                                <h3 id="reply-title">Comments are <span>not allowed!</span>
                                <?php
                            }
$birdy->addScriptToBottom("
$(document).ready(function() {
	$('#comment_show".$klip->id."').click(function(){
		style = document.getElementById('comments".$klip->id."').style.display;
		if (style=='none') {
			$('#comments".$klip->id."').slideDown('slow');
			$('#comment".$klip->id."').focus();
			//scrollToAnchor('comments".$klip->id."');
			//document.getElementById('comments".$klip->id."').style.display = 'block';
		} else {
			$('#comments".$klip->id."').slideUp('slow');
			//document.getElementById('comments".$klip->id."').style.display = 'none';
		}
		return false;
	});
});
");
