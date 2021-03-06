<?php
/**
 * @since 12/6/06
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRemoteFileRecordImporter.class.php,v 1.5 2007/10/10 22:58:48 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/XMLFileRecordImporter.class.php");
require_once(dirname(__FILE__)."/XMLFileUrlPartImporter.class.php");
require_once(dirname(__FILE__)."/XMLFileNamePartImporter.class.php");
require_once(dirname(__FILE__)."/XMLFileSizePartImporter.class.php");

/**
 * <##>
 * 
 * @since 12/6/06
 * @package polyphony.importer
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XMLRemoteFileRecordImporter.class.php,v 1.5 2007/10/10 22:58:48 adamfranco Exp $
 */
class XMLRemoteFileRecordImporter 
	extends XMLFileRecordImporter
{
		
	/**
	 * Sets up importer's self-knowledge
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function setupSelf () {
		$this->_childImporterList = array (
			"XMLFileUrlPartImporter",
			"XMLFileNamePartImporter", 
			"XMLFileSizePartImporter",
			"XMLMIMEPartImporter", 
			"XMLFileDimensionsPartImporter",
			"XMLThumbDataPartImporter", 
			"XMLThumbMIMEPartImporter", 
			"XMLThumbDimensionsPartImporter", 
			"XMLFilepathPartImporter", 
			"XMLThumbpathPartImporter");
		$this->_childElementLIst = NULL;
		$this->_info = array();
	}
	
	/**
	 * Filters nodes of incorrect type
	 * 
	 * @param object DOMIT_Node
	 * @return boolean
	 * @static
	 * @access public
	 * @since 10/6/05
	 */
	static function isImportable ($element) {
		if ($element->nodeName == "remotefilerecord")
			return true;
		else
			return false;
	}
	
	/**
	 * Sets the node's internal information
	 * 
	 * @access public
	 * @since 10/6/05
	 */
	function getNodeInfo () {
		$idManager = Services::getService("Id");
		
		$this->_info['recordStructureId'] =$idManager->getId("REMOTE_FILE");
	}
	
}

?>