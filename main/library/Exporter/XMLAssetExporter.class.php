<?php
/**
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetExporter.class.php,v 1.2 2005/10/18 15:50:38 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/Exporter/XMLRecordExporter.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLFileRecordExporter.class.php");

/**
 * Exports into XML for use with the XML Importer
 * 
 * @since 10/17/05
 * @package polyphony.exporter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLAssetExporter.class.php,v 1.2 2005/10/18 15:50:38 cws-midd Exp $
 */
class XMLAssetExporter {
		
	/**
	 * Constructor
	 *
	 * Maintains the archive, xmlfile, and file directory for data files
	 * 
	 * @param object Archive_Tar
	 * @param resource
	 * @param string
	 * @access public
	 * @since 10/17/05
	 */
	function XMLAssetExporter (&$archive, &$xmlFile, $fileDir) {
		$this->_archive =& $archive;
		$this->_xml =& $xmlFile;
		$this->_fileDir = $fileDir;
		
		$this->_childExporterList = array("XMLRecordExporter",
			"XMLAssetExporter");
		$this->_childElementList = array("records", "assets");
	}

	/**
	 * Exporter of Asset things
	 * 
	 * @param object HarmoniAsset
	 * @access public
	 * @since 10/17/05
	 */
	function export (&$asset) {
		$this->_object =& $asset;
		$this->_myId =& $this->_object->getId();
		$type =& $this->_object->getAssetType();

		fwrite($this->_xml,
"\t<asset ".
"id=\"".$this->_myId->getIdString()."\" ".
//isExisting?			
">\n".
"\t\t<name>".$this->_object->getDisplayName()."</name>\n".
"\t\t<description><![CDATA[".$this->_object->getDescription()."]]></description>\n".
"\t\t<type>\n\t\t\t<domain>".$type->getDomain()."</domain>\n".
"\t\t\t<authority>".$type->getAuthority()."</authority>\n".
"\t\t\t<keyword>".$type->getKeyword()."</keyword>\n");
		if ($type->getDescription() != "")
			fwrite($this->_xml,
"\t\t\t<description><![CDATA[".$type->getDescription()."]]></description>\n");
		fwrite($this->_xml,
"\t\t</type>\n");

//================== DATES GO HERE ===================//

		foreach ($this->_childElementList as $child) {
			$exportFn = "export".ucfirst($child);
			if (method_exists($this, $exportFn))
				$this->$exportFn();
		}
		
		fwrite($this->_xml,
"\t</asset>\n");
	}

	/**
	 * Exporter of recordstructures
	 * 
	 * Adds recordstructure elements to the xml, which contain the necessary
	 * information to create the same recordstructure.
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportRecords () {
		$idManager =& Services::getService("Id");
		$children =& $this->_object->getRecords();
		
		while ($children->hasNext()) {
			$child =& $children->next();
			$rS =& $child->getRecordStructure();
			if ($rS->getId() == $idManager->getId("FILE")) {
				$exporter =& new XMLFileRecordExporter($this->_archive,
					$this->_xml, $this->_fileDir);
			} else 
				$exporter =& new XMLRecordExporter($this->_xml);
			
			$exporter->export($child); // ????
		}
	}

	/**
	 * Exporter of child Assets
	 * 
	 * @access public
	 * @since 10/17/05
	 */
	function exportAssets () {
		$children =& $this->_object->getAssets();

		while ($children->hasNext()) {
			$child =& $children->next();

			$exporter =& new XMLAssetExporter($this->_archive, $this->_xml, 
				$this->_fileDir);

			$exporter->export($child);
		}
	}
}
?>