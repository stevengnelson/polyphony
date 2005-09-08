<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WPreviousStepButton.class.php,v 1.3 2005/09/08 20:48:53 gabeschine Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

/**
 * This adds a "Next" button to the wizard and throws the appropriate event.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WPreviousStepButton.class.php,v 1.3 2005/09/08 20:48:53 gabeschine Exp $
 */
class WPreviousStepButton extends WEventButton {
	var $_stepContainer;

	/**
	 * Constructor
	 * @param ref object $stepContainer A {@link WizardStepContainer} object.
	 * @access public
	 * @return void
	 */
	function WPreviousStepButton (&$stepContainer) {
		$this->setLabel(dgettext("polyphony", "Previous"));
		$this->_stepContainer =& $stepContainer;
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
		parent::update($fieldName);
		if ($this->getAllValues()) {
			// advance the step!
			$this->_stepContainer->previousStep();
		}
		$this->setEnabled($this->_stepContainer->hasPrevious());
	}

}

?>