<?php

/**
 * Search Modules generate forms for and collect/format the subitions of said forms
 * for various Digital Repository search types.
 * 
 * @package polyphony.dr.search
 * @version $Id: SimpleFieldModule.class.php,v 1.1 2004/10/29 20:22:31 adamfranco Exp $
 * @date $Date: 2004/10/29 20:22:31 $
 * @copyright 2004 Middlebury College
 */

class SimpleFieldModule {
	
	/**
	 * Constructor
	 * 
	 * @param string $fieldName
	 * @return object
	 * @access public
	 * @date 10/28/04
	 */
	function SimpleFieldModule ( $fieldName ) {
		$this->_fieldname = $fieldName;
	}
	
	
	
	/**
	 * Create a form for searching.
	 * 
	 * @param string $action The destination on form submit.
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function createSearchForm ($action ) {
		ob_start();
		
		print "<form action='$action' method='post'>\n";
		
		print "\t<input type='text' name='".$this->_fieldname."'>\n";
		
		print "\t<input type='submit'>\n";
		print "</form>";
		
		$form = ob_get_contents();
		ob_end_clean();
		return $form;
	}
	
	/**
	 * Get the formatted search terms based on the submissions of the form
	 * 
	 * @return mixed
	 * @access public
	 * @date 10/28/04
	 */
	function getSearchCriteria () {
		return $_REQUEST[$this->_fieldname];
	}
}

?>