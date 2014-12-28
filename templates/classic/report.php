<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Report | Klipsam");
$birdy->pageDescription("Klip everything you find while you surf. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
if (!empty($_POST['submitReport'])) {
	$db->update('klips','reports=reports+1','id=:id',array(":id"=>intval($_POST['klip'])));
	$db->insert('reports',array("description"=>$_POST['report_description']));
	$klip = $db->loadObject("SELECT id,title,user_id FROM klips WHERE id=:id",array(":id"=>intval($_POST['klip'])));
	$klip_user = birdyUser::getInstance($klip->user_id);
	$birdy = birdyCMS::getInstance();
	$klip_url = 'http://www.klipsam.com/klip/'.$klip->id.'/'.str_replace(array("/"," "),"_",stripslashes($klip->title));
	$reporter_url = 'http://www.klipsam.com/klipper/'.$user->username;

        $mail = new birdyMailer;
        // fill mail with data
        $mail->From = "info@klipsam.com";
        $mail->FromName = "Klipsam";
        $mail->AddAddress($klip_user->email);
        $mail->Subject = "Your klip has been reported!";
        $mail->Body = "Your klip <strong>".stripslashes($klip->title)."</strong> has been reported by another user as inappropriate.<br />
        It is now offline and will go under moderation. If found to be in accordance with 
        <a href='http://www.klipsam.com/rules'>The Klipsam Rules</a>,
        it will be back online again soon";
		$mail->Send(true);

        $mail = new birdyMailer;
        // fill mail with data
        $mail->From = "info@klipsam.com";
        $mail->FromName = "Klipsam";
        $mail->AddAddress("chris@klipsam.com");
        $mail->Subject = "A klip has been reported!";
        $mail->Body = "The klip <strong><a href='".$klip_url."'>".stripslashes($klip->title)."</a></strong> has been reported by 
        <a href='".$reporter_url."'>".$klipper->used_name."</a> as inappropriate.<br />
        It is now offline and will wait for your moderation.<br /><br />";
        $mail->Body.= "Reason for reporting:<br />".htmlentities($_POST['report_description']);
		$mail->Send(true);
		
		$birdy->outputNotice("Your report has been submitted");
		$referer = $_SESSION['HTTP_REFERER'];
		unset($_SESSION['HTTP_REFERER']);
		$birdy->loadPage($referer);
		return;
}
$_SESSION['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
?>
<body>
		<form action="/report" method="POST">
		<div style="margin-bottom:25px;">
		<span class="">Reason for this report:</span><br />
		<label class="tooltip">
			<textarea id="report_description" class="textbox klip-textarea" style="height:50px;font-size:12px;width:93%;" name="report_description" placeholder="Enter the reason why you submit this report"></textarea>
			<?php echo $birdy->tooltip('Your report is confidential!'); ?>
		</label>
		</div>
		<span class="info" style="text-align:justify;font-size:11px">
		Please be careful with your reports. If your reports are submitted without valid reasons, your account will be suspended!<br />
		Valid reasons for reporting include among others violent, sexual, offensive, discriminating content. In such cases, we support and reward reports!
		</span>
		<input type="hidden" name="submitReport" value="1" />
		<input type="hidden" name="klip" value="<?php echo intval($_REQUEST['klip']); ?>" />
		<p class="para top">
		<input type="button" onclick="Lightbox.hide()" class="klip-submit" style="text-align:center;float:left;margin-left:5px" name="delete-cancel" value="Cancel" />
		<input type="submit" class="klip-submit" name="klip-submit" value="Report!" />
		</p>
		<div class="clear">&nbsp;</div>
		</form>
</body>
</html>
