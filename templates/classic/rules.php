<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Rules | Klipsam");
$birdy->pageDescription("Klip everything you find while you surf. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
?>
<body>
<!-- start header -->
<?php include "page-parts".DS."header.php"; ?>
<!-- start sub-header -->
<?php include "page-parts".DS."sub-header.php"; ?>
<!-- start main -->
<div class="wrap">
<div class="wrapper">
	<div class="main">
	{(birdy_feedback)}

<div class="legal">
<h1 class="page-title">The Klipsam Rules</h1>
		<div class="clear"></div>
		<div class="ui divider">
		</div>
<p><span style="font-size: 13px; line-height: 18px;">Our goal is to  provide a service that allows you to discover and receive content from  sources that interest you as well as to share your content with others.  We respect the ownership of the content that users share and each user  is responsible for the content he or she provides. Because of these  principles, we do not actively monitor and will not censor user content,  except in limited circumstances described below.</span></p>
	<br />
	<br />
<h3>Content Boundaries and Use of Klipsam</h3>
		<div class="clear"></div>
		<div class="ui divider">
		</div>
<p style="margin-bottom: 10px;">In order to provide the Klipsam service and the ability to  communicate and stay connected with others, there are some limitations  on the type of content that can be published with Klipsam. These  limitations comply with legal requirements and make Klipsam a better  experience for all. We may need to change these rules from time to time  and reserve the right to do so. Please check back here to see the  latest.</p>
<ul>
<li> <strong><a>Impersonation</a></strong>:  You may not impersonate others through the Klipsam service in a manner  that does or is intended to mislead, confuse, or deceive others.</li>
<li> <strong><a>Trademark</a></strong>:  We reserve the right to reclaim usernames on behalf of businesses or  individuals that hold legal claim or trademark on those usernames.  Accounts using business names and/or logos to mislead others may be  permanently suspended.</li>
<li> <strong><a>Private information</a></strong>:  You may not publish or post other people's private and confidential  information, such as credit card numbers, street address or Social  Security/National Identity numbers, without their express authorization  and permission.</li>
<li> <strong><a>Violence and Threats</a></strong>: You may not publish or post direct, specific threats of violence against others.</li>
<li> <strong><a>Copyright</a></strong>:  We will respond to clear and complete notices of alleged copyright  infringement. Our copyright procedures are set forth in the Terms of  Service.</li>
<li> <strong><a>Unlawful Use</a></strong>: You may not use our service for any  unlawful purposes or in furtherance of illegal activities. International  users agree to comply with all local laws regarding online conduct and  acceptable content.</li>
<li> <strong><a>Misuse of Klipsam Badges</a></strong>: You may not use badges, such  as but not limited to the Promoted or Verified Klipsam badge, unless  provided by Klipsam. Accounts using these badges as part of profile  photos, header photos, background images, or in a way that falsely  implies affiliation with Klipsam may be suspended.</li>
</ul>
	<br />
	<br />
<h3>Abuse and Spam</h3>
		<div class="clear"></div>
		<div class="ui divider">
		</div>
<p style="margin-bottom: 10px;">Klipsam strives to protect its users from abuse and spam. User abuse  and technical abuse are not tolerated on Klipsam.com, and may result in  permanent suspension. Any accounts engaging in the activities specified  below may be subject to permanent suspension.</p>
<ul>
<li> <strong><a>Serial Accounts</a></strong>: You may not create multiple accounts  for disruptive or abusive purposes, or with overlapping use cases. Mass  account creation may result in suspension of all related accounts.  Please note that any violation of the Klipsam Rules is cause for  permanent suspension of all accounts.</li>
<li> <strong><a>Targeted Abuse</a></strong>:  You may not engage in targeted abuse or harassment. Some of the factors  that we take into account when determining what conduct is considered  to be targeted abuse or harassment are:      
<ul>
<li>if you are sending messages to a user from multiple accounts;</li>
<li>if the sole purpose of your account is to send abusive messages to others;</li>
<li>if the reported behavior is one-sided or includes threats</li>
</ul>
</li>
<li> <strong><a>Username Squatting</a></strong>:  You may not engage in username squatting. Accounts that are inactive  for more than six months may also be removed without further notice.  Some of the factors that we take into account when determining what  conduct is considered to be username squatting are:         
<ul>
<li>the number of accounts created</li>
<li>creating accounts for the purpose of preventing others from using those account names</li>
<li>creating accounts for the purpose of selling those accounts</li>
<li>using feeds of third-party content to update and maintain accounts under the names of those third parties</li>
</ul>
</li>
<li> <strong><a>Invitation spam</a></strong>: You may not use Klipsam.com's address book contact import to send repeat, mass invitations.</li>
<li> <strong><a>Selling usernames</a></strong>: You may not buy or sell Klipsam usernames.&nbsp;</li>
<li> <strong><a>Malware/Phishing</a></strong>: You may not publish or link to  malicious content intended to damage or disrupt another user&rsquo;s browser  or computer or to compromise a user&rsquo;s privacy.&nbsp;</li>
<li> <strong><a>Spam</a></strong>: You may not use the Klipsam service for the  purpose of spamming anyone. What constitutes &ldquo;spamming&rdquo; will evolve as  we respond to new tricks and tactics by spammers. Some of the factors  that we take into account when determining what conduct is considered to  be spamming are:         
<ul>
<li>If you have followed and/or unfollowed large amounts of users in a  short time period, particularly by automated means (aggressive following  or follower churn);</li>
<li>If you repeatedly follow and unfollow people, whether to build followers or to garner more attention for your profile;</li>
<li>If a large number of people are blocking you;</li>
<li>If a large number of spam complaints have been filed against you;</li>
<li>If you post duplicate content over multiple accounts or multiple duplicate updates on one account;</li>
<li>If you post multiple unrelated updates to a topic using tags, trending or popular topic, or promoted trend;</li>
<li>If you send large numbers of duplicate @replies or mentions;</li>
<li>If you send large numbers of unsolicited @replies or mentions in an aggressive attempt to bring attention to a service or link;</li>
<li>If you add a large number of unrelated users to lists in an attempt to bring attention to an account, service or link;</li>
<li>If you repeatedly create false or misleading content in an attempt to bring attention to an account, service or link;</li>
<li>Randomly or aggressively favoriting klips through automation in an attempt to bring attention to an account, service or link;</li>
<li>Randomly or aggressively Rekliping through automation in an attempt to bring attention to an account, service or link;</li>
<li>If you repeatedly post other users' account information as your own (bio, klips, url, etc.);</li>
<li>If you post misleading links (e.g. affiliate links, links to malware/click jacking pages, etc.);</li>
<li>Creating multiple misleading accounts in order to gain followers;</li>
<li>Selling followers;</li>
<li>Purchasing followers;</li>
<li>Using or promoting third-party sites that claim to get you more  followers (such as follower trains, sites promising "more followers  fast," or any other site that offers to automatically add followers to  your account);</li>
</ul>
</li>
<li> <strong><a>Pornography</a></strong>: You may not use obscene or pornographic images in either your profile photo, header photo, or user background.</li>
</ul>
<p style="margin-bottom: 10px;">Your account may be suspended for Terms of Service violations if any of the above is true. Accounts created to replace suspended  accounts will be permanently suspended.</p>
<p style="margin-bottom: 10px;">Accounts engaging in any of these behaviors may be investigated for  abuse. Accounts under investigation may be removed from Search for  quality. Klipsam reserves the right to immediately terminate your  account without further notice in the event that, in its judgment, you  violate these Rules or the <a href="/terms">Terms of Service</a>.</p>
<p>We may revise these Rules from time to time; the most current version will always be at <a href="/rules">Klipsam.com/rules</a>.</p>
	<br />
	<br />
</div>

	</div>
</div>
</div>
<!-- start footer -->
<?php include "page-parts".DS."footer.php"; ?>
</body>
</html>
