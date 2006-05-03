<?php
/**
 * @package polyphony.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addAjax.act.php,v 1.1 2006/05/02 22:33:06 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Basket/Basket.class.php");

/**
 * 
 * 
 * @package polyphony.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addAjax.act.php,v 1.1 2006/05/02 22:33:06 adamfranco Exp $
 */
class addAjaxAction 
	extends Action
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
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
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Add an Item to Your Basket");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("basket");
		
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
		
		$basket =& Basket::instance();
		$viewAZ =& $idManager->getId("edu.middlebury.authorization.view");
		$assetIdList = RequestContext::value("assets");
		$assetIdArray = explode(",", trim($assetIdList));
		foreach ($assetIdArray as $id) {
			$assetId =& $idManager->getId($id);
			if ($authZ->isUserAuthorized(
				$viewAZ, 
				$assetId)) 
			{
				$basket->addItem($assetId);
			}
		}
		$harmoni->request->endNamespace();
		
		print $basket->getSmallBasketHtml();
		exit;
	}
}