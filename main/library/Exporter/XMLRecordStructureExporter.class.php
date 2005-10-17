<?php
/**
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
 */ 

require_once(HARMONI."/Primitives/Chronology/DateAndTime.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLPartStructureExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 9/20/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRecordStructureExporter.class.php,v 1.1 2005/10/17 20:45:31 cws-midd Exp $
 */
class XMLRecordStructureExporter {
		
	/**
	 * Constructor
	 *
	 * 
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/20/05
	 */
	function XMLRecordStructureExporter (&$xmlFile) {
		$this->_xml =& $xmlFile;
		
		$this->_childExporterList = array("XMLPartStructureExporter");
		$this->_childElementList = array("partstructures");
	}

	/**
	 * Exporter of All things
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function export (&$rS) {
		$this->_object =& $rS;
		$this->_myId =& $this->_object->getId();


		fwrite($this->_xml,
"\t<recordstructure ".
"id=\"".$this->_myId->getIdString()."\" ".
"xml:id=\"".$this->_myId->getIdString()."\" ".
//isExisting?			
">\n".
"\t\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t\t<description>".$this->_object->getDescription()."/<description>\n".
"\t\t<format>".$type->getFormat()."</format>\n");		
		
		// recordStructures
		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}
		
		fwrite($this->_xml,
"\t</recordstructure>\n");
	}

	/**
	 * Exporter of partstructures
	 * 
	 * Adds partstructure elements to the xml, which contain the necessary
	 * information to create the same partstructure.
	 * 
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function exportPartstructures () {
		$children =& $this->_object->getPartStructures();
		
		while ($children->hasNext()) {
			$child =& $children->next();
			
			$exporter =& new XMLPartStructureExporter($this->_xml);
			
			$exporter->export($child); // ????
		}
	}

	/**
	 * <##>
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 9/26/05
	 */
	function <##> (<##>) {
		<##>
	}
	
}

?>