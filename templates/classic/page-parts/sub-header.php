<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
if ($birdy->current_page!='login') {

               //first get the count of users
                $birdy->total_users = $db->count("birdy_users");
                //then check the first x klips where x=count of users, or max. 1000 klips with tags
                $birdy->total_users = ($birdy->total_users<1000) ? $birdy->total_users : 1000;
                $trending_tags = $db->loadAssoclist("SELECT tags FROM klips WHERE tags<>'' ORDER BY creation_date DESC LIMIT ".$birdy->total_users);
                foreach ($trending_tags as $value) {
                    $trendings = explode(",",$value['tags']);
                    foreach ($trendings as $trend) {
                        $trending[] = $trend;
                    }
                }
                $trending = array_count_values($trending);
                arsort($trending,1);
                ?>
<div class="header_sub_bg">
<div class="wrap">
<div class="wrapper">
		<div class="hdr-nav trending hide">
			<ul class="sub_nav">
                <li class="info hide"><a disabled="disabled">Trending:</a></li>
                <?php
                foreach($trending as $x => $x_value) {
                    $x_value = $db->loadResult("SELECT id FROM tags WHERE tag=:tag",array(":tag"=>$x));
                    echo '<li class="hide"><a href="/?tag='.$x_value.'">'.$x.'</a></li>';
                }
                ?>
                <!--
                <li class="hide"><a href="#">Responsive design</a></li>
                <li class="hide"><a href="#">Best islands in the world</a></li>
                <li class="hide"><a href="#">Image manipulation</a></li>
                <li class="active hide"><a href="#">New Technologies</a></li>
                <li class="hide"><a href="#">Web design and technology</a></li>
                -->
				</ul>
		</div>
		<div class="hdr-nav" style="float:right">
			<ul class="sub_nav">
                <li><a href="/search" rel="lightbox" style="font-size:12px;"><img src="/images/icons/search.png" style="width:26px"/> Search</a></li>
                <?php if ($user->isLoggedIn()) { ?><li><a href="/settings" style="font-size:12px;"><img src="/images/icons/options.png" style="width:26px"/> Settings</a></li><?php } ?>
            </ul>
		</div>
		<!--
		<div class="hdr-nav" style="float: left; width: 47%;">
  			<ul class="sub_nav">
                <li><input type="search" placeholder="Search" class="klip-submit klip-input" style="/*position: relative; top: 2px;*/" /></li>
                <li><a href="/settings" style="font-size:12px;"><img src="/images/icons/options.png" style="width:26px"/> Settings</a></li>
            </ul>
		</div>
		-->
</div>
</div>
</div>
<div class="clear"></div>
<?php 
//echo "<pre>";print_r($_SESSION);echo "</pre>"; 

}
?>