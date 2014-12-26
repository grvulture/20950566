<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');

Class birdyPagination {
	protected static $_instance;

	public function __construct() {
		self::$_instance = $this;
	}
	
	// returns an instance of the birdy class
	public static function getInstance() {
		return self::$_instance;
	}
	
	/**
	* General Pagination
	* this can serve as a generic pagination object for unlimited different paginations
	* each pagination is defined by $name, so there are no conflicts between different paginations
	*
	* string name: the name of the pagination
	* int total: the total number of objects for pagination
	* int limit: the limit of the number of objects that can appear on a page
	*
	* return: a pagination object for that specific pagination, defined by $name
	*/
	public function createPagination($name, $total, $limit=20) {
		// first naming conventions
		$given_limit = $limit;
		$limitstart = $name.'limitstart';
		$limit = $name.'limit';
		// then create this specific pagination object
		$paginationName = $name.'pagination';
		$this->$paginationName = new stdClass();
		// then create properties for this pagination
		$this->$paginationName->limitstart = (isset($this->$paginationName->limitstart)) ? $this->$paginationName->limitstart : 0;
		$this->$paginationName->limitstart = (isset($_REQUEST[$limitstart])) ? intval($_REQUEST[$limitstart]) : $this->$paginationName->limitstart;
		$this->$paginationName->limit = (isset($this->$paginationName->limit)) ? $this->$paginationName->limit : $given_limit;
		$this->$paginationName->limit = (isset($_REQUEST[$limit])) ? intval($_REQUEST[$limit]) : $this->$paginationName->limit;
		$this->$paginationName->limitend 	= $this->$paginationName->limitstart + $this->$paginationName->limit;
		$this->$paginationName->currentpage	= ceil( ($this->$paginationName->limitstart+$this->$paginationName->limit) / $this->$paginationName->limit );
		if ($this->$paginationName->limitend > $total) $this->$paginationName->limitend = $total;
		$this->$paginationName->totalpages = ceil( $total / $this->$paginationName->limit );
		// then return this properties as an object
		return $this->$paginationName;
	}

	public function showPagination($name) {
		$paginationName = $name.'pagination';
		/*
		echo "limitstart:".$this->$paginationName->limitstart.
		" limitend:".$this->$paginationName->limitend.
		" currentpage:".$this->$paginationName->currentpage.
		" totalpages:".$this->$paginationName->totalpages.
		" limit:".$this->$paginationName->limit."<br />";
		*/
		$return = '';
		$previous = ($this->$paginationName->limitstart)-($this->$paginationName->limit);
		if ($previous>-1) {
			$return.= '<a class="pagination left-arrow" rel="previous" href="'.BIRDY_SEF_URI."?".$name."limitstart=".$previous.'">&lsaquo;</a>';
		}
		for ($i=1;$i<=$this->$paginationName->totalpages;$i++) {
			$selected = ($i==$this->$paginationName->currentpage) ? "selected" : "";
			$limitstart = ($i*$this->$paginationName->limit) - $this->$paginationName->limit;
			$return.= "<a class='pagination $selected' href='".BIRDY_SEF_URI."?".$name."limitstart=".$limitstart."' ".$selected.">".$i."</a>";
		}
		$next = ($this->$paginationName->limitstart)+($this->$paginationName->limit);
		if ($this->$paginationName->currentpage < $this->$paginationName->totalpages) {
			$return.= '<a class="pagination right-arrow" rel="next" href="'.BIRDY_SEF_URI."?".$name."limitstart=".$next.'">&rsaquo;</a>';
		}
		return $return;
	}
	
}