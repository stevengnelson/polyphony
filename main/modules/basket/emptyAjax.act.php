<?php
/**
 * @since 5/5/06
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: emptyAjax.act.php,v 1.5 2007/09/19 14:04:54 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(POLYPHONY."/main/library/Basket/Basket.class.php");

/**
 * empties the basket
 * 
 * @since 5/5/06
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: emptyAjax.act.php,v 1.5 2007/09/19 14:04:54 adamfranco Exp $
 */
class emptyAjaxAction 
	extends Action {

	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/5/06
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/5/06
	 */
	function getHeadingText () {
		return _("Empty Your Selection");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/5/06
	 */
	function execute () {
		$harmoni = Harmoni::Instance();
		
		$basket = Basket::instance();
		$basket->removeAllItems();
		
		print $basket->getSmallBasketHtml();
		exit;
	}
}

?>