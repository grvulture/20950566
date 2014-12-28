<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
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
	} else {
		document.getElementById('addFriend'+klipper_id).innerHTML ='+ Add friend'; 
	}
}
function changeFollow(klipper_id) {
	if (document.getElementById('addFollow'+klipper_id).innerHTML=='Follow') {
		document.getElementById('addFollow'+klipper_id).innerHTML ='Un-follow';
	} else {
		document.getElementById('addFriend'+klipper_id).innerHTML ='Follow'; 
	}
}
function changeBlock(klipper_id) {
	if (document.getElementById('block'+klipper_id).innerHTML=='Block this Klipper') {
		document.getElementById('block'+klipper_id).innerHTML ='Un-block';
	} else {
		document.getElementById('block'+klipper_id).innerHTML ='Block this Klipper'; 
	}
}
");
?>
<!--<iframe style="width:0px;height:0px;" name="iframe" id="iframe"></iframe>-->
