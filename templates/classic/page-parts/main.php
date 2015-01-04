<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();

if (isset($_REQUEST['remove_filter']) && $_REQUEST['remove_filter']=='klippers') unset($_SESSION['klipper_search']);

if (!$user->isLoggedIn() && $birdy->current_page!='login') {
?>
		<div class="content">
		    <h2 class="style list">
		    <img src="images/URLs/default.jpg" alt="Klip your Surf Jam" class="hide index-logo"/>	<!--paper-clip-attachment.jpg-->
		    <a href="/login">Klip your Surf Jam</a></h2>
		</div>
	<div class="clear"></div>
<?php
}
?>

<div class="wrap">
<div class="wrapper">
	<div class="main">
	{(birdy_feedback)}
	<?php if (file_exists(BIRDY_TEMPLATE_BASE.DS."page-parts".DS."main".DS.$birdy->current_page.".php")) include("main".DS.$birdy->current_page.".php"); ?>
	</div>
	<div style="padding:0 0 4% 0;"></div>
</div>
</div>

<?php
$birdy->addScriptToBottom("
function change_favorite(klip_id) {
	$.post('/favorite.php', 
	{'klip': klip_id},
	function(data){
		if (data==0) { //favorite was deleted
			if (document.getElementById('favoriteicon'+klip_id)) {
				document.getElementById('favoriteicon'+klip_id).style.display='none';
			}
			if (document.getElementById('favoritecount'+klip_id)) {
				if (document.getElementById('favoritecount'+klip_id).innerHTML=='(1)') {
					$('#favoritecount'+klip_id).remove();
				} else {
					var favorited = document.getElementById('favoritecount'+klip_id).innerHTML;
					var result = parseInt(favorited.substring(1, yourString.length-1));
					result--;
					document.getElementById('favoritecount'+klip_id).innerHTML = '('+result+')';
					document.getElementById('favoritecount'+klip_id).style.color='#666666';
				}
			}
			document.getElementById('favorite'+klip_id).style.color='#666666';
			document.getElementById('favorite'+klip_id).innerHTML = 'Approve';
		} else { //favorite was added
			if (document.getElementById('favoriteicon'+klip_id)) {
				document.getElementById('favoriteicon'+klip_id).style.display='inline';
			}
			if (document.getElementById('favoritecount'+klip_id)) {
				var favorited = document.getElementById('favoritecount'+klip_id).innerHTML;
				var result = parseInt(favorited.substring(1, yourString.length-1));
				result++;
				document.getElementById('favoritecount'+klip_id).style.color='#FFAC33';
			} else {
				// now make this happen only on web, not mobile.
				// on web favoriteicon exists, but not on mobile.
				// so we check that.
				if (document.getElementById('favoriteicon'+klip_id)) {
					$('#favorite'+klip_id).after(' <span id=\"favoritecount'+klip_id+'\">(1)</span>');
					document.getElementById('favoritecount'+klip_id).style.color='#FFAC33';
				}
			}
			document.getElementById('favorite'+klip_id).style.color='#FFAC33';
			document.getElementById('favorite'+klip_id).innerHTML = 'Approved';
		}
	}
	);
	//return false;
}
function scrollToAnchor(aid){
		$('html,body').animate({scrollTop: $('#'+aid).offset().top},'slow');
}
var optionsDisplay;
function showOptions(klip_id, what) {
	if (what) optionsDisplay = what;
	else if (!optionsDisplay) optionsDisplay = 'inline';
	else {
		if (optionsDisplay=='inline') optionsDisplay = 'none';
		else if (optionsDisplay=='none') optionsDisplay = 'inline';
	}
	document.getElementById('actions-'+klip_id).style.display=optionsDisplay;
	return false;
}
function changeFriend(klipper_id) {
	if (document.getElementById('addFriend'+klipper_id).innerHTML=='+ Add friend') {
		document.getElementById('addFriend'+klipper_id).innerHTML ='Un-friend';
		changeFollow(klipper_id);
	} else {
		document.getElementById('addFriend'+klipper_id).innerHTML ='+ Add friend'; 
	}
}
function changeFollow(klipper_id) {
	if (document.getElementById('addFollow'+klipper_id).innerHTML=='Follow') {
		document.getElementById('addFollow'+klipper_id).innerHTML ='Un-follow';
	} else {
		document.getElementById('addFollow'+klipper_id).innerHTML ='Follow'; 
	}
}
var blockstate = 'unchanged';
function changeBlock(klipper_id,block) {
	//if block changed, we don't need to check the initial text...
	if (blockstate!='unchanged') block='';
	
	if (block=='Block this Klipper' || blockstate=='unblocked') {
		document.getElementById('block'+klipper_id).innerHTML ='Un-block';
		blockstate = 'blocked';
	} else {
		document.getElementById('block'+klipper_id).innerHTML ='Block this Klipper'; 
		blockstate = 'unblocked';
	}
}
");
?>
<!--<iframe style="width:0px;height:0px;" name="iframe" id="iframe"></iframe>-->
