<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStepContainer.class.php,v 1.2 2005/07/22 20:26:43 gabeschine Exp $
 */ 

/**
 * This is a special {@link WizardComponent} that will keep track of {@link WizardStep}s
 * and switch from one to the other.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStepContainer.class.php,v 1.2 2005/07/22 20:26:43 gabeschine Exp $
 */
class WizardStepContainer extends WizardComponent {
	var $_currStep;
	var $_steps;
	var $_stepNames;
	
	var $_parent;
	
	/**
	 * Constructor
	 * @access public
	 * @return void
	 */
	function WizardStepContainer () {
		$this->_currStep = 0;
		$this->_steps = array();
		$this->_stepNames = array();
	}
	
	/**
	 * Adds a new {@link WizardStep} to this container.
	 * @param string $name A reference/ID name for this wizard.
	 * @param ref object $step
	 * @access public
	 * @return ref object The new step object.
	 */
	function &addStep ($name, &$step) {
		ArgumentValidator::validate($step, ExtendsValidatorRule::getRule("WizardStep"), true);
		$this->_steps[] =& $step;
		$this->_stepNames[] = $name;
		$step->setParent($this);
		return $step;
	}
	
	/**
	 * Returns the current step number (starting at zero).
	 * @access public
	 * @return integer
	 */
	function getCurrentStep () {
		return $this->_currStep;
	}
	
	/**
	 * Sets the step to the one given by $name.
	 * @param string $name
	 * @access public
	 * @return void
	 */
	function setStep ($name) {
		$ind = array_search($name, array_keys($this->_steps));
		if ($ind !== false) {
			$this->_currStep = $ind;
		}
	}
	
	/**
	 * Returns an array of steps keyed by step name/id.
	 * @access public
	 * @return ref array
	 */
	function &getSteps () {
		return $this->_steps;
	}
	
	/**
	 * Returns the step referenced by $name.
	 * @param string $name
	 * @access public
	 * @return ref object
	 */
	function &getStep ($name) {
		$key = array_search($name, $this->_stepNames);
		if($key !== false) return $this->_steps[$key];
		return ($null = null);
	}
	
	/**
	 * Goes to the next step, if possible. 
	 * @access public
	 * @return void
	 */
	function nextStep () {
		$num = count($this->_steps);
		if ($this->_currStep != $num - 1) {
			$this->_currStep++;
		}
		
		$wizard =& $this->getWizard();
		$wizard->triggerLater("edu.middlebury.polyphony.wizard.step_changed", $this, array(
				'from'=>$this->_currStep-1, 'to'=>$this->_currStep));
	}
	
	/**
	 * Goes to the previous step, if possible. 
	 * @access public
	 * @return void
	 */
	function previousStep () {
		$num = count($this->_steps);
		if ($this->_currStep != 0) {
			$this->_currStep--;
		}
		
		$wizard =& $this->getWizard();
		$wizard->triggerLater("edu.middlebury.polyphony.wizard.step_changed", $this, array(
				'from'=>$this->_currStep+1, 'to'=>$this->_currStep));
	}
	
	/**
	 * Returns if this StepContainer has a next step.
	 * @access public
	 * @return boolean
	 */
	function hasNext () {
		return ($this->_currStep != count($this->_steps) - 1) && (count($this->_steps) != 0);
	}
	
	/**
	 * Returns if this StepContainer has a previous step.
	 * @access public
	 * @return boolean
	 */
	function hasPrevious () {
		return $this->_currStep != 0;
	}
	
	
	
	
	
	
	/**
	 * Sets this component's parent (some kind of {@link WizardComponentWithChildren} so that it can
	 * have access to its information, if needed.
	 * @param ref object $parent
	 * @access public
	 * @return void
	 */
	function setParent (&$parent) {
		$this->_parent =& $parent;
	}
	
	/**
	 * Returns the top-level {@link Wizard} in which this component resides.
	 * @access public
	 * @return ref object
	 */
	function &getWizard () {
		return $this->_parent->getWizard();
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
		for($i = 0; $i < count($this->_steps); $i++) {
			$this->_steps[$i]->update($fieldName."_".$this->_stepNames[$i]);
		}
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$array = array();
		for($i = 0; $i < count($this->_steps); $i++) {
			$array[$this->_stepNames[$i]] = $this->_steps[$i]->getAllValues();
		}
		return $array;
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
		// first we have to check our current step
		if (count($this->_steps)) {
			$theStep =& $this->_steps[$this->_currStep];
			$theName = $this->_stepNames[$this->_currStep];
			$markup = $theStep->getMarkup($fieldName."_".$theName);
			return $markup;
		}
		$error = dgettext("polyphony", "WIZARD ERROR: no steps were added to this WizardStepContainer!");
		return "<span style='color: red; font-weight: 900;'>$error</span>";
	}
}

?>