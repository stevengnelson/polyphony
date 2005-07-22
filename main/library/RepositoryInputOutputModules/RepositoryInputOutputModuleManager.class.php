<?php
/**
 *
 * @package polyphony.library.repository.inputoutput
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RepositoryInputOutputModuleManager.class.php,v 1.6 2005/07/22 17:06:48 adamfranco Exp $
 */

/**
 * Require our necessary files
 * 
 */
require_once(dirname(__FILE__)."/modules/DataManagerPrimativesModule.class.php");
require_once(dirname(__FILE__)."/modules/HarmoniFileModule.class.php");

/**
 * The RepositoryInputOutModuleManager is responcible for sending records to the 
 * appropriate RepositoryInputOutputModule based on their Schema Formats.
 * 
 * @package polyphony.library.repository.inputoutput
 * @version $Id: RepositoryInputOutputModuleManager.class.php,v 1.6 2005/07/22 17:06:48 adamfranco Exp $
 * @since $Date: 2005/07/22 17:06:48 $
 * @copyright 2004 Middlebury College
 */

class RepositoryInputOutputModuleManager {

	/**
	 * Constructor, set up the relations of the Formats to Modules
	 * 
	 * @return object
	 * @access public
	 * @since 10/19/04
	 */
	function RepositoryInputOutputModuleManager () {
		$this->_modules = array();
		$this->_modules["DataManagerPrimatives"] =& new DataManagerPrimativesModule;
 		$this->_modules['Harmoni File'] =& new HarmoniFileModule;
// 		$this->_modules['text/plain'] = new PlainTextModule;
	}
	
	/**
	 * Assign the configuration of this Manager. Valid configuration options are as
	 * follows:
	 *	database_index			integer
	 *	database_name			string
	 * 
	 * @param object Properties $configuration (original type: java.util.Properties)
	 * 
	 * @throws object OsidException An exception with one of the following
	 *		   messages defined in org.osid.OsidException:	{@link
	 *		   org.osid.OsidException#OPERATION_FAILED OPERATION_FAILED},
	 *		   {@link org.osid.OsidException#PERMISSION_DENIED
	 *		   PERMISSION_DENIED}, {@link
	 *		   org.osid.OsidException#CONFIGURATION_ERROR
	 *		   CONFIGURATION_ERROR}, {@link
	 *		   org.osid.OsidException#UNIMPLEMENTED UNIMPLEMENTED}, {@link
	 *		   org.osid.OsidException#NULL_ARGUMENT NULL_ARGUMENT}
	 * 
	 * @access public
	 */
	function assignConfiguration ( &$configuration ) { 
		$this->_configuration =& $configuration;
	}

	/**
	 * Return context of this OsidManager.
	 *	
	 * @return object OsidContext
	 * 
	 * @throws object OsidException 
	 * 
	 * @access public
	 */
	function &getOsidContext () { 
		return $this->_osidContext;
	} 

	/**
	 * Assign the context of this OsidManager.
	 * 
	 * @param object OsidContext $context
	 * 
	 * @throws object OsidException An exception with one of the following
	 *		   messages defined in org.osid.OsidException:	{@link
	 *		   org.osid.OsidException#NULL_ARGUMENT NULL_ARGUMENT}
	 * 
	 * @access public
	 */
	function assignOsidContext ( &$context ) { 
		$this->_osidContext =& $context;
	} 
		
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @param array $partStructures An ordered array of the partStructures to include.
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function createWizardStepsForPartStructures ( & $record, & $wizard, & $partStructures ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->createWizardStepsForPartStructures($record, $wizard, $partStructures);
	}
	
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function createWizardSteps ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
				
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->createWizardSteps($record, $wizard);
	}
	
	/**
	 * Get the values submitted in the wizard and update the Record with them.
	 * 
	 * @param object $record
	 * @param object $wizard
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function updateFromWizard ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->updateFromWizard($record, $wizard);
	}
	
	/**
	 * Generate HTML for displaying the Record
	 * 
	 * @param object $record
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplay ( & $repositoryId, & $assetId, & $record ) {
		ArgumentValidator::validate($repositoryId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->generateDisplay($repositoryId, $assetId, $record);
	}
	
	/**
	 * Generate HTML for displaying particular fields of the Record 
	 * 
	 * @param object $record The record to print.
	 * @param array $partStructures An array of partStructures to print. 
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplayForPartStructures ( &$repositoryId, &$assetId, 
		&$record, &$partStructures ) 
	{
		ArgumentValidator::validate($repositoryId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));
		
		$recordStructure =& $record->getRecordStructure();
		$format = $recordStructure->getFormat();
		
		if (!is_object($this->_modules[$format]))
			throwError(new Error("Unsupported Format, '$format'", "RepositoryInputOutputModuleManager", true));
		
		return $this->_modules[$format]->generateDisplayForPartStructures($repositoryId, $assetId, $record, $partStructures);
	}
	
	
	/**
	 * Return the URL of a thumbnail image for a given Asset.
	 * 
	 * @param object Id $repositoryId
	 * @param object Id $assetId
	 * @return string The URL of the thumbnail
	 * @access public
	 * @since 7/22/05
	 */
	function getThumbnailUrlForAsset (&$assetId ) {
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		
		$repositoryManager =& Services::getService("RepositoryManager");
		$idManager =& Services::getService("IdManager");
		$asset =& $repositoryManager->getAsset($assetId);
		$repository =& $asset->getRepository();
		$repositoryId =& $repository->getId();
		
		$imageProcessor =& Services::getService("ImageProcessor");
		$fileRecords =& $asset->getRecordsByRecordStructure($idManager->getId("FILE"));
		while ($fileRecords->hasNextRecord()) {
			$record =& $fileRecords->nextRecord();
			if (!isset($thumbnailRecord)) {
				$thumbnailRecord =& $record;
			}
			
			$mimeTypeParts =& $record->getPartsByPartStructure(
				$idManager->getId("MIME_TYPE"));
			$mimeTypePart =& $mimeTypeParts->next();
			$mimeType =& $mimeTypePart->getValue();
			
			// If this record is supported by the image processor, then use it
			// to generate a thumbnail instead of the default icons.
			if ($imageProcessor->isFormatSupported($mimeType)) {
				$thumbnailRecord =& $record;
				break;	
			}
		}
		
		if (!isset($thumbnailRecord)) {
			return NULL;
		}
		
		$thumbnailRecordId =& $thumbnailRecord->getId();
		
		$filenameParts =& $record->getPartsByPartStructure(
			$idManager->getId("FILE_NAME"));
		$filenamePart =& $filenameParts->next();
		$filename =& $filenamePart->getValue();
		
		$thumbnailMimeTypeParts =& $record->getPartsByPartStructure(
			$idManager->getId("THUMBNAIL_MIME_TYPE"));
		$thumbnailMimeTypePart =& $thumbnailMimeTypeParts->next();
		$thumbnailMimeType =& $thumbnailMimeTypePart->getValue();
		
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-repository");
		
		
		// If we have a thumbnail with a valid mime type, print a link to that.
		$thumbnailName = ereg_replace("\.[^\.]+$", "", $filename);
		if (!is_null($thumbnailMimeType)) {
			$mime = Services::getService("MIME");
			$thumbnailName .= ".".$mime->getExtensionForMIMEType($thumbnailMimeType);
		}
		$url = $harmoni->request->quickURL("repository", "viewthumbnail",
			array(
				"repository_id" => $repositoryId->getIdString(),
				"asset_id" => $assetId->getIdString(),
				"record_id" => $thumbnailRecordId->getIdString(),
				"thumbnail_name" => $thumbnailName));
		
		
		$harmoni->request->endNamespace();
		
		return $url;
	}
}

?>