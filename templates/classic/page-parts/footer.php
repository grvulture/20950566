<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
if ($birdy->current_page!='login' && $birdy->current_page!='contact') {
?>
<div class="footer-float hide">
<?php
}
?>
<div class="wrap">
	<div class="footer">
		<div class="foot_nav">
				 <ul>
				    <li><a class="footer-to-top" href="#top"></a></li>
				    <li><a href="/terms">terms</a></li>
				    <?php //echo (birdyUser::isLoggedIn()) ? '<li><a href="/logout">sign out</a></li>' : '<li><a href="/login">sign in</a></li>'; ?>
				    <li><a href="/contact">Contact</a></li>
				    <li><a href="/getinvolved">Get Involved</a></li>
				    <div class="clear"></div>
				 </ul>
		</div>
		<div class="foot_soc">
			<a title="Facebook" class="socials facebook" target="_blank" href="https://www.facebook.com/klipsam">facebook</a>
			<!--<a title="Rss" class="socials rss" target="_blank" href="http://feeds.feedburner.com/chrismichaelideseu">rss</a>-->
			<!--<a title="Twitter" class="socials twitter" target="_blank" href="https://twitter.com/chris_michaeli">twitter</a>-->
			<a title="Linkedin" class="socials linkedin" target="_blank" href="http://www.linkedin.com/company/5100899">linkedin</a>
			<a title="Mail" class="socials mail" href="mailto:info@klipsam.com">mail</a>
		</div>
		<div class="copy">
			<p class="w3-link"><a href="/"><img class="footer-logo" src="/images/logo-cropped-small.jpg" /></a> Copyright © <?php echo date("Y"); ?> Klipsam. All Rights Reserved.</p>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
if ($birdy->current_page!='login' && $birdy->current_page!='contact') {
?>
</div>
<?php
}
?>
<script type="text/javascript">
$("a[href='#top']").click(function() {
  $("html, body").animate({ scrollTop: 0 }, "slow");
  return false;
});
$("img.lazy").lazyload({
    //effect : "fadeIn",
    threshold : 200,
    skip_invisible : false
});
</script>
