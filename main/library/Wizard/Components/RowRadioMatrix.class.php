<?php
/**
 * @since 11/1/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RowRadioMatrix.class.php,v 1.3 2008/03/20 13:09:31 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/RadioMatrix.abstract.php");

/**
 * The {@link RadioMatrix} is an inteface element that presents the user with 
 * a matrix of RadioButtons that allow choosing from a list of options for each
 * of a number of fields.
 *
 * The RowRadioMatrix prints its fields as rows and its options as columns.
 * 
 * @since 11/1/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RowRadioMatrix.class.php,v 1.3 2008/03/20 13:09:31 adamfranco Exp $
 */
class RowRadioMatrix
	extends RadioMatrix
{
		
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	public function getMarkup ($fieldName) {
		ob_start();
		
		print $this->getRulesJS($fieldName);
		
		print "\n<table border='1' class='radio_matrix row_radio_matrix'>";
		
		// Options Row
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th></th>";	// Fieldname column
		$options = $this->getOptions();
		for ($i = 0; $i < count($options); $i++) {
			print "\n\t\t\t<th class='option'>";
			if (!is_null($options[$i]->description))
				print "\n\t\t\t\t<a href='#' onclick=\"RadioMatrix.openDescriptionWindow(this, this.nextSibling); return false;\">";
			print $options[$i]->displayText;
			if (!is_null($options[$i]->description)) {
				print "</a>";
				print "<textarea name='option{$i}_desc' style='display: none;' rows='1' cols='1'>";
				print $options[$i]->description;
				print "</textarea>";
				print "\n\t\t\t";
			}
			print "</th>";
		}
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		
		// Fields rows
		$fields = $this->getFields();
		print "\n\t<tbody>";
		for ($i = 0; $i < count($fields); $i++) {
			if ($fields[$i]->spacerBefore !== false) {
				print "\n\t</tbody>\n\t<tbody>";
				print "\n\t\t<tr>\n\t\t\t<th colspan='".(count($options)+1)."' class='spacer'>";
				if (strlen($fields[$i]->spacerBefore))
					print $fields[$i]->spacerBefore;
				else
					print " &nbsp; ";
				print "</th>\n\t\t</tr>";
				print "\n\t</tbody>\n\t<tbody>";
				
			}
			print "\n\t<tr>";
			if (method_exists($this->_parent, 'getFieldName'))
				$displayText = Wizard::parseText($fields[$i]->displayText, $this->_parent->getChildren(), $this->_parent->getFieldName());
			else
				$displayText = $fields[$i]->displayText;
			print "\n\t\t<th class='field'>".$displayText."</th>";
			for ($j = 0; $j < count($options); $j++) {
				print "\n\t\t<td>".$this->getMatrixButton($fieldName, $i, $j)."</td>";
			}
			print "\n\t</tr>";
			if ($fields[$i]->spacerAfter !== false) {
				print "\n\t</tbody>\n\t<tbody>";
				print "\n\t\t<tr>\n\t\t\t<th colspan='".(count($options)+1)."' class='spacer'>";
				if (strlen($fields[$i]->spacerAfter))
					print $fields[$i]->spacerAfter;
				else
					print " &nbsp; ";
				print "</th>\n\t\t</tr>";
				print "\n\t</tbody>\n\t<tbody>";
				
			}
		}
		print "\n\t</tbody>";
		
		print "\n</table>";
		
		return ob_get_clean();
	}
}

?>