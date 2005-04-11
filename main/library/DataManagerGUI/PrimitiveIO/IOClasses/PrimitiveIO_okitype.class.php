<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_okitype.class.php,v 1.3 2005/04/07 17:07:43 adamfranco Exp $
 *//

/**
 * 
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_okitype.class.php,v 1.3 2005/04/07 17:07:43 adamfranco Exp $
 */
class PrimitiveIO_okitype extends PrimitiveIO {
	function mkFormHTML(&$primitive, $label, $index) {
		$t = "[ update: <input type='checkbox' name='update-$label-$index' value='1'/> ]\n";
		$t .= "<b>".$label."[".$index."]</b>: \n";
		$domain = $authority = $keyword = '';
		if (is_object($primitive)) {
			$domain = htmlentities($primitive->getDomain(), ENT_QUOTES);
			$authority = htmlentities($primitive->getAuthority(), ENT_QUOTES);
			$keyword = htmlentities($primitive->getKeyword(), ENT_QUOTES);
		}
		$t .= "Domain: <input type='text' name='domain-$label-$index' size='15' value='$domain'/>\n";
		$t .= "Authority: <input type='text' name='authority-$label-$index' size='15' value='$authority'/>";
		$t .= "Keyword: <input type='text' name='keyword-$label-$index' size='15' value='$keyword'/>";
		return $t;
	}
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {
		if ($fieldSet->get("update-$label-$index")) {
			$domain = $fieldSet->get("domain-$label-$index");
			$authority = $fieldSet->get("authority-$label-$index");
			$keyword = $fieldSet->get("keyword-$label-$index");
			return new OKIType($domain, $authority, $keyword);
		}
		return ($null=null);
	}
}