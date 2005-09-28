<?php
/**
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EmbeddedArrayResultPrinter.class.php,v 1.1 2005/09/08 20:48:53 gabeschine Exp $
 */

/**
 * Print out an Array of items in rows and columns in a TABLE HTML element.
 *
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EmbeddedArrayResultPrinter.class.php,v 1.1 2005/09/08 20:48:53 gabeschine Exp $
 */

class EmbeddedArrayResultPrinter {

	var $_array;
	var $_numColumns;
	var $_pageSize;
	var $_overridePage = -1;
	var $_callbackFunction;
	
	var $_tableStyle = '';
	var $_tdStyle = 'padding: 3px;';
	var $_trStyle = '';
	
	var $_currentItem;
	
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
	function EmbeddedArrayResultPrinter (& $array, $numColumns, 
									$numResultsPerPage, $callbackFunction) {
		ArgumentValidator::validate($array, ArrayValidatorRule::getRule());
		ArgumentValidator::validate($numColumns, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($numResultsPerPage, IntegerValidatorRule::getRule());
//		ArgumentValidator::validate($callbackFunction, StringValidatorRule::getRule());

		$this->_array =& $array;
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
	 * Sets the style of the TD elements of this table.
	 *
	 * @return void
	 **/
	function setTDStyle($style)
	{
		$this->_tdStyle = $style;
	}

	/**
	 * Sets the style of the TR elements of this table.
	 *
	 * @return void
	 **/
	function setTRStyle($style)
	{
		$this->_trStyle = $style;
	}

	/**
	 * Sets the style of the TABLE element.
	 *
	 * @return void
	 **/
	function setTableStyle($style)
	{
		$this->_tableStyle = $style;
	}

	/**
	 * Creates the HTML table element(s) for the passed content.
	 * @param string $content
	 * @access protected
	 *
	 * @return void
	 **/
	function createItemElement($content)
	{
		return $this->createTDElement($content);
	}

	/**
	 * Creates the links that go from one page to another.
	 * @param integer $numItems The total number of items in the result set.
	 * @param integer $startingNumber The index of the first item actually displayed.
	 *
	 * @return string
	 **/
	function createPageLinks($numItems, $startingNumber)
	{
		$harmoni =& Harmoni::instance();
		ob_start();
		$numPages = ceil($numItems/$this->_pageSize);
		$currentPage = floor($startingNumber/$this->_pageSize)+1; // add one for 1-based counting
		for ($i=1; $i<=$numPages; $i++) {
			if ($i > 0 && ($i+1) % 10 == 0)
				print "<br />";
			print " ";
			if ($i != $currentPage) {
				$url =& $harmoni->request->mkURLWithPassthrough();
				$url->setValue("starting_number", (($i-1)*$this->_pageSize+1));
				print "<a href='".$url->write()."'>";
			}
			print $i;
			if ($i != $currentPage)
				print "</a>";
		}

		// Add the links to the page
		$pageLinks = ob_get_contents();
		ob_end_clean();
		
		return $pageLinks;
	}
	
	/**
	 * Builds the header row of this result table.
	 *
	 * @return string
	 **/
	function createHeaderRow()
	{
		return '';
	}
	
	/**
	 * Builds the footer row of this result table.
	 *
	 * @return string
	 **/
	function createFooterRow()
	{
		return '';
	}
	
	/**
	 * Creates a table TD element with the passed content.
	 * @param string $content
	 * @param optional integer $colspan The number of columns for this element to span.
	 * @param optional string $align The text alignment.
	 *
	 * @return string
	 **/
	function createTDElement($content, $colspan = 0, $align='left')
	{
		$tdStyle = "style='".addslashes($this->_tdStyle)."'";
		$colspanAttr = '';
		if ($colspan > 0) $colspanAttr = " colspan='$colspan'";
		return "<td align='$align' $tdStyle$colspanAttr>$content</td>\n";
	}
	
	/**
	 * Returns the starting tag of a TR element.
	 *
	 * @return string
	 **/
	function createTRElement()
	{
		$trStyle = "style='".addslashes($this->_trStyle)."'";
		return "<tr $trStyle>\n";
	}

	/**
	 * Called if it is desired to override the page number that is displayed.
	 * @param integer $number
	 *
	 * @return void
	 **/
	function overridePageNumber($number)
	{
		$this->_overridePage = ($number-1) * $this->_pageSize + 1;
	}

	/**
	 * Returns a block of HTML markup.
	 * 
	 * @param optional string $shouldPrintFunction The name of a function that will
	 *		return a boolean specifying whether or not to filter a given result.
	 *		If null, all results are printed.
	 * @return string
	 * @access public
	 * @date 8/5/04
	 */
	function getMarkup ($shouldPrintFunction = NULL) {
		$defaultTextDomain = textdomain("polyphony");
		$harmoni =& Harmoni::instance();

		if ($harmoni->request->get('starting_number'))
			$startingNumber = $harmoni->request->get('starting_number');
		else
			$startingNumber = 1;
			
		if ($this->_overridePage > 0) $startingNumber = $this->_overridePage;

		$markup = '';
		$currentCol = 0;

		$tableStyle = "style='".addslashes($this->_tableStyle)."'";

		$table = "<table $tableStyle width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
		$table .= $this->createHeaderRow();
		$table .= $this->createTRElement();

		$endingNumber = $startingNumber+$this->_pageSize-1;
		$numItems = 0;	
		$shouldPrintEval = $shouldPrintFunction?"\$shouldPrint = ".$shouldPrintFunction."(\$item);":"\$shouldPrint = true;";
		if (count($this->_array)) {

			reset($this->_array);

			// trash the items before our starting number
			while ($numItems+1 < $startingNumber && $numItems < count($this->_array)) {
				$item =& current($this->_array);
				next($this->_array);

				// Ignore this if it should be filtered.
				eval($shouldPrintEval);
				if ($shouldPrint)
					$numItems++;
			}

			// print up to $this->_pageSize items
			$pageItems = 0;
			while ($numItems < $endingNumber && $numItems < count($this->_array)) {
				$item =& current($this->_array);
				next($this->_array);

				// Only Act if this item isn't to be filtered.

				eval($shouldPrintEval);
				if ($shouldPrint) {
					$this->_currentItem =& $item;
					$numItems++;
					$pageItems++;

					$itemArray = array (& $item);
					$params = array_merge($itemArray, $this->_callbackParams);
					$itemMarkup = call_user_func_array(
						$this->_callbackFunction, $params);
					
					if ($currentCol == $this->_numColumns) {
						$markup .= "</tr>" . $this->createTRElement();
						$currentCol = 0;
					}
					
					$markup .= $this->createItemElement($itemMarkup);
					$currentCol++;
					$this->_currentItem = null;
				}
			}

			//if we have a partially empty last row, add more empty layouts
			// to better-align the columns
			while($currentCol < $this->_numColumns) {
				$currentCol++;
				$markup .= $this->createTDElement("&nbsp;");
			}
			
			// find the count of items 
			while (true) {
				$item =& current($this->_array);
				if (!$item) break;
				next($this->_array);
				// Ignore this if it should be filtered.
				eval($shouldPrintEval);
				if ($shouldPrint)
					$numItems++;
			}	
		} else {
			$markup .= $this->createTDElement("<ul><li>"._("No items are availible.")."</li></ul>\n", $this->_numColumns);
		}	
	
		$markup .= "</tr>\n";

		// print out links to skip to more items if the number of Items is greater
		// than the number we display on the page
		
/*		if ($numItems > $this->_pageSize) {
			
			$table .= $this->createTRElement().$this->createTDElement($pageLinks, $this->_numColumns)."</tr>";			
		}*/

		$table .= $markup;
		
		if ($numItems > $this->_pageSize) {
			$pageLinks = $this->createPageLinks($numItems, $startingNumber);
			$table .= $this->createTRElement().$this->createTDElement($pageLinks, $this->_numColumns, "right")."</tr>";
		}
		
		$table .= $this->createFooterRow();
		$table .= "</table>\n";

		textdomain($defaultTextDomain);
		return $table;
	}	
}

?>