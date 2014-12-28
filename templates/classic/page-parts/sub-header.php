<div class="header_sub_bg">
<div class="wrap">
<div class="wrapper">
		<div class="hdr-nav trending hide">
			<ul class="sub_nav">
                <li class="info hide"><a disabled="disabled">Trending:</a></li>
                <li class="hide"><a href="#">Responsive design</a></li>
                <li class="hide"><a href="#">Best islands in the world</a></li>
                <li class="hide"><a href="#">Image manipulation</a></li>
                <li class="active hide"><a href="#">New Technologies</a></li>
                <li class="hide"><a href="#">Web design and technology</a></li>
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
