<?php
/**
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Action.class.php,v 1.4 2005/08/10 21:20:14 gabeschine Exp $
 */ 

/**
 * This class is the most simple abstraction of an action. It provides a structure
 * for common methods
 * 
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Action.class.php,v 1.4 2005/08/10 21:20:14 gabeschine Exp $
 * @since 4/28/05
 */
class Action {
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
	
	/**
	 * Answer the message to print when the user is not authorized to execute
	 * this action
	 * 
	 * @return string
	 * @access public
	 * @since 7/18/05
	 */
	function getUnauthorizedMessage () {
		
		// Default implementation. Override as necessary.
		
		$harmoni =& Harmoni::instance();
		$message = _("You are not authorized to ");
		if ($this->getHeadingText() == '') {
			$message .= _("execute this action, ");
			$message .= $harmoni->getCurrentAction();
		} else {
			$message .= $this->getHeadingText();
		}
		$message .= _(".");
		return $message;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return '';
	}
	
	/**
	 * Execute this action.
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute ( &$harmoni ) {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridden in child classes."));
	}
	
	/**
	 * Answer the requested module, maybe other than this action's module if this
	 * action was chained onto another's request.
	 * 
	 * @return string
	 * @access public
	 * @since 6/3/05
	 */
	function requestedModule () {
		$harmoni =& Harmoni::instance();
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		return $module;
	}
	
	/**
	 * Answer the requested action, maybe other than this action's action if this
	 * action was chained onto another's request.
	 * 
	 * @return string
	 * @access public
	 * @since 6/3/05
	 */
	function requestedAction () {
		$harmoni =& Harmoni::instance();
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		return $action;
	}
}

?>