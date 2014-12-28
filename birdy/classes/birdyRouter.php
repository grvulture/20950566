<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');

Class birdyRouter {
		/*
			each template has different php files for each pages.
			each template has a router, with methods for each page to parse different uris as it wants.
			eg. blog.php can parse articles and blog categories
			route here sees /blog?... and passes to [template]/blog.php the uri
			this can be ?article_id=1 or cat_id=1
			blog.php sees that and queries the database according to each own router.
			this can return eg. /blog/1-This is an article
			or /blog/cat/1-This is a category
			
			on return, parseRoute sees first part (blog), and sends the uri to be parsed to the appropriate page
			
			NOTICE: solutions here do not take into account components and widget positions yet. Too bad... will have to fix this in the future
		*/

	private static function uritoarray($query) {
		$queryParts = explode('&', $query);
	
		$params = array();
		foreach ($queryParts as $param) {
			$item = explode('=', $param);
			$params[$item[0]] = $item[1];
		}
	
		return $params;
	}

	public static function setRoute($uri) {
		$parsed_url = parse_url(BIRDY_URL.$uri);
		
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? $pass."@" : '';
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query    = isset($parsed_url['query']) ? $parsed_url['query'] : '';
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

		if (!empty($query)) {
			// strip path, for later: name of page router and put in new url
			$path = str_replace(array("/",".php"),"",$path);
			// first include the appropriate router of the template
			if (file_exists(BIRDY_TEMPLATE_BASE.DS.'routers'.DS.$path.'.php')) {
				include_once(BIRDY_TEMPLATE_BASE.DS.'routers'.DS.$path.'.php');
				// prepare the name of the router object we gonna create...
				$router = $path.'Router';
			} else {
				include_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'route_helper.php');
				$router = new birdyRouteHelper();
			}
			// set the url query to associative array to pass it to the template's page router
			$query = self::uritoarray($query);
			// create the object...
			$router = new $router();
			// parse the uri
			$query = $router->setRoute($query);
		}
		// return the new url
		return $scheme.$user.$pass.$host.$port.'/'.$path.$query.$fragment;
	}
	
	public function parseRoute() {
		// Rerouting for SEO.
		$page = BIRDY_SEF_URI;
		if ($page=='/') $page='/index.php';

			// PARSE ACCORDING TO PAGE
			if (strstr($page,'#')) {
				$page = explode("#",$page);
				$page = $page[0];
				$anchor = "#".$page[1];
			} else {
				$anchor = '';
			}
			$args = explode("/",$page);
			$file = str_replace(".php","",$args[1]);
			$birdy= birdyCMS::getInstance();
			$birdy->current_page = $file;
			if (!file_exists(BIRDY_TEMPLATE_BASE.DS.$file.".php")) $file = '404';
			
			if (file_exists(BIRDY_TEMPLATE_BASE.DS.'routers'.DS.$file.'.php')) {
				include_once(BIRDY_TEMPLATE_BASE.DS.'routers'.DS.$file.'.php');
				$router = ($file=='404') ? 'Error404Router' : $file.'Router';
				// create the object...
				$router = new $router();
			} else {
				include_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'route_helper.php');
				$router = new birdyRouteHelper();
			}
			// parse the uri
			return array('file'=>$file.".php", 'args'=>$router->parseRoute($args), 'anchor'=>$anchor);
		// End of Rerouting
	}

}
?>