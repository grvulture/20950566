<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//=============================================================================
include_once("user_profile.php");

if (!$user->isLoggedIn()) {
?>
		<div class="content">
		    <h2 class="style list">
		    <img src="images/URLs/default.jpg" alt="Klip your Surf Jam" class="hide index-logo"/>	<!--paper-clip-attachment.jpg-->
		    <a href="#">Klip your Surf Jam</a></h2>
		</div>
	<div class="clear"></div>
<?php
}
?>
<div class="span3">
	<div class="span1_of_3 profile-left">
<?php
if (!$user->isLoggedIn()) {
	//$klips = $db->loadObjectlist("SELECT * FROM klips WHERE privacy=:privacy ORDER BY id DESC",array(":privacy"=>0));
	// @TODO: fix privacy of klips!
?>
			<p class="para top1" style="font-size:11px">In Kipsam you can klip everything you like while you surf. Images, videos, your favorite articles, or even your personal notes.</p>
			<p class="para top" style="font-size:11px">You can also upload your own pictures, <!--MP3s and videos, -->and personalize them with titles and descriptions.</p>
			<p class="para top" style="font-size:11px">Keep it organized in nested tags (tags, sub-tags, etc.), so you won't get lost in your own jam.</p>
			<p>&nbsp;</p>
			<p>Start by signing up now! It's totally FREE and takes only a couple of seconds!</p>
			<h3 class="style list">
				<!--Klip the jam you love in Klipsam!<br />-->
				<a href="<?php echo BIRDY_URL.'login?popup=1'; ?>" rel="lightbox" id="loginPopup" class="btn" style="width: 156px; font-size: 37px;">
					Sign In<br />
					<small style="font-size: 14px; position: relative; left: 86px; top: -50px;">or sign-up</small>
				</a>
				<a href="<?php echo BIRDY_URL.'login'; ?>" id="loginRedirect" class="btn" style="width: 156px; font-size: 37px;">
					Sign In<br />
					<small style="font-size: 14px; position: relative; left: 86px; top: -50px;">or sign-up</small>
				</a>
			</h3>
		<?php
} else {
	?>
		<a href="/profile"><img src="<?php echo $user->avatar; ?>" style="border:2px solid #DDDDDD;border-radius:150px;width:80px;float:left;" alt="<?php echo $user_credentials; ?>"></a>
		<h4 class="style top1" style="word-wrap: break-word;"><a href="/profile"><?php echo $user_credentials; ?></a></h4>
		<div style="text-align:center;padding:2px;">
		<button onclick="window.location='/friend_requests'" class="klip-submit klip-button"><?php echo $db->loadResult("SELECT count(id) FROM friends WHERE requested=:user_id AND accepted=0",array(":user_id"=>$user->id)); ?> friend requests</button>
		<button class="klip-submit klip-button"><?php echo birdyMailer::getUnreadMessages($user->id); ?> new messages</button>
		<button class="klip-submit klip-button"><?php echo $db->loadResult("SELECT count(id) FROM notifications WHERE user_id=:user_id",array(":user_id"=>$user->id)); ?> notifications</button>
		</div>
<?php
}
?>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<?php 
		$klips_sql = klips_sql();
	// @TODO: fix privacy of klips!
		$klips = $db->loadObjectlist("SELECT * FROM klips WHERE ".$klips_sql[0]." ORDER BY id DESC",$klips_sql[1]);
		echo left_column('index',$klips); 
		?>
	</div>
	<div id="result" class="span2_of_3 profile-right">
	<?php
		$actual_row_count = 10;
		if (isset($_REQUEST['remove_filter']) && $_REQUEST['remove_filter']=='klippers') unset($_SESSION['klipper_search']);
		if (isset($_SESSION['klipper_search'])) {
			echo "<h2 class='style' style='font-size:8px'><a>Search results: ".$_SESSION['klipper_search']['query']." (klippers)</a>
				<a class='klip-submit klip-button remove-filter' style='float:none;display:block !important;' href='?remove_filter=klippers'>remove this search</a></h2>
				</h2>";
			$total = count($_SESSION['klipper_search']);
			for ($i=0; $i<$actual_row_count; $i++) {
				$klipper = $_SESSION['klipper_search'][$i];
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klipper-block.php');
				echo "<div class='clear'></div>";
			}
		} elseif ($klips) {
			$total = count($klips);
			for ($i=0; $i<$actual_row_count; $i++) {
				$klip = $klips[$i];
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block.php');
				echo "<div class='clear'></div>";
			}
		}
		$birdy->addScriptToBottom('
            var page = 1;


            $(window).scroll(function () {
                if($(window).scrollTop() + $(window).height() > $(document).height() - 200) {
                    page++;
                    var actual_count = "'.$actual_row_count.'";
                    var total = "'.$total.'";
                    var ajax_session = "'.json_encode($_SESSION).'";
                    var data = {
                        page_num: page,
                        actual_count: actual_count,
                        ajax_session: ajax_session
                    };

                    if((page-1)* actual_count > total){
                        //we have reached the end of page
                    }else{
                        $.ajax({
                            type: "POST",
                            url: "'.BIRDY_URL.'/index.php",
                            data:data,
                            success: function(res) {
                                $("#result").append(res);
                            }
                        });
                    }

                }


            });
		')
	?>
	</div>
<div class="clear"></div>
