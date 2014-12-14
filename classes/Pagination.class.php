<?php
/**
 * PHPSense Pagination Class
 *
 * PHP tutorials and scripts
 *
 * @package		PHPSense
 * @author		Jatinder Singh Thind
 * @copyright	Copyright (c) 2006, Jatinder Singh Thind
 * @link		http://www.phpsense.com
 */

// ------------------------------------------------------------------------


class Pagination {
	var $php_self;
	var $rows_per_page = 10; //Number of records to display per page
	var $total_rows = 0; //Total number of rows returned by the query
	var $links_per_page = 5; //Number of links to display per page
	var $append = ""; //Paremeters to append to pagination links
	var $sql = "";
	var $debug = false;
	var $page = 1;
	var $max_pages = 0;
	var $offset = 0;
	
	/**
	 * Constructor
	 *
	 * @param resource $connection Mysql connection link
	 * @param string $sql SQL query to paginate. Example : SELECT * FROM users
	 * @param integer $rows_per_page Number of records to display per page. Defaults to 10
	 * @param integer $links_per_page Number of links to display per page. Defaults to 5
	 * @param string $append Parameters to be appended to pagination links 
	 */
	
	function Pagination($sql, $rows_per_page = 20, $links_per_page = 5, $append = "") {
		$this->sql = $sql;
		$this->rows_per_page = (int)$rows_per_page;
		if (intval($links_per_page ) > 0) {
			$this->links_per_page = (int)$links_per_page;
		} else {
			$this->links_per_page = 5;
		}
		if($append != "") {
			$this->append = $append;
		}else{
			$this->append = "";
			$i = 0;
			$params = array_unique($_GET);
			foreach ($params as $key => $value) {
				if(!is_array($value) && $key != 'trailingParam' && $key != 'l' && $key != 'page') {
					$this->append .= '&'.$key.'='.$value;
					$i++;
				}
			}
		}
		$this->php_self = htmlspecialchars($_SERVER['PHP_SELF'] );
		if (isset($_GET['page'] )) {
			$this->page = intval($_GET['page'] );
		}
	}
	
	/**
	 * Executes the SQL query and initializes internal variables
	 *
	 * @access public
	 * @return resource
	 */
	function paginate() {
		global $dbase;

		//Find total number of rows
		$all_rs = $dbase->query($this->sql);

		if (! $all_rs) {
			if ($this->debug)
				echo "SQL query failed. Check your query.";
			return array();
		}
		
		$this->total_rows = $dbase->num_rows($all_rs);
				
		//Return FALSE if no rows found
		if ($this->total_rows == 0) {
			if ($this->debug)
				echo "Query returned zero rows.";
			return array();
		}
		
		//Max number of pages
		$this->max_pages = ceil($this->total_rows / $this->rows_per_page );
		if ($this->links_per_page > $this->max_pages) {
			$this->links_per_page = $this->max_pages;
		}
		
		//Check the page value just in case someone is trying to input an aribitrary value
		if ($this->page > $this->max_pages || $this->page <= 0) {
			$this->page = 1;
		}
		
		//Calculate Offset
		$this->offset = $this->rows_per_page * ($this->page - 1);
		
		//Fetch the required result set
		$rs = $dbase->query($this->sql . " LIMIT {$this->offset}, {$this->rows_per_page}" );
	
		if (! $rs) {
			if ($this->debug)
				echo "Pagination query failed. Check your query.";
			return array();
		}
		return $rs;
	}
	
	/**
	 * Display the result summary
	 *
	 * @access public
	 * @param string $tpl Template of the summary where you can use the following tags {row_per_page} {total_rows} {$page} {total_pages}
	 * @return string
	 */
	function renderHeader($tpl='Displaying {$page}-{row_per_page} of {total_rows} result(s)') {
		global $func;
		
		if ($this->total_rows != 0){
			$rowsPerPage = $this->rows_per_page;
			if($this->total_rows <= $this->rows_per_page) {
				$rowsPerPage = $this->total_rows;
			}
			$find = array('{row_per_page}','{total_rows}','{$page}','{total_pages}');
			$replace = array($rowsPerPage, $this->total_rows, $this->page, $this->max_pages);
			$summary = str_replace($find, $replace, $tpl);
			return $summary;

		}else{
			return "";
		}
	}
	
	/**
	 * Display the link to the first page
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to 'First'
	 * @return string
	 */
	function renderFirst($tag = '<<') {
		global $func;
		
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page != 1) {
			$url = $func->link($_GET['l'], 'page=1' . $this->append);
			return ' <a href="'.$url.'">' . $tag . '</a>';
		}
	}
	
	/**
	 * Display the link to the last page
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to 'Last'
	 * @return string
	 */
	function renderLast($tag = '>>') {
		global $func;
		
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page != $this->max_pages) {
			$url = $func->link($_GET['l'], 'page=' . $this->max_pages . '' . $this->append);
			return ' <a href="'.$url.'">' . $tag . '</a>';
		}
	}
	
	/**
	 * Display the next link
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to '>>'
	 * @return string
	 */
	function renderNext($tag = '>') {
		global $func;
		
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page < $this->max_pages) {
			$url = $func->link($_GET['l'], 'page=' . ($this->page + 1) . '' . $this->append);
			return '<a href="'.$url.'">' . $tag . '</a>';
		}
	}
	
	/**
	 * Display the previous link
	 *
	 * @access public
	 * @param string $tag Text string to be displayed as the link. Defaults to '<<'
	 * @return string
	 */
	function renderPrev($tag = '<') {
		global $func;
		
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page > 1) {
			$url = $func->link($_GET['l'], 'page=' . ($this->page - 1) . '' . $this->append);
			return ' <a href="'.$url.'">' . $tag . '</a>';
		}
	}
	
	/**
	 * Display the page links
	 *
	 * @access public
	 * @return string
	 */
	function renderNav($prefix = '<span class="page_link">', $suffix = '</span>') {
		global $func;
		
		if ($this->total_rows == 0)
			return FALSE;
		
		$batch = ceil($this->page / $this->links_per_page );
		$end = $batch * $this->links_per_page;
		if ($end == $this->page) {
			//$end = $end + $this->links_per_page - 1;
		//$end = $end + ceil($this->links_per_page/2);
		}
		if ($end > $this->max_pages) {
			$end = $this->max_pages;
		}
		$start = $end - $this->links_per_page + 1;
		$links = '';
	
		for($i = $start; $i <= $end; $i ++) {
			$class = '';
			if ($i == $this->page) {
				$class = 'class="active"';
			}
			$url = $func->link($_GET['l'], 'page=' . $i . '' . $this->append);
			$links .= ' ' . $prefix . '<a '.$class.' href="'.$url.'">' . $i . '</a>' . $suffix . ' ';
			
		}
		
		return $links;
	}
	
	/**
	 * Display full pagination navigation
	 *
	 * @access public
	 * @return string
	 */
	function renderFullNav() {
		if($this->max_pages > 1)
			return $this->renderFirst() . '&nbsp;' . $this->renderPrev() . '&nbsp;' . $this->renderNav() . '&nbsp;' . $this->renderNext() . '&nbsp;' . $this->renderLast();
		else
			return "";
	}
	
	/**
	 * Set debug mode
	 *
	 * @access public
	 * @param bool $debug Set to TRUE to enable debug messages
	 * @return void
	 */
	function setDebug($debug) {
		$this->debug = $debug;
	}
}
?>
