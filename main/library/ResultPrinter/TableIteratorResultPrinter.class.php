<?php
/**
 * @package polyphony.library.resultprinter
 */
 
/**
 * Print out an Iterator of items in a table spread over multiple pages.
 * 
 * @package polyphony.resultprinter
 * @version $Id: TableIteratorResultPrinter.class.php,v 1.6 2005/03/28 23:24:12 nstamato Exp $
 * @date $Date: 2005/03/28 23:24:12 $
 * @copyright 2004 Middlebury College
 */

class TableIteratorResultPrinter {
	
	
	/**
	 * Constructor
	 * 
	 * @param object $iterator The iterator to print.
	 * @param integer $numColumns The number of result columns to print on each page.
	 * @param integer $numResultsPerPage The number of iterator items to print on a page.
	 * @param string $callbackFunction The name of the function that will be called to
	 *			to print each result.
	 * @param optional mixed $callbackArgs Any additional arguements will be stored
	 *			and passed on to the callback function.
	 * @access public
	 * @date 8/5/04
	 */
	function TableIteratorResultPrinter (& $iterator, $numColumns, 
									$numResultsPerPage, $callbackFunction) {
		ArgumentValidator::validate($iterator, new HasMethodsValidatorRule("hasNext", "next"));
		ArgumentValidator::validate($numColumns, new IntegerValidatorRule);
		ArgumentValidator::validate($numResultsPerPage, new IntegerValidatorRule);
		ArgumentValidator::validate($callbackFunction, new StringValidatorRule);
		
		$this->_iterator =& $iterator;
		$this->_numColumns =& $numColumns;
		$this->_pageSize =& $numResultsPerPage;
		$this->_callbackFunction =& $callbackFunction;
		
		$this->_callbackParams = array();
		$args =& func_get_args();
		for ($i=4; $i<count($args); $i++) {
			$this->_callbackParams[] =& $args[$i];
		}
	}
	
	
	
	/**
	 * Returns a layout of the Results
	 * 
	 * @param object Harmoni The Harmoni object containing context data.
	 * @param optional string $shouldPrintFunction The name of a function that will
	 *		return a boolean specifying whether or not to filter a given result.
	 *		If null, all results are printed.
	 * @return object Layout A layout containing the results/page links
	 * @access public
	 * @date 8/5/04
	 */
	function &getLayout (& $harmoni, $shouldPrintFunction = NULL) {
		$startingNumber = ($_REQUEST['starting_number'])?$_REQUEST['starting_number']:1;
		
		$yLayout =& new YLayout();
		$layout =& new Container($yLayout, OTHER, 1);
		
		ob_start();
		$endingNumber = $startingNumber+$this->_pageSize-1;
		$numItems = 0;
		print "\n<table cellspacing='10px'>";
		print  "\n<tr>";
		if ($this->_iterator->hasNext()) {
			
			// trash the items before our starting number
			while ($this->_iterator->hasNext() && $numItems+1 < $startingNumber) {
				$item =& $this->_iterator->next();
				
				// Ignore this if it should be filtered.
				if (!$shouldPrintFunction || $shouldPrintFunction($item))
					$numItems++;
			}
			
			
			// print up to $this->_pageSize items
			$pageItems = 0;
			while ($this->_iterator->hasNext() && $numItems < $endingNumber) {
				$item =& $this->_iterator->next();
				
				// Only Act if this item isn't to be filtered.
				if (!$shouldPrintFunction || $shouldPrintFunction($item)) {
					$numItems++;
					$pageItems++;
					
					// Table Rows subtract 1 since we are counting 1-based
					if ($pageItems > 1 && ($pageItems-1) % $this->_numColumns == 0) 
						print  "\n</tr>\n<tr>";
					
					print "\n<td style='border: 1px solid;  padding: 5px;'>";
					
					$itemArray = array (& $item);
					$params = array_merge($itemArray, $this->_callbackParams);
					call_user_func_array($this->_callbackFunction, $params);
					
					
					print  "\n</td>";
				}
			}
			
			// find the count of items 
			while ($this->_iterator->hasNext()) {
				$item =& $this->_iterator->next();
				
				// Ignore this if it should be filtered.
				if (!$shouldPrintFunction || $shouldPrintFunction($item))
					$numItems++;
			}	
		} else {
			print  "\n\t<td>"._("No <em>Items</em> are availible.")."</td>";
		}
		print  "\n</tr>";
		print  "\n</table>";
		
		$resultBlock =& new Block (ob_get_contents(), 2);
		ob_end_clean();
		
		
		// print out links to skip to more items if the number of Items is greater
		// than the number we display on the page
		ob_start();
		if ($numItems > $this->_pageSize) {
			$numPages = ceil($numItems/$this->_pageSize);
			$currentPage = floor($startingNumber/$this->_pageSize)+1; // add one for 1-based counting
			for ($i=1; $i<=$numPages; $i++) {
				if ($i > 0 && ($i+1) % 10 == 0)
					print "<br />";
				print " ";
				if ($i != $currentPage)
					print "<a href='".MYURL."/".implode("/", $harmoni->pathInfoParts)."?starting_number=".(($i-1)*$this->_pageSize+1)."'>";
				print $i;
				if ($i != $currentPage)
					print "</a>";
			}
			
			// Add the links to the page
			$pageLinkBlock =& new Block(ob_get_contents(), 2);
			ob_end_clean();
			$layout->add($pageLinkBlock, null, null, CENTER, CENTER);
		}
		
		$layout->add($resultBlock, null, null, CENTER, CENTER);
		
		if ($numItems > $this->_pageSize) {
			$layout->add($pageLinkBlock, null, null, CENTER, CENTER);
		}
		
		return $layout;
	}	
}

?>