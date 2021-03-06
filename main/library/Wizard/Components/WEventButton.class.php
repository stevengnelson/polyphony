<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WEventButton.class.php,v 1.17 2007/11/16 18:39:40 adamfranco Exp $
 */ 

/**
 * This is a base class for any button in a {@link Wizard} that will throw an event when
 * it is activated.
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WEventButton.class.php,v 1.17 2007/11/16 18:39:40 adamfranco Exp $
 */
class WEventButton 
	extends WizardComponent
{
	private $events;
	var $_label = "NO LABEL";
	var $_pressed = false;
	var $_onclick = null;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/16/07
	 */
	public function __construct () {
		$this->events = array("nop");
	}
	
	/**
	 * virtual constructor
	 * @param string $event
	 * @param string $label
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withEventAndLabel ($event, $label) {
		$obj = new WEventButton();
		$obj->setEventAndLabel($event, $label);
		
		return $obj;
	}
	
	/**
	 * virtual constructor - creates the button with a "nop" event
	 * @param string $label
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withLabel ($label) {
		$obj = new WEventButton();
		$obj->_label = $label;
		return $obj;
	}
	
	/**
	 * Sets the event type and label for the button.
	 * @param string $event
	 * @param string $label
	 * @param optional string $textDomain the gettext() text domain to use for the label.
	 * @access public
	 * @return void
	 */
	function setEventAndLabel ($event, $label) {
		$this->setLabel($label);
		$this->setEvent($event);
	}
	
	/**
	 * Set the event type for the button
	 * 
	 * @param string $event
	 * @return void
	 * @access public
	 * @since 11/16/07
	 */
	public function setEvent ($event) {
		ArgumentValidator::validate($event, NonZeroLengthStringValidatorRule::getRule());
		$this->events = array($event);
	}
	
	/**
	 * Add an event type for the button
	 * 
	 * @param string $event
	 * @return void
	 * @access public
	 * @since 11/16/07
	 */
	public function addEvent ($event) {
		ArgumentValidator::validate($event, NonZeroLengthStringValidatorRule::getRule());
		$this->events[] = $event;
	}
	
	/**
	 * Answer the events triggered by this button
	 * 
	 * @return array
	 * @access public
	 * @since 11/16/07
	 */
	public function getEvents () {
		return $this->events;
	}
	
	/**
	 * Sets the label for the button.
	 * @param string $label
	 * @param optional string $textDomain the gettext() text domain to use for the label.
	 * @access public
	 * @return void
	 */
	function setLabel ($label) {
		$this->_label = $label;
	}
	
	/**
	 * Sets the on-click javascript to be called.
	 * @param string $javascript
	 * @access public
	 * @return void
	 */
	function addOnClick ($javascript) {
		$this->_onclick .= " ".$javascript;
	}
	
	/**
	 * Add a confirmation question that will be present in a javascript 'confirm' 
	 * dialog on button press.
	 * 
	 * @param string $confirmText
	 * @return void
	 * @access public
	 * @since 6/7/06
	 */
	function addConfirm ($confirmText) {
		if (!isset($this->_confirms))
			$this->_confirms = array();
		$this->_confirms[] = $confirmText;
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
		if ($val) {
			// trigger the save event on the wizard
			$wizard =$this->getWizard();
			foreach ($this->getEvents() as $event)
				$wizard->triggerLater($event, $wizard);
			$this->_pressed = true;
		}
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$val = $this->_pressed;
		$this->_pressed = false;
		return $val;
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
		$label = htmlspecialchars($this->_label, ENT_QUOTES);
		$onclick = '';
		if ($this->_onclick) $onclick = addslashes($this->_onclick) . ";";
		$m = "<input type='hidden' name='$name' id='$name' value='0' />\n";
		$m .= "<input type='button' value='$label' onclick='";
		
		if (isset($this->_confirms) && count($this->_confirms)) {
			$m .= "var confirmed = (confirm(\"";
			$m .= implode("\") && confirm(\"", $this->_confirms);
			$m .= "\"));";
		} else {
			$m .= "var confirmed = true; ";
		}
				
		$m .= " if (confirmed) { ";		
		$m .= 	$onclick;
		$m .= 	" if (validateWizard(this.form)) { ";
		$m .= 		" getWizardElement(\"$name\").value=\"1\";";
		$m .=		" submitWizard(this.form);";
		$m .= 	" }";
		$m .= " }";
		
		$m .= "'".($this->isEnabled()?"":" disabled='disabled'")." />";
		return $m;
	}
}

?>