<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
//============================================================================
$klipDB = $db->loadObject("SELECT id,url,title,description,thumbnail,user_id FROM klips WHERE id=:id",array(":id"=>intval($_REQUEST['klip'])));
$title = stripslashes($klipDB->title);
$description = stripslashes($klipDB->description);
$media = $klipDB->thumbnail;
$klip_user = birdyUser::getInstance($klipDB->user_id);
$klip_url = BIRDY_URL.'klip/'.$klipDB->id.'/'.str_replace(array("/"," "),"_",$title);
$url   = rawurlencode($klip_url);

	    // fix title
	    str_replace('(','-',$title);
	    str_replace(')','-',$title);
	    if (birdyBrowser::get_user_agent()=='ie') str_replace(' ','_',$title);

      // Social Bookmarks
      $socialNUM_COLS = 10;
      $dir = BIRDY_BASE.DS.'images'.DS.'socialbookmarker'.DS;
      $socialimagepath = BIRDY_URL.'images/socialbookmarker/';
      //open a handle to the directory
      $handle = opendir($dir);
      //intitialize our counter
      $socialcount = 0;
      //loop through the directory
      while (false !== ($socialnetwork = readdir($handle))) {
        //evaluate each entry, removing the . & .. entries
        if ($socialnetwork !== '.' && $socialnetwork !== '..' && $socialnetwork !== '' && $socialnetwork !== 'index.html') {
          $socialnetworks[$socialcount] = $socialnetwork;
          $socialcount++;
        }
        //echo $socialnetwork."<br/>";
      }
      //echo count($socialnetworks);
      //echo "<pre>";print_r($socialnetworks);echo "</pre>";
      //return;
      // Social Links
      $social_xml = simplexml_load_file(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS."social_links.xml");
      $link_names = array();
      $links = array();
      foreach($social_xml->children() as $link) {
        $i = 0;
        while ($i<16) {
          if (isset($socialnetworks[$i])) {
			if (strstr($socialnetworks[$i],str_replace('.','',strtolower($link->getName())))) {
				//echo $socialnetworks[$i].' => '.$link->getName().'<br />';
				$link_names[$i] = $link->getName();
				$links[$i] = str_replace('{link}',$url,$link);
				$links[$i] = str_replace('{title}',$title,$links[$i]);
				$links[$i] = str_replace('+','&',$links[$i]);
			}
		  }
          if (strtolower($link->getName())=='google') {
            $google = str_replace('{link}',$url,$link);
            $google = str_replace('{title}',$title,$google);
            $google = str_replace('+','&',$google);
          }
          if (strtolower($link->getName())=='pinterest') {
            $pinterest = str_replace('{link}',$url,$link);
            $pinterest = str_replace('{title}',$title,$pinterest);
            $pinterest = str_replace('{media}',$media,$pinterest);
            $pinterest = str_replace('+','&',$pinterest);
          }
          if (strtolower($link->getName())=='facebook') {
            $facebook = str_replace('{link}',$url,$link);
            $facebook = str_replace('{title}',$title,$facebook);
            $facebook = str_replace('+','&',$facebook);
          }
          if (strtolower($link->getName())=='twitter') {
            $twitter = str_replace('{link}',$url,$link);
            $twitter = str_replace('{title}',$title,$twitter);
            $twitter = str_replace('+','&',$twitter);
          }
          if (strtolower($link->getName())=='digg') {
            $digg = str_replace('{link}',$url,$link);
            $digg = str_replace('{title}',$title,$digg);
            $digg = str_replace('+','&',$digg);
          }
          if (strtolower($link->getName())=='linkedin') {
            $linkedin = str_replace('{link}',$url,$link);
            $linkedin = str_replace('{title}',$title,$linkedin);
            $linkedin = str_replace('{description}',$description,$linkedin);
            $linkedin = str_replace('+','&',$linkedin);
          }
          if (strtolower($link->getName())=='stumbleupon') {
            $stumbleupon = str_replace('{link}',$url,$link);
            $stumbleupon = str_replace('{title}',$title,$stumbleupon);
            $stumbleupon = str_replace('+','&',$stumbleupon);
          }
          if (strtolower($link->getName())=='tumblr') {
            $tumblr = str_replace('{link}',$url,$link);
            $tumblr = str_replace('{title}',$title,$tumblr);
            $tumblr = str_replace('+','&',$tumblr);
          }
          $i++;
        }
      }
      ?>
      <div style="text-align:center;" class="main">
		<p style="width:20%">
                          <h2 class="style" style="font-size:22px;color:#3289c8;background: #e9e9e9;padding:5px;"><?php echo "Share this Klip!"; ?></h2>
                          <div class="big_social">
                          <a class="klip-social" onclick="window.open('<?php echo $facebook; ?>','Facebook_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-facebook.png' /> <!--Facebook-->
                          </a>
                          <a class="klip-social" onclick="window.open('<?php echo $twitter; ?>','Twitter_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-twitter.png' /> <!--Twitter-->
                          </a>
                          <a class="klip-social" onclick="window.open('<?php echo $google; ?>','Google_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-googleplus.png' /> <!--Google+-->
                          </a>
                          <a class="klip-social" onclick="window.open('<?php echo $myspace; ?>','MySpace_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-linkedin.png' /> <!--LinkedIn-->
                          </a>
                          <a class="klip-social" onclick="window.open('<?php echo $pinterest; ?>','Pinterest_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-pinterest.png' /> <!--Pinterest-->
                          </a>
                          <a class="klip-social" onclick="window.open('<?php echo $digg; ?>','Digg_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-digg.png' /> <!--Digg-->
                          </a>
                          <a class="klip-social" onclick="window.open('<?php echo $stumbleupon; ?>','StumbleUpon_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-stumbleupon.png' /> <!--Digg-->
                          </a>
                          <a class="klip-social" onclick="window.open('<?php echo $tumblr; ?>','Tumblr_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/64-tumblr.png' /> <!--Tumblr-->
                          </a>
                          </div>
			  <br style="clear:both" />
                          <div style="text-align:left;">
			    <h2>Embed code:</h2>
                          <input type="text" id="embedcodebox" readonly="" value="<?php
			  echo htmlentities('<iframe src="'.BIRDY_URL.'embed/klip_id/'.$klipDB->id.'" frameborder="0" height="180" width="660"></iframe>
			  <div style="clear: both; height: 3px; width:652px;"></div>
			  <p style="display: block; font-size: 11px; font-family: &quot;OpenSans&quot;,Helvetica,Arial,sans-serif; margin: 0px; padding: 3px 4px; color:rgb(153, 153, 153); width: 652px;">
			      <a href="'.$klip_url.'" target="_blank" style="color:#808080; font-weight:bold;">'.$title.'</a>
			      <span> by </span>
			      <a href="'.BIRDY_URL."klipper/".$klip_user->username.'" target="_blank" style="color:#808080; font-weight:bold;">'.stripslashes($klip_user->used_name).'</a>
			      <span> on </span>
			      <a href="'.BIRDY_URL.'" target="_blank" style="color:#808080; font-weight:bold;"> Klipsam</a>
			  </p>
			  <div style="clear: both; height: 3px; width: 652px;"></div>
			  ');?>" />
			  <small style="font-size:12px;padding:20px;">...or send it with an <a style="color:#3289C8;" href='mailto:?subject=<?php echo $title; ?> | Klipsam&body=Check out "<?php echo $title; ?>" on Klipsam <?php echo $klip_url; ?>'>Email</a></small>
			  </div>
			  <?php
			  echo '<iframe src="'.BIRDY_URL.'embed/klip_id/'.$klipDB->id.'" frameborder="0" height="180" width="660"></iframe>
			  <div style="clear: both; height: 3px; width:652px;"></div>
			  <p style="display: block; font-size: 11px; font-family: &quot;OpenSans&quot;,Helvetica,Arial,sans-serif; margin: 0px; padding: 3px 4px; color:rgb(153, 153, 153); width: 652px;">
			      <a href="'.$klip_url.'" target="_blank" style="color:#808080; font-weight:bold;">'.$title.'</a>
			      <span> by </span>
			      <a href="'.BIRDY_URL."klipper/".$klip_user->username.'" target="_blank" style="color:#808080; font-weight:bold;">'.stripslashes($klip_user->used_name).'</a>
			      <span> on </span>
			      <a href="'.BIRDY_URL.'" target="_blank" style="color:#808080; font-weight:bold;"> Klipsam</a>
			  </p>
			  <div style="clear: both; height: 3px; width: 652px;"></div>
			  ';?>
		</p>
	</div>
<br />
<br />