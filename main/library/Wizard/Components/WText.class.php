<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WText.class.php,v 1.2 2005/12/08 15:47:58 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponent.abstract.php');

/**
 * This class allows for the creation of simple block of text.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WText.class.php,v 1.2 2005/12/08 15:47:58 adamfranco Exp $
 */
class WText 
	extends WizardComponent 
{

	var $_value;
	var $_style = '';
	
	/**
	 * Virtual Constructor
	 * @param string $value
	 * @access public
	 * @return ref object
	 * @static
	 */
	function &withValue ($value) {
		$obj =& new WText();
		$obj->_value = $value;
		return $obj;
	}
	
	/**
	 * Sets the value of this text.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		$this->_value = $value;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		return true;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * 
	 * In this case, a "1" or a "0" is returned, depending on the checked state of the checkbox.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->_value;
	}
	
	/**
	 * Sets the CSS style for this span of text.
	 * @param string $style
	 * @return void
	 **/
	function setStyle($style)
	{
		$this->_style = $style;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$val = htmlspecialchars($this->_value, ENT_QUOTES);
		$style = str_replace('"', '\\"', $this->_style);
		$m = "<span style=\"$style\">$val</span>";
		
		return $m;
	}
}

?>