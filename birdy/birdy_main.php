<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');

header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0, no-cache", false);
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache"); // HTTP/1.0
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Pragma-directive: no-cache");
header("Cache-directive: no-cache");
//header("Expires: 0");

include_once "config".DS."config.php";
// create database connection
try {
	$db = new birdyDB();
} catch (PDOException $e) {
	die('Database connection could not be established.');
}

Class birdyCMS {

	protected static $_instance;
	var $homepage 		= false;
	var $externalPage	= false;
	var $current_page	= 'index';
	var $pageTitle		= '<meta property="og:title" content="Birdy CMS" /><title>Birdy CMS</title>';
	var $pageDescription= '<meta name="description" content="This is a page generated by Birdy CMS." /><meta property="og:description" content="This is a page generated by Birdy CMS." />';
	var $pageImage		= ''; // will be populated when birdy is constructed
	var $pageType		= '<meta property="og:type" content="website" />'; // could be 'movie' for videos for example
	var $MetaData		= array('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">');
	var $StyleSheets	= array();
	var $Javascripts	= array();
	var $Bottomscripts	= array();
	var $browser		= '';
	
	// DEFINES:
	//
	// _BIRDY
	// BIRDY_BASE
	// BIRDY_URL
	// BIRDY_SEF_URI
	// BIRDY_PARSED_URI
	// BIRDY_TEMPLATE_BASE
	// BIRDY_TEMPLATE_URL
	//

	public function __construct() {
		// Enable Error Reporting
		if (birdyConfig::$error_reporting!=0) {
			ini_set('display_errors',1);
			ini_set('display_startup_errors',1);
			error_reporting(birdyConfig::$error_reporting);
		}
		//=============================================================================
		$this->homepage = $this->check_homepage();
		$scheme = (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")) ? 'https' : 'http';
		$sef_uri = $_SERVER['REQUEST_URI'];
		if (strstr($sef_uri,'?')) {
			$sef_uri = explode('?',$sef_uri);
			$sef_uri = $sef_uri[0];
		}
		DEFINE('BIRDY_URL', $scheme.'://'.$_SERVER['HTTP_HOST'].'/');
		DEFINE('BIRDY_SEF_URI', $sef_uri);
		DEFINE('BIRDY_TEMPLATE_URL', BIRDY_URL.'templates/'.birdyConfig::$template.'/');
		DEFINE('BIRDY_TEMPLATE_BASE', BIRDY_BASE.DS.'templates'.DS.birdyConfig::$template);
		birdyConfig::setDEFINES();
		//=============================================================================
		$this->detectBrowser();
		birdySession::init();
		//=============================================================================
		// Now sanitize inputs!
		// this does not work, as various functionality doesn't work (infinite scrolling, login, etc.)
		// I will have to sanitize each one individually
		//$_SESSION = filter_var_array($_SESSION,FILTER_SANITIZE_STRING);
		//$_SERVER = filter_var_array($_SERVER,FILTER_SANITIZE_STRING);
		//$_REQUEST = filter_var_array($_REQUEST,FILTER_SANITIZE_STRING);
		//$_POST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
		//$_GET = filter_var_array($_GET,FILTER_SANITIZE_STRING);
		//=============================================================================
		if (birdyConfig::$use_lightbox) {
			$this->addScriptFile(BIRDY_URL.'birdy/js/right.js');
			$this->addScriptFile(BIRDY_URL.'birdy/js/right-lightbox.js');
			//$this->addScriptFile(BIRDY_URL.'birdy/js/jquery.colorbox-min.js');
		}
		// add jQuery here. Conflicting js could be loaded before or after
		$this->addScriptFile(BIRDY_URL.'birdy/js/jquery.js');
		$this->addScriptFile(BIRDY_URL.'birdy/js/jquery.lazyload.min.js');
		if (birdyConfig::$user_access) {
			// The Composer auto-loader (official way to load Composer contents) to load external stuff automatically
			//if (file_exists('vendor/autoload.php')) {
				//require 'vendor/autoload.php';
			//}
			// Start the php-login application
			//$app = new Application();
		}
		$this->pageImage = '<meta property="og:image" content="'.BIRDY_URL.'images/birdy_logo.png" />';
		self::$_instance = $this;
	}
	
	// returns an instance of the birdy class
	public static function getInstance() {
		return self::$_instance;
	}
	
	// check to see if we are in the homepage
	public function check_homepage() {
		if ($_SERVER['REQUEST_URI']=='/index.php' || empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI']=='/')
			$this->homepage = true;
		else
			$this->homepage = false;
		return $this->homepage;
	}
	
	// make a link to a SEF link
	public function setRoute($uri) {
		return birdyRouter::setRoute($uri);
	}
	
	// parse a SEF link back to original
	public function parseRoute() {
		return birdyRouter::parseRoute();
	}
	
	public function pageTitle($title) {
        //<title>Chris Michaelides | Tech Artist</title>
        $title = strip_tags($title);
		$this->pageTitle = '<meta property="og:title" content="'.$title.'" /><title>'.$title.'</title>';
	}
	
	public function pageDescription($description) {
        //<meta name="description" content="Innovative technology leader who can translate ideas to action and results. Effective team participant/leader skilled in communicating technical concepts and gaining support for new ideas. Customer advocate recognized for commitment to usability and ability to develop new capabilities from existing technologies. Quick learner eager for new professional challenges." />
        $description = strip_tags($description);
		$this->pageDescription = '<meta name="description" content="'.$description.'" /><meta property="og:description" content="'.$description.'" />';
	}
	
	public function pageImage($image) {
        if (strstr($image,'src=')) {
			$clean_image = explode('src="',$image);
			if (empty($clean_image)) {
				$clean_image = explode("src='",$image);
				$separator = "'";
			} else $separator = '"';
			$image = explode($separator,$clean_image[1]);
			$image = $image[0];
        }
		$this->pageImage = '<meta property="og:image" content="'.$image.'" />';
	}
	
	public function pageType($type) {
        //<title>Chris Michaelides | Tech Artist</title>
		$this->pageType = '<meta property="og:type" content="'.$type.'" />';
	}
	
	public function addMeta($name,$content) {
		$this->MetaData[] = '<meta name="'.$name.'" description="'.$content.'" />';
	}
	
	public function addCustomMeta($meta) {
		$this->MetaData[] = $meta;
	}
	
	public function addStyleSheet($css_url, $media='all') {
		$this->StyleSheets[] = '<link rel="stylesheet" type="text/css" media="'.$media.'" href="'.$css_url.'" />';
	}
	
	public function addStyle($css_style) {
		$this->StyleSheets[] = '<style>'.$style.'"</style>';
	}
	
	public function addScriptFile($js_file) {
		$this->Javascripts[] = '<script type="text/javascript" src="'.$js_file.'"></script>';
	}
	
	public function addScript($script) {
		$this->Javascripts[] = '<script type="text/javascript">'.$script.'</script>';
	}
	
	public function addScriptToBottom($script) {
		$this->Bottomscripts[] = '<script type="text/javascript">'.$script.'</script>';
	}
	
	public function redirect($uri) {
		/**
		* Too many redirections was adding more metadata to the head than it should.
		* What should happen is that only the LAST redirection should write its metadata to the header.
		* This static variable fixes that...
		* It's one of those situations where the programmer is happy it works, but doesn't exactly know how and why it works! :)
		*/
		static $times = 0;
		// add 1 use...
		$times++;
		
		if (!defined('BIRDY_PARSED_URI')) DEFINE('BIRDY_PARSED_URI', $uri);
		$uri = parse_url(BIRDY_URL.$uri);
		// replace only if not an action...
		$file= (!strstr($uri['path'],'/actions/')) ? str_replace("/","",$uri['path']) : $file = $uri['path'];
		$query = (isset($uri['query'])) ? explode("&",$uri['query']) : array();
		foreach ($query as $queryPart) {
			$part = explode("=",$queryPart);
			$_REQUEST[$part[0]] = $part[1];
		}
		ob_start();
		if (!$this->externalPage) include (BIRDY_TEMPLATE_BASE.DS.$file);
		$output = ob_get_clean();
		/**
		* if $times is bigger than zero, then we can add metadata to the head
		*
		* The logic here is that on double redirections the first redirection sets $times to 1, and includes a file that sends another
		* redirection and then returns back to this method to continue. Now on a double redirection, the included file redirects back 
		* here so $times gets +1 (becomes 2). This means that the second redirection will add its metadata (since $times>0), and then
		* it will set $times back to 0. So, after this execution, the program flow returns back here, where the first redirection 
		* is still active. But since $times has been set to 0 by the second (nested) redirection, the metadata of the first redirection
		* will not be added, as the below code will be ignored.
		*
		* Now that I explained it to myself, I understand it! ;)
		*/
		if ($times>0) {
			$canonical = substr(BIRDY_URL,0,-1).BIRDY_SEF_URI; // fix the url because too many // redirects infinitely
			if (birdyConfig::$googleHighlighterArticle || birdyConfig::$googleHighlighterComments) {
				$this->addScriptFile("https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?skin=".birdyConfig::$googleHighlighterSkin);
			}
			if (birdyConfig::$syntaxHighlighterArticle || birdyConfig::$syntaxHighlighterComments) {
				$this->addStyleSheet(BIRDY_URL.'birdy/helpers/syntaxHighlighter/styles/shCore.css',"screen,projection");
				$this->addStyleSheet(BIRDY_URL.'birdy/helpers/syntaxHighlighter/styles/shCore'.birdyConfig::$syntaxHighlighterTheme.'.css',"screen,projection");
				$this->addStyleSheet(BIRDY_URL.'birdy/helpers/syntaxHighlighter/styles/shTheme'.birdyConfig::$syntaxHighlighterTheme.'.css',"screen,projection");

				$shpath = BIRDY_URL.'birdy/helpers/syntaxHighlighter/scripts/';
				$this->addScriptFile($shpath.'shCore.js');
				$this->addScriptFile($shpath.'shAutoloader.js');
				
				//$scriptsToLoad = array("'code ".$shpath."shBrushplain.js'");
				$this->addScriptFile($shpath."shBrushplain.js");
				foreach (birdyConfig::$codesToHighlight as $code) {
					$this->addScriptFile($shpath."shBrush".$code.".js");
					//$scriptsToLoad[] = "'".$code." ".$shpath."shBrush".$code.".js'";
				}
				$this->addScript("SyntaxHighlighter.config.stripBrs=true;SyntaxHighlighter.all();");
			}
			// add disqus
			if (birdyConfig::$commentsMethod=='disqus') {
				//$output = str_replace("</head>",'<script src="'.BIRDY_URL.'birdy/js/jquery.disqus.js" type="text/javascript"></script></head>',$output);
				//$output = str_replace("</head>",'<script type="text/javascript">$(document).ready(function() {disqus_shortname  = "'.birdyConfig::$disqus_shortname.'";$.disqus();});</script></head>',$output);
				$disqus_code = "
				<!-- DISQUS ADDITION! -->
				<script type='text/javascript'>
				/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
				var disqus_shortname = '".birdyConfig::$disqus_shortname."'; // required: replace example with your forum shortname
				/* * * DON'T EDIT BELOW THIS LINE * * */
				(function () {
					var s = document.createElement('script'); s.async = true;
					s.type = 'text/javascript';
					s.src = '//' + disqus_shortname + '.disqus.com/count.js';
					(document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
				}());
				</script>
				<!-- DISQUS ADDITION! -->
				";
				$output = str_replace('</body>',$disqus_code.'</body>',$output);
			}
			// add bottomscript
			$output = str_replace("</body>",implode("",$this->Bottomscripts).'</body>',$output);
			// add javascript
			$output = str_replace("<head>","<head>".implode("",$this->Javascripts),$output);
			// add styles
			$output = str_replace("<head>","<head>".implode("",$this->StyleSheets),$output);
			// add metadata
			$output = str_replace("<head>","<head>".implode("",$this->MetaData),$output);
			// add page url
			$output = str_replace("<head>",'<head><meta property="og:url" content="'.$canonical.'" />',$output);
			// add page type
			$output = str_replace("<head>","<head>".$this->pageType,$output);
			// add page image
			$output = str_replace("<head>","<head>".$this->pageImage,$output);
			// add page description
			$output = str_replace("<head>","<head>".$this->pageDescription,$output);
			// add page title
			$output = str_replace("<head>","<head>".$this->pageTitle,$output);
			// add character encoding
			$output = str_replace("<head>","<head>".'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',$output);
		}
		// then check the system feedbacks...
		if (!empty($_SESSION["feedback_positive"])) {
			foreach($_SESSION["feedback_positive"] as $feedback) {
				$output = str_replace("{(birdy_feedback)}",'<div class="notification">'.$feedback.'</div>{(birdy_feedback)}',$output);
			}
			unset($_SESSION["feedback_positive"]);
		}
		if (!empty($_SESSION["feedback_neutral"])) {
			foreach($_SESSION["feedback_neutral"] as $feedback) {
				$output = str_replace("{(birdy_feedback)}",'<div class="warning">'.$feedback.'</div>{(birdy_feedback)}',$output);
			}
			unset($_SESSION["feedback_neutral"]);
		}
		if (!empty($_SESSION["feedback_negative"])) {
			foreach($_SESSION["feedback_negative"] as $feedback) {
				$output = str_replace("{(birdy_feedback)}",'<div class="alert">'.$feedback.'</div>{(birdy_feedback)}',$output);
			}
			unset($_SESSION["feedback_negative"]);
		}
		$output = str_replace("{(birdy_feedback)}","",$output);
		//============================================================================
		// display
		echo $output;
		// set $times to 0 to prevent any further additions of metadata.
		// no matter how many nested redirections are active, only the last one will be able to add metadata
		$times = 0;
	}
	
	public function sendMail($from_name, $from_email, $to_email, $subject, $body, $additional_headers='') {
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: '.$from_name.' <'.$from_email.'>' . "\r\n" . 'Reply-To: ' . $from_email;
		
		if (!empty($additional_headers)) $headers .= "\r\n" . $additional_headers;
	
	    $sendmail = mail( $to_email, $subject, $body, $headers );
	}
	
	public function CodeHighlight($text, $type="Article") {
		if ($type=="Article") {
			$text = str_replace('[highlight]','<span class="highlight">',$text);
			$text = str_replace('[/highlight]','</span>',$text);
			$text = str_replace("[@more@]","",$text);
		}
		$return = birdyComments::CodeHighlight($text,$type);
		return $return;
	}
	
	public function getComments($article_id) {
		return birdyComments::getComments($article_id);
	}
	
	public function displayComments($article_id) {
		return birdyComments::displayComments($article_id);
	}
	
	public function commentForm($commentform,$form_id='') {
		return birdyComments::commentForm($commentform,$form_id);
	}
	
	public function createPagination($name, $total, $limit=20) {
		$pagination = new birdyPagination();
		return $pagination->createPagination($name, $total, $limit);
	}
	
	public function showPagination($name) {
		$pagination = birdyPagination::getInstance();
		return $pagination->showPagination($name);
	}
	
	/**
	* Generic session method
	* serve as a "set" and "get" session variables
	* 
	* string variable: the name of the variable we want to set/get
	* var value: the value we want to give to this variable session (SET). if ommited the variable is not changed (GET)
	*
	* return: the value of the given session variable, or false if this session variable is not set.
	*/
	public function session($variable, $value=false) {
		if (!isset($_SESSION)) session_start();
		if (!empty($value)) $_SESSION[$variable] = $value;
		return isset($_SESSION[$variable]) ? $_SESSION[$variable] : false;
	}
	
	public function loadHelper($file) {
		require_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.$file.'.php');
	}
	
	public function detectBrowser() {
		$this->browser = birdyBrowser::detect();
		return $this->browser;
	}
	
	public function renderDisqus($article_id, $content_title) {
		$string = BIRDY_URL; $ascii = '';
		for ($i = 0; $i < strlen($string); $i++){
			$ascii .= ord($string[$i]);
		}
		$content_identifier = $article_id.$ascii;
		
		$canonical = substr(BIRDY_URL,0,-1).BIRDY_SEF_URI; // fix the url because too many // redirects infinitely
		/* THIS DOES NOT WORK!
		$disqus = 'http://disqus.com/embed/comments/?disqus_version=8994571c&base=default&f='.birdyConfig::$disqus_shortname.'&t_i='.$content_identifier.'&t_u='.rawurlencode($canonical).'&t_e='.rawurlencode($content_title).'&t_d='.rawurlencode($content_title).'&t_t='.rawurlencode($content_title).'&s_o=desc#2';
		$result = $this->get_web_page($disqus);
		if (empty($result['errno'])) {
			if ($result['http_code']=='200') {
				$content = $result['content'];
			} else {
				echo "failed with code:".$result['http_code'];
			}
			//echo "<pre>";print_r($result);echo "</pre>";
		}  else {
			echo $result['errmsg'];
		}
		if (empty($content)) return;
		//Adding the path to your stylesheet :
		$content = str_replace('</head>','<link rel="stylesheet" href="'.BIRDY_URL.'birdy/styles/disqus.css" /></head>', $content);
		//Specify the base url form the original url in case css and js are called relatively:
		$content = str_replace('</title>','</title><base href="http://disqus.com/embed/comments/" />', $content);
		
		$file = BIRDY_BASE.DS.'birdy'.DS.'disqus'.DS.$content_identifier.'.php';
		return file_put_contents($file, $content);
		*/
		$return = '<div id="disqus_thread">';
		/* THIS DOESN'T WORK EITHER!
		$return.= '<iframe width="100%" scrolling="no" frameborder="0" id="dsq-2" data-disqus-uid="2" allowtransparency="true" tabindex="0" title="Disqus" style="width: 100% ! important; border: medium none ! important; overflow: hidden ! important; height: 299px ! important;" horizontalscrolling="no" verticalscrolling="no">'.$content.'</iframe>';
		*/
		return '
							<div id="disqus_thread"></div>
							<script type="text/javascript">
								/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
								var disqus_shortname = "'.birdyConfig::$disqus_shortname.'"; // required: replace example with your forum shortname
								var disqus_identifier = "'.$content_identifier.'";
								var disqus_title = "'.$content_title.'";
								var disqus_url = "'.$canonical.'";
								(function() {
									var dsq = document.createElement("script"); dsq.type = "text/javascript"; dsq.async = true;
									dsq.src = "//" + disqus_shortname + ".disqus.com/embed.js";
									(document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(dsq);
								})();
							</script>
							<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered.</a></noscript>
				';
	}
	
	function url_exists($url) {
		$ch = @curl_init($url);
		@curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		@curl_setopt ($ch, CURLOPT_TIMEOUT, 5);
		$exist = @curl_exec($ch);
		@curl_close($ch);
		if (!$exist) {
			return false;
		}else{
			return true;
		} 
	} 

	/**
	 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
	 * array containing the HTTP server response header fields and content.
	 */
	private function get_web_page( $url, $pvars=array() )
	{
		$http_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0";
	    $options = array(
	        CURLOPT_RETURNTRANSFER => true,     // return web page
	        CURLOPT_HEADER         => false,    // don't return headers
	        CURLOPT_FOLLOWLOCATION => false,     // follow redirects
	        CURLOPT_ENCODING       => "",       // handle all encodings
	        CURLOPT_USERAGENT      => $http_agent, // who am i
	        CURLOPT_POST, 1,
	        CURLOPT_POSTFIELDS, $pvars,
	        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
	        CURLOPT_CONNECTTIMEOUT => 12,      // timeout on connect
	        CURLOPT_TIMEOUT        => 12,      // timeout on response
	        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
	        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
	    );
	
	    $ch      = curl_init( $url );
	    curl_setopt_array( $ch, $options );
	    $content = curl_exec( $ch );
	    $err     = curl_errno( $ch );
	    $errmsg  = curl_error( $ch );
	    $header  = curl_getinfo( $ch );
	    curl_close( $ch );
	
	    $header['errno']   = $err;
	    $header['errmsg']  = $errmsg;
	    $header['content'] = $content;
	    return $header;
	}
	
	public function tooltip($text) {
		return '<div style="position:relative;top:0px;left:0px"><span>'.$text.'</span></div>';
	}
	
	public function outputNotice($text) {
		if (!empty($text)) $_SESSION['feedback_positive'][] = $text;
	}
	
	public function outputWarning($text) {
		if (!empty($text)) $_SESSION['feedback_neutral'][] = $text;
	}
	
	public function outputAlert($text) {
		if (!empty($text)) $_SESSION['feedback_negative'][] = $text;
	}
	
	public function loadPhantom(
		$scripttoload, 
		$URLtoload, 
		$screenshot		= "",
		$Referer		= "", 
		$proxySettings	= ""/*"--proxy=65.48.113.25:9999 --proxy-type=https"; //--proxy=ip:port*/,
		$UserAgent		= "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0"
	) {
				$now = time(); // Just an example sending GET arguments to the page
				$cmd = 'phantomjs' . ' '
					. $proxySettings . ' '
					. "--web-security=no" . ' '
					. $scripttoload . ' '
					. $URLtoload . ' '
					. $screenshot . ' '
					. escapeshellarg($UserAgent) . ' '
					. $Referer . ' '
					. ' 2>&1'; // let's capture STDERR as well
					
				exec($cmd, $op, $er);
				$content = implode("",$op);
				
				return array('response'=>$content, 'errors'=>$er, 'command'=>$cmd);
	}
	
	/**
	 * Create a directory if it doesn't exist
	 */
	public function createDir($dir) {
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
	}
	
	/**
	 * Function to save the session before loading the new page
	 */
	public function loadPage($page = false) {
		if (!$page) $page = $_SERVER['HTTP_REFERER'];
		session_write_close();
		header('Location:'.$page);
	}

	/**
	 * Recursive String Replace - recursive_array_replace(mixed, mixed, array);
	 */
	function recur_replace($find, $replace, $array){
		if (!is_array($array)) {
			if (!is_object($array)) 
				return str_replace($find, $replace, $array);
			else
				return $array;
		}

		$newArray = array();
		foreach ($array as $key => $value) {
			$newArray[$key] = $this->recur_replace($find, $replace, $value);
		}
		
		return $newArray;
	}

	
}
