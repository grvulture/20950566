<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
//============================================================================
$klipDB = $db->loadObject("SELECT id,url,title,thumbnail FROM klips WHERE id=:id",array(":id"=>intval($_REQUEST['klip'])));
$url   = rawurlencode(BIRDY_URL.'klip/'.$klipDB->id.'/'.str_replace(array("/"," "),"_",$klipDB->title));
$title = stripslashes($klipDB->title);
$media = $klipDB->thumbnail;

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
          if (strtolower($link->getName())=='myspace') {
            $myspace = str_replace('{link}',$url,$link);
            $myspace = str_replace('{title}',$title,$myspace);
            $myspace = str_replace('+','&',$myspace);
          }
          $i++;
        }
      }
      ?>
      <div style="text-align:center;" class="main">
		<p style="width:20%">
                          <h2 class="top1 style" style="font-size:18px;"><?php echo "Share this Klip!"; ?></h2>
                          <div class="big_social">
                          <a class="klip-submit" style="float:left;margin: 5px;width: 29%" onclick="window.open('<?php echo $facebook; ?>','Facebook_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/facebook.png' /> Facebook
                          </a>
                          <a class="klip-submit" style="float:left;margin: 5px;width: 29%" onclick="window.open('<?php echo $twitter; ?>','Twitter_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/twitter.png' /> Twitter
                          </a>
                          <a class="klip-submit" style="float:left;margin: 5px;width: 29%" onclick="window.open('<?php echo $google; ?>','Google_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/google.png' /> Google+
                          </a>
                          <a class="klip-submit" style="float:left;margin: 5px;width: 29%" onclick="window.open('<?php echo $myspace; ?>','MySpace_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/myspace.png' /> MySpace
                          </a>
                          <a class="klip-submit" style="float:left;margin: 5px;width: 29%" onclick="window.open('<?php echo $pinterest; ?>','Pinterest_Share','width=640,height=480,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,copyhistory=no,resizable=no');">
                            <img src='<?php echo BIRDY_URL; ?>images/socialBIG/pinterest.png' /> Pinterest
                          </a>
                          </div>
                          <div class="small_social">
                          <?php                        
                          $sociali=0;
                          while ($sociali<$socialcount) {
                            if (!empty($socialnetworks[$sociali])) {
                              $socialtitle = $link_names[$sociali];
                              $socialimage=$socialnetworks[$sociali];
                              $sociallink = $links[$sociali]; 
                              echo "<a class='klip-submit' style='float:left;margin: 5px;width: 29%' href=\"$sociallink\" target=\"_blank\"><img src=\"".$socialimagepath.$socialimage."\" title=\"$socialtitle\" > ";
                              echo $socialtitle;
                              echo "</a>";
                              $sociali++;
                            } else $socialcount--;
                          }?>
		</p>
	</div>
