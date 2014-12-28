<?php
defined('_BIRDY') or die(__FILE__.': Restricted access');
//============================================================================
/**
 * Define the from email
 */ 
 
// email
define('TO_EMAIL', 'info@klipsam.com'); 
define('FROM_EMAIL', 'enquiry@klipsam.com');  
define('FROM_NAME', 'Klipsam.com'); 

/**
 * define the body of the email. You can add some shortcode, with this format: %ID%
 * 
 * ID = the id have you insert on the html markup.
 * 
 * e.g.
 * <input type="text" name="email" />
 *       
 * You can add on BODY, this:
 * email: %email%   
 */ 
define( 'BODY', '%userMsg%<br /><br /><small>Email Enquiry from %userName%, email %userEmail%.</small>' );
define( 'SUBJECT', 'Enquiry from Klipsam.com' );

// here the redirect, when the form is submitted
define( 'ERROR_URL', '/contact' );
define( 'SUCCESS_URL', '/contact' ); 
define( 'NOTSENT_URL', '/contact' );           

/**
 * Send the email.
 * 
 * SERVER-SIDE: the functions redirect to some URL, in base of result of control and send.
 * The urls must be set in the constants above: ERROR_URL, SUCCESS_URL, NOTSENT_URL
 * 
 * CLIENT-SIDE: in js/contact.js, there is already script for real-time checking of fields
 * and for ajax request of send email, that request in this page (sendmail.php) and echo the feedback message.    
 */   
sendemail();
    
// NO NEED EDIT
function sendemail() 
{
	// the message feedback of ajax request
	$msg = array(
		'error' => '<p class="error">Warning! All fields are required.</p>',
		'success' => '<p class="success">Email sent correctly. Thanks to get in touch with us! We will reply shortly.</p>',
		'not-sent' => '<p class="error">An error has been encountered. Please try again.</p>'
	);      
		
	// the field required, by name
	$required = array( 'userName', 'userEmail', 'userMsg' );
    
    $birdy= birdyCMS::getInstance();
    
    $ajax = false;
    
	if ( isset( $_POST['action'] ) AND $_POST['action'] == 'Submit' ) 
	{
	    $body = BODY;
	    
	    $post_data = array_map( 'stripslashes', $_POST );
	    
// 	    print_r($post_data);
// 	    die;
	    
	    foreach ( $required as $id_field ) {
    	    if( $post_data[$id_field] == '' || is_null( $post_data[$id_field] ) ) {
    	        if ( $ajax )
    	           end_ajax( $msg['error'] );
				else {
					$birdy->outputAlert($msg['error']);
					$birdy->loadPage( ERROR_URL );
				}
    	    }                       
    	}
	    
	    if( !is_email( $post_data['userEmail'] ) OR $post_data['userEmail'] == '' ) 
	        if ( $ajax )
	           end_ajax( $msg['error'] );
			else {
				$birdy->outputAlert($msg['error']);
				$birdy->loadPage( ERROR_URL );
			}
	    
	    foreach( $post_data as $id => $var )
	    {
	    	if( $id == 'userMsg' ) $var = nl2br($var);
			$body = str_replace( "%$id%", $var, $body );	
		}
	    
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: '.FROM_NAME.' <'.FROM_EMAIL.'>' . "\r\n" . 'Reply-To: ' . $post_data['email'];
	
	    $sendmail = mail( TO_EMAIL, SUBJECT, $body, $headers );
	         
		if ( $sendmail ) 
	        if ( $ajax )
	           end_ajax( $msg['success'] );
	        else {
    	       $birdy->outputNotice($msg['success']);
    	       $birdy->loadPage( SUCCESS_URL );
    	    }
	    else
	        if ( $ajax )
	           end_ajax( $msg['not-sent'] );
	        else {
    	       $birdy->outputWarning($msg['not-sent']);
    	       $birdy->loadPage( NOTSENT_URL );
    	    }
	} 
}

function is_email($email) 
{
    if (!preg_match("/[a-z0-9][_.a-z0-9-]+@([a-z0-9][0-9a-z-]+.)+([a-z]{2,4})/" , $email))
    {
        return false;
    }
    else
    {
        return true;
    }
}             

function end_ajax( $msg = '' ) {
    echo $msg;
    die;
}           

function redirect( $redirect = '' ) {
    header( 'Location: ' . $redirect );
    die;
}      

?>
