<?php
/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextField.class.php,v 1.8 2005/09/09 19:59:26 gabeschine Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/ErrorCheckingWizardComponent.abstract.php");

/**
 * This adds an input type='text' field to a {@link Wizard}.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WTextField.class.php,v 1.8 2005/09/09 19:59:26 gabeschine Exp $
 */
class WTextField 
	extends ErrorCheckingWizardComponent 
{

	var $_size = 30;
	var $_maxlength = 255;
	var $_style = null;
	var $_value = null;
	var $_startingDisplay = null;
	var $_readonly = false;
	var $_onchange = null;
	
	var $_showError = false;
	
	/**
	 * Sets the size of this text field.
	 * @param int $size
	 * @access public
	 * @return void
	 */
	function setSize ($size) {
		$this->_size = $size;
	}
	
	/**
	 * Sets the maxlength of the value of this field.
	 * @param integer $maxlength
	 * @access public
	 * @return void
	 */
	function setMaxLength ($maxlength) {
		$this->_maxlength = $maxlength;
	}
	
	/**
	 * Sets the text of the field to display until the user enters the field.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setStartingDisplayText ($text) {
		$this->_startingDisplay = $text;
	}
	
	/**
	 * Sets the CSS style of this field.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		$this->_style = $style;
	}

	/**
	 * Sets the value of this text field.
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
		$val = RequestContext::value($fieldName);
		if ($val) $this->_value = $val;
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$rule =& $this->getErrorRule();
		if (!$rule) return true;
		
		$err = $rule->checkValue($this);
		if (!$err) $this->_showError = true;
		return $err;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->_value;
	}
	
	/**
	 * Sets the readonly flag for this element.
	 * @param boolean $bool
	 *
	 * @return void
	 **/
	function setReadOnly($bool)
	{
		$this->_readonly = $bool;
	}
	
	/**
	 * Sets the javascript onchange attribute.
	 * @param string $commands
	 * @access public
	 * @return void
	 */
	function setOnChange($commands) {
		$this->_onchange = $commands;
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
		$name = RequestContext::name($fieldName);
		$m = "<input type='text' name='$name' id='$fieldName' size='".$this->_size."' maxlength='".$this->_maxlength."'".($this->_readonly?" readonly='readonly'":"");
		if ($this->_value != null && $this->_value != $this->_startingDisplay) {
			$m .= " value='".htmlentities($this->_value, ENT_QUOTES)."'";
		} else if ($this->_startingDisplay) {
			$v = htmlentities($this->_startingDisplay, ENT_QUOTES);
			$m .= " value='$v' onfocus='if (this.value == \"$v\") { this.value=\"\"; }'";
		}
		if ($this->_style) {
			$m .= " style=\"".str_replace("\"", "\\\"", $this->_style)."\"";
		}
		if ($this->_onchange) {
			$m .= " onchange=\"".str_replace("\"", "\\\"", $this->_onchange)."\"";
		}
		$m .= " />";
		
		$errText = $this->getErrorText();
		$errRule =& $this->getErrorRule();
		$errStyle = $this->getErrorStyle();
		
		if ($errText && $errRule) {
			$m .= "<span id='".$fieldName."_error' style=\"padding-left: 10px; $errStyle\">&laquo; $errText</span>";	
			$m .= Wizard::getValidationJavascript($fieldName, $errRule, $fieldName."_error", $this->_showError);
			$this->_showError = false;
		}
		
		return $m;
	}
}

?>