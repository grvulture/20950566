<?php
defined('_BIRDY') or die(__FILE__.': Restricted access');
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
include "page-parts".DS."top-of-page.php";
//============================================================================
$birdy->pageTitle("Contact | Chris Michaelides");
$birdy->pageDescription("Please feel free to contact me for any questions you may have.");
?>
                <!-- START MAP -->
                <div class="header-map hide-if-no-js">
                    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            var position = new google.maps.LatLng(49.45,11.05); // <-- Edit here with the latitude and longitude of your map
                            var settings = {
                              zoom: 5,
                              center: position,
                              mapTypeControl: true,
                              mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
                              navigationControl: true,
                              navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
                              mapTypeId: google.maps.MapTypeId.ROADMAP                    };
                            var map = new google.maps.Map(document.getElementById("map"), settings);
                            
                            var marker = new google.maps.Marker({
                                    position: position,
                                    map: map
                            });
                        });
                        
                         var header_map = {"tab_open":"I am here","tab_close":"Close map"};
                    </script>
                    <div id="map-wrap">
                        <div id="map"></div>
                    </div>
                    <div id="ds-h" class="shadow">
                        <div class="ds h1 o1"></div>
                        <div class="ds h2 o2"></div>
                        <div class="ds h3 o3"></div>
                        <div class="ds h4 o4"></div>
                        <div class="ds h5 o5"></div>
                    </div>
                    <a href="#" class="tab-label closed">I am here</a>
                </div>    
                <!-- END MAP -->
        
                <!-- START CONTENT -->
                <div id="content" class="layout-sidebar-right group">
                
                    <!-- START PRIMARY -->
                    <div id="primary" class="hentry group wrapper-content" role="main">
                    
                        <h2>Contact</h2>
                    
                        <form class="contact-form" method="post" action="sendmail.php" enctype="multipart/form-data">

                            <!-- The feedback message is here -->
                        	<div class="usermessagea"></div>
                        	
                        	<fieldset>
                        
                        		<ul>
                        
                                    <!-- NAME FIELD -->
                        			<li class="text-field">
                        				<label for="name-main">
                        					<span class="label">What's your <span class="highlight-text">name</span>?</span>
                        				</label>
                        				
                        				<input type="text" name="name" id="name-form" class="required" value="" />                   
                        				<div class="msg-error"></div>
                        			</li>            
                                    <!-- END NAME FIELD -->
                                    
                                    <!-- EMAIL FIELD -->
                        			<li class="text-field">
                        				<label for="email-main">
                        					<span class="label">What's your <span class="highlight-text">e-mail</span>?</span> 
                        				</label>
                        
                        				<input type="text" name="email" id="email-form" class="required email-validate" value="" />
                        				<div class="msg-error"></div>
                        			</li>   
                                    <!-- END EMAIL FIELD -->
                                    
                                    <!-- MESSAGE FIELD -->
                        			<li class="textarea-field">
                        				<label for="message-main">
                        					<span class="label">How can I <span class="highlight-text">help</span> you?</span>
                        				</label>
                        				
                        				<textarea name="message" id="message-form" rows="8" cols="30" class="required"></textarea>
                        				<div class="msg-error"></div>
                        			</li>      
                                    <!-- END MESSAGE FIELD -->
                                    
                                    <!-- SUBMIT FIELD -->
                        			<li class="submit-button">
                        				<input type="hidden" name="action" value="sendmail" id="action" />
                        				<input type="submit" name="sendmail" value="send message" class="sendmail alignleft" />			
                                    </li>
                        		</ul>
                        
                        	</fieldset>
                        </form>
                        
                        <script type="text/javascript">
                            // specif here the message for each field of contact form, by ID of field
                        	var error_messages = {
                        		name: "A valid name is required.",
                        		email: "Write a valid email address.",
                        		message: "Insert a message."
                        	};
                        </script>
                        
                    </div>
                    <!-- END CONTENT -->
                    
                    <!-- START SIDEBAR -->
                    <div id="sidebar" class="group">
                    
                        <div class="widget-first widget text-image">
                            <h3>Customer support</h3>
                            <!--<div class="text-image" style="text-align:left"><img src="images/callus3.gif" alt="Customer support" /></div>-->
                            <p>Please feel free to contact me for any questions you may have.</p>
                        </div>
                        
                        <div class="widget-last widget text-image">
                            <div class="box-post-thumb sphere" style="width:185px;margin-left:10px;"><img style="width:185px" src="<?php echo $chris_image; ?>" alt="" /></div>
                        </div>
                        
                    </div>
                    <!-- END SIDEBAR -->     
                    
                </div>
                <!-- END CONTENT -->
        
<?php include "page-parts".DS."footer.php"; ?>
