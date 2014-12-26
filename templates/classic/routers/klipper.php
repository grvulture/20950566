<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');

/**
* This is the generic rooting helper of Birdy.
* It should serve well for most pages by itself.
*
* All other page/uri routers should extend this basic router
* and either overwrite its methods or use as is.
*
*/
Class klipperRouter {
	
	//this transforms a normal uri to a SEF uri in Birdy
	public function setRoute($queryParts, $Pairs = array()) {
		foreach ($queryParts as $key => $value) {
			if (!empty($Pairs[$key])) $key = $Pairs[$value];
			/**
			* the above makes it possible to custom routers to give custom string representations of query parts.
			* eg. the query /blog.php?cat_id=1-General&item_id=35-a_new_article
			* with a $Pairs = array( 'cat_id'=>'category', 'item_id'=>'article' )
			* gives the url /blog/category/1-General/article/35-a_new_article
			*/
			$items[] = $key.'/'.$value;
		}
		return '/'.implode("/",$items);
	}
	
	/**
	* this transforms a SEF uri back to normal uri in Birdy
	* for generic purpose use, you can pass to it the possible query fields that a page could have.
	* 
	* array queryParts: parts of a SEF query splitted in an array. You don't need to do anything about this, Birdy takes care of it by itself
	* array $possible: any possible query fields a page could have. You must pass this to the method in order to return any results
	*
	* returns string: the transformed query from SEF back to normal
	*/
	public function parseRoute($queryParts, $Pairs=array()) {
		//echo "<pre>";print_r($queryParts);echo "</pre>";
		//start the query
		$queryBuilt = array();
		if (!empty($queryParts)) {
			// args[0] is always nothing, args[1] is our file, so we start $i from 2
			for($i=2;$i<count($queryParts);$i++) {
				//in klip.php we have this syntax: klip(the file)/klip_id/klip_title
				//and then anything can follow
				//what we want is to take the klip_id, ignore the title, and get anything else as normal pairs
				if ($i==2) $queryBuilt[] = 'user_name='.$queryParts[$i];
				else {
					//this assumes that keys are always followed by values, eg. key=value -> key/value
					$arg = $queryParts[$i];echo "$i ARG:$arg ";
					$value = $queryParts[$i+1];echo "| VALUE:$value<br />";
					if (!empty($Pairs[$arg])) $arg = $Pairs[$value];
					/**
					* the above makes it possible to custom routers to give custom string representations of query parts.
					* eg. the query /blog/category/1-General/article/35-a_new_article
					* with a $Pairs = array( 'category'=>'cat_id', 'article'=>'item_id' )
					* gives the url /blog.php?cat_id=1-General&item_id=35-a_new_article
					*/
					$queryBuilt[] = $arg."=".$value;
					$i++;
				}
			}
		}
		//echo "<pre>";print_r($queryBuilt);echo "</pre>";
		$queryBuilt = (empty($queryBuilt)) ? "" : '?'.implode("&",$queryBuilt);
		//echo "QueryBuilt:".$queryBuilt;
		return $queryBuilt;
	}
}
?>