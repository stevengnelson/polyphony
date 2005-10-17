<?php
/**
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECNonZeroRegex.class.php,v 1.1 2005/10/17 20:43:53 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/ErrorCheckingRules/WECRegex.class.php");
/**
 * Allows for regular expression javascript error checking.
 * 
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECNonZeroRegex.class.php,v 1.1 2005/10/17 20:43:53 adamfranco Exp $
 */
class WECNonZeroRegex 
	extends WECRegex 
{
	/**
	 * Returns true if the passed {@link WizardComponent} validates against this rule.
	 * @param ref object $component
	 * @access public
	 * @return boolean
	 */
	function checkValue (&$component) {
		$value = $component->getAllValues();
		if (!strval($value)) return false;
		
		return parent::checkValue($component);
	}
}


?>