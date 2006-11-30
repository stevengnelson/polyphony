<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewuser.act.php,v 1.2 2006/11/30 22:02:48 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/view.act.php");

/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewuser.act.php,v 1.2 2006/11/30 22:02:48 adamfranco Exp $
 */
class viewuserAction 
	extends viewAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/07/06
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute this action
	 * 
	 * @return mixed
	 * @access public
	 * @since 11/21/06
	 */
	function &execute () {
		// @todo check authorization to view a given user's tags.
		$idManager =& Services::getService('Id');		
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		
		if (RequestContext::value('agent_id')) {
			$this->agentId =& $idManager->getId(RequestContext::value('agent_id'));
		} else {
			$tagManager =& Services::getService('Tagging');	
			$this->agentId =& $tagManager->getCurrentUserId();
		}
		
		$harmoni->request->endNamespace();
		
		return parent::execute();
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 11/07/06
	 */
	function getHeadingText () {
		$heading = dgettext("polyphony", "Items tagged with '%1' by %2");
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		$heading = str_replace('%1', RequestContext::value('tag'), $heading);

		$agentManager =& Services::getService('Agent');			
		if ($agentManager->isAgent($this->agentId)) {
			$agent =& $agentManager->getAgent($this->agentId);
			$heading = str_replace('%2', $agent->getDisplayName(), $heading);
		} else
			$heading = str_replace('%2', $this->agentId->getIdString(), $heading);

		$harmoni->request->endNamespace();
		return $heading;
	}
	
	/**
	 * Answer the tags
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function &getItems () {
// 		$tagManager =& Services::getService("Tagging");
		$tag =& new Tag(RequestContext::value('tag'));
		$idManager =& Services::getService('Id');
		return $tag->getItemsForAgent($this->agentId);
	}
	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'viewuser';
	}
}

?>