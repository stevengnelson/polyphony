<?php
/**
 * @since 8/5/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BasketManager.class.php,v 1.5 2005/12/14 21:04:49 cws-midd Exp $
 */ 

/**
 * A Basket is a session-persistant ordered collection of Asset Ids. Items can be
 * added and removed from it, as well as its contents viewed.
 * 
 * @since 8/5/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BasketManager.class.php,v 1.5 2005/12/14 21:04:49 cws-midd Exp $
 */
class BasketManager {
		
/*********************************************************
 * Class Methods - Instance-Creation/Singlton
 *********************************************************/

	/**
	 * Get the instance of the BasketPrinter.
	 * The BasketPrinter class implements the Singleton pattern. There is only ever
	 * on instance of the BasketPrinter object and it is accessed only via the 
	 * BasketPrinter::instance() method.
	 * 
	 * @return object BasketPrinter
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	function &instance () {
		if (!defined("BASKETMANAGER_INSTANTIATED")) {
			$GLOBALS['BASKETMANAGER'] =& new BasketManager();
			define("BASKETMANAGER_INSTANTIATED", true);
		}
		
		return $GLOBALS['BASKETMANAGER_INSTANTIATED'];
	}

/*********************************************************
 * Instance Methods
 *********************************************************/	

	/**
	 * The constructor.
	 * @access public
	 * @return void
	 **/
	function BasketManager() {
		// Verify that there is only one instance of Harmoni.
		$backtrace = debug_backtrace();
		if (false && $GLOBALS['BASKETMANAGER_INSTANTIATED'] 
			|| !(
				$backtrace[1]['class'] == 'BasketManager'
				&& $backtrace[1]['function'] == 'instance'
				&& $backtrace[1]['type'] == '::'
			))
		{
			die("<br/><strong>Invalid BasketPrinter instantiation at...</strong>"
			."<br/> File: ".$backtrace[0]['file']
			."<br/> Line: ".$backtrace[0]['line']
			."<br/><strong>Access BasketManager with <em>BasketManager::instance()</em></strong>");
		}
	}
	
	/**
	 * Answer the Basket
	 * 
	 * @return object OrderedSet
	 * @access public
	 * @since 8/5/05
	 */
	function &getBasket () {
		$setManager =& Services::getService("Sets");
		$idManager =& Services::getService("Id");
		return $setManager->getTemporarySet($idManager->getId("__basket"));
	}
	
	/**
	 * removes unauthorized assets from the basket
	 * 
	 * @return void
	 * @access public
	 * @static
	 * @since 12/14/05
	 */
	function cleanBasket () {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");

		$basket =& BasketManager::getBasket();
		$basket->reset();
		while ($basket->hasNext()) {	
			$id =& $basket->next();
			if (!$authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"), $id))
			{
				$basket->removeItem($id);
				$basket->reset();
			}
		}
		$basket->reset();
	}
	
	/**
	 * Return an XHTML string of a small version of the basket for use in a header. 
	 * Includes a link and the number of items in it.
	 * 
	 * @return object Component
	 * @access public
	 * @since 8/5/05
	 */
	function &getSmallBasketBlock () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("basket");
		
		BasketManager::cleanBasket();
		
		ob_start();
		$setManager =& Services::getService("Sets");
		$idManager =& Services::getService("Id");
		$basket =& $setManager->getTemporarySet($idManager->getId("__basket"));
		
		print "<a href='";
		print $harmoni->request->quickURL("basket", "view");
		print "'>";
		print "<img src='".POLYPHONY_PATH."/main/library/Basket/icons/basket.png' height='25px' border='0' alt='"._("Basket")."' align='middle' /></a>";
		print "<a href='";
		print $harmoni->request->quickURL("basket", "view");
		print "'>";
		print "(".$basket->count()." "._("items").")";
		print "</a>";
		
		$block = new Block(ob_get_contents(), ALERT_BLOCK);
		ob_end_clean();
		$harmoni->request->endNamespace();
		return $block;
	}
	
}

?>