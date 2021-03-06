<?php
/**
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIOManager.class.php,v 1.15 2007/10/12 15:33:45 adamfranco Exp $
 */

/**
 * Handles the creation of {@link PrimitiveIO} objects for different data types, as registered with the DataTypeManager of Harmoni.
 *
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIOManager.class.php,v 1.15 2007/10/12 15:33:45 adamfranco Exp $
 * @author Gabe Schine
 */
class PrimitiveIOManager {
	
	/**
	 * Creates a new {@link PrimitiveIO} object for the given dataType.
	 * @param string $dataType a datatype string as registered with the DataManager of Harmoni
	 * @return ref object A new {@link PrimitiveIO} object.
	 * @access public
	 * @static
	 */
	static function createComponent($dataType) {
		$class = "PrimitiveIO_".$dataType;
		if (!class_exists($class)) return ($null=null);

		$obj = new $class();

		return $obj;
	}

	/**
	 * Creates a new {@link PrimitiveIO} object for the given dataType.
	 * @param string $dataType a datatype string as registered with the DataManager of Harmoni
	 * @return ref object A new {@link PrimitiveIO} object.
	 * @access public
	 * @static
	 */
	static function createAuthoritativeComponent($dataType) {
		$class = "PrimitiveIO_Authoritative_".$dataType;
		if (!class_exists($class)) return ($null=null);

		$obj = new $class();

		return $obj;
	}
	
	/**
	 * Create a new PrimitiveIO object that allows for selection from an authority
	 * list
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 5/1/06
	 */
	static function createComponentForPartStructure ($partStruct) {
		ArgumentValidator::validate($partStruct, ExtendsValidatorRule::getRule("PartStructure"));
		
		$partStructType =$partStruct->getType();		
		// get the datamanager data type
		$dataType = $partStructType->getKeyword();
// 		printpre($dataType);
		
		$authoritativeValues =$partStruct->getAuthoritativeValues();
		if ($authoritativeValues->hasNext()) {
			$authZManager = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			if ($authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify_authority_list"),
					$partStruct->getRepositoryId())) 
			{
				$component = new PrimitiveIO_AuthoritativeContainer();
				$component->setSelectComponent(
					PrimitiveIOManager::createAuthoritativeComponent($dataType));
				$component->setNewComponent(
					PrimitiveIOManager::createComponent($dataType));
			} else {
				$component = PrimitiveIOManager::createAuthoritativeComponent($dataType);
			}
						
			while($authoritativeValues->hasNext()) {
				$component->addOptionFromSObject($authoritativeValues->next());
			}
		} else {		
			// get the simple component for this data type
			$component = PrimitiveIOManager::createComponent($dataType);
		}
		
		return $component;
	}
	
}
