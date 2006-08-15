<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createcourse.act.php,v 1.2 2006/08/02 19:59:36 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."/utilities/StatusStars.class.php");

/**
 * 
 * 
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createcourse.act.php,v 1.2 2006/08/02 19:59:36 jwlee100 Exp $
 */
class createcourseAction
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId("edu.middlebury.coursemanagement")
		);
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a SlideShow in this <em>Exhibition</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Create a canonical course.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$actionRows =& $this->getActionRows();
		$cacheName = "createCanonicalCourseWizard";
		$this->runWizard ( $cacheName, $actionRows );
	}
		
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	
	function &createWizard () {
		$harmoni =& Harmoni::instance();
		$courseManager =& Services::getService("CourseManagement");
		$canonicalCourseIterator =& $courseManager->getCanonicalCourses();
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Please enter the information about a canonical course:"));
		
		ob_start();
		$self = $harmoni->request->quickURL();
		print ("<p align='center'><b><font size=+1>What would you like to search for?")." 
				</font></b></p>";
		print "<form action='$self' method='post'>
			<div>";
			
		print "<br>Course Type: <select name='search_option'>";
		print "<option value='canonicalcourse' selected='selected'>Canonical course</option>";
		print "<option value='courseoffering'>Course offering</option>";
		print "<option value='coursesection'>Course section</option>";
		print "\n\t</select>";
		
		print "\n\t<p><input type='submit' value='"._("Submit!")."' />";
		print "</form>";
				
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		$searchOption = RequestContext::value('search_option');
		
		if ($searchOption == 'canonicalcourse') {
			$titleProp =& $step->addComponent("coursetitle", new WTextField());
			$titleProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$titleProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
			// Create the properties.		
			$numberProp =& $step->addComponent("coursenumber", new WTextField());
			$numberProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$numberProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		
			$descriptionProp =& $step->addComponent("coursedescription", WTextArea::withRowsAndColumns(10,30));
		
			// Create the type chooser.
			$select =& new WSelectList();
			$typename = "can";	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_'.$typename."_type");
			$query->addColumn('id');
			$query->addColumn('keyword');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['keyword']);
			}
			$typeProp =& $step->addComponent("coursetype", $select);
			/*
			$typeProp =& $step->addComponent("type", new WTextField());
			$typeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$typeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
			*/
			$select =& new WSelectList();
			$typename = "can_stat";	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_'.$typename."_type");
			$query->addColumn('id');
			$query->addColumn('keyword');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['keyword']);
			}
			$typeProp =& $step->addComponent("coursestatustype", $select);
			/*
			$statusTypeProp =& $step->addComponent("statusType", new WTextField());
			$statusTypeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$statusTypeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
			*/
			$creditsProp =& $step->addComponent("coursecredits", new WTextField());
			$creditsProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$creditsProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
					
			// Create the step text
			ob_start();
			print "\n<font size=+2><h2>"._("Canonical Course")."</h2></font>";
			print "\n<h2>"._("Title")."</h2>";
			print "\n"._("The title of this <em>canonical course</em>: ");
			print "\n<br />[[coursetitle]]";
			print "\n<h2>"._("Number")."</h2>";
			print "\n"._("The number of this <em>canonical course</em>: ");
			print "\n<br />[[coursenumber]]";
			print "\n<h2>"._("Description")."</h2>";
			print "\n"._("The number of this <em>canonical course</em>: ");
			print "\n<br />[[coursedescription]]";
			print "\n<h2>"._("Type")."</h2>";
			print "\n"._("The type of this <em>canonical course</em>: ");
			print "\n<br />[[coursetype]]";
			print "\n<h2>"._("Status type")."</h2>";
			print "\n"._("The status type of this <em>canonical course</em>: ");
			print "\n<br />[[coursestatustype]]";
			print "\n<h2>"._("Credits")."</h2>";
			print "\n"._("The status type of this <em>canonical course</em>: ");
			print "\n<br />[[coursecredits]]";
			print "\n<div style='width: 400px'> &nbsp; </div>";
			$step->setContent(ob_get_contents());
			ob_end_clean();
		} else if ($searchOption == 'courseoffering') {
			$select =& new WSelectList();
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_can');
			$query->addColumn('id');
			$query->addColumn('title');
			$query->addColumn('number');
			$query->addOrderBy('number');
			$res=& $dbHandler->query($query);
			//$select->addOption("1","There");
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['number']." ".$row['title']);
			}
			//$select->addOption("2","Here");
			$canonicalCourse =& $step->addComponent("courseid", $select);
			
			$descriptionProp =& $step->addComponent("offeringdescription", WTextArea::withRowsAndColumns(10,30));
		
			$select =& new WSelectList();
	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_term');
			$query->addColumn('id');
			$query->addColumn('name');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['name']);
			}
			$termProp =& $step->addComponent("offeringterm", $select);
			/*$termProp =& $step->addComponent("term", new WTextField());
			$termProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$termProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));*/
			
		
			$select =& new WSelectList();
			$typename = "offer";	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_'.$typename."_type");
			$query->addColumn('id');
			$query->addColumn('keyword');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['keyword']);
			}
			$typeProp =& $step->addComponent("offeringtype", $select);
			/*$typeProp =& $step->addComponent("type", new WTextField());
			$typeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$typeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));*/
			
			$select =& new WSelectList();
			$typename = "offer_stat";	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_'.$typename."_type");
			$query->addColumn('id');
			$query->addColumn('keyword');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['keyword']);
			}
			$statusTypeProp =& $step->addComponent("offeringstatustype", $select);
			/*
			$statusTypeProp =& $step->addComponent("statusType", new WTextField());
			$statusTypeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$statusTypeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
			*/
	
			$select =& new WSelectList();
			$typename = "grade";	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('gr_'.$typename."_type");
			$query->addColumn('id');
			$query->addColumn('keyword');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['keyword']);
			}
			$courseGradeProp =& $step->addComponent("offeringcoursegrade", $select);
			/*
			$courseGradeProp =& $step->addComponent("courseGrade", new WTextField());
			$courseGradeProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$courseGradeProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
			*/
			// Create the step text
			ob_start();
			print "\n<font size=+2><h2>"._("Course Offering")."</h2></font>";
			//print "\n<h2>"._("Title")."</h2>";
			//print "\n"._("The title of this <em>course offering</em>: ");
			//print "\n<br />[[title]]";
			//print "\n<h2>"._("Number")."</h2>";
			//print "\n"._("The number of this <em>course offering</em>: ");
			//print "\n<br />[[number]]";
			print "\n<h2>"._("Canonical Course")."</h2>";
			print "\n"._("The course for which you want to make an <em>offering</em>: ");
			print "\n<br />[[courseid]]";
			//print "\n<h2>"._("Description")."</h2>";
			//print "\n"._("The description of this <em>course offering</em>: ");
			//print "\n<br />[[description]]";
			print "\n<h2>"._("Term")."</h2>";
			print "\n"._("The term of this <em>course offering</em>: ");
			print "\n<br />[[offeringterm]]";
			print "\n<h2>"._("Type")."</h2>";
			print "\n"._("The type of this <em>course offering</em>: ");
			print "\n<br />[[offeringtype]]";
			print "\n<h2>"._("Status type")."</h2>";
			print "\n"._("The status type of this <em>course offering</em>: ");
			print "\n<br />[[offeringstatustype]]";
			print "\n<h2>"._("Course Grading Type")."</h2>";
			print "\n"._("The course grading type of this <em>course offering</em>: ");
			print "\n<br />[[offeringcoursegrade]]";
			print "\n<div style='width: 400px'> &nbsp; </div>";
			
			$step->setContent(ob_get_contents());
			ob_end_clean();
		} else if ($searchOption == 'coursesection') {
			$select =& new WSelectList();
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_offer');
			$query->addColumn('id');
			$query->addColumn('title');
			$query->addColumn('number');
			$query->addOrderBy('number');
			$res=& $dbHandler->query($query);
			//$select->addOption("1","There");
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['number']." ".$row['title']);
			}
			//$select->addOption("2","Here");
			$canonicalCourse =& $step->addComponent("offeringid", $select);
				
			$descriptionProp =& $step->addComponent("sectiondescription", WTextArea::withRowsAndColumns(10,30));
			
			$select =& new WSelectList();
		
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_term');
			$query->addColumn('id');
			$query->addColumn('name');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['name']);
			}
			$termProp =& $step->addComponent("sectionterm", $select);
			
			
			$select =& new WSelectList();
			$typename = "section";	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_'.$typename."_type");
			$query->addColumn('id');
			$query->addColumn('keyword');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['keyword']);
			}
			$typeProp =& $step->addComponent("sectiontype", $select);
		
			
			
			$select =& new WSelectList();
			$typename = "section_stat";	
			$dbHandler =& Services::getService("DBHandler");
			$query=& new SelectQuery;
			$query->addTable('cm_'.$typename."_type");
			$query->addColumn('id');
			$query->addColumn('keyword');
			$res=& $dbHandler->query($query);
			while($res->hasMoreRows()){
				$row = $res->getCurrentRow();
				$res->advanceRow();
				$select->addOption($row['id'],$row['keyword']);
			}
			$statusTypeProp =& $step->addComponent("sectionstatustype", $select);
		
			// Text box for location
			$locationProp =& $step->addComponent("sectionlocation", new WTextField());
			$locationProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
			$locationProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
				
			// Create the step text
			ob_start();
			print "\n<font size=+2><h2>"._("Course Section")."</h2></font>";
			//print "\n<h2>"._("Title")."</h2>";
			//print "\n"._("The title of this <em>course section</em>: ");
			//print "\n<br />[[title]]";
			//print "\n<h2>"._("Number")."</h2>";
			//print "\n"._("The number of this <em>course section</em>: ");
			//print "\n<br />[[number]]";
			print "\n<h2>"._("Course Section")."</h2>";
			print "\n"._("The course for which you want to make a <em>section</em>: ");
			print "\n<br />[[offeringid]]";
			//print "\n<h2>"._("Description")."</h2>";
			//print "\n"._("The description of this <em>course section</em>: ");
			//print "\n<br />[[description]]";
			print "\n<h2>"._("Term")."</h2>";
			print "\n"._("The term of this <em>course section</em>: ");
			print "\n<br />[[sectionterm]]";
			print "\n<h2>"._("Section Type")."</h2>";
			print "\n"._("The type of this <em>course section</em>: ");
			print "\n<br />[[sectiontype]]";
			print "\n<h2>"._("Status type")."</h2>";
			print "\n"._("The status type of this <em>course sectiong</em>: ");
			print "\n<br />[[sectionstatustype]]";
			print "\n<h2>"._("Location")."</h2>";
			print "\n"._("The location of this <em>course section</em>: ");
			print "\n<br />[[sectionlocation]]";
			print "\n<div style='width: 400px'> &nbsp; </div>";
					
			$step->setContent(ob_get_contents());
			ob_end_clean();
		}
		
		return $wizard;
	}
		
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
		
		// Make sure we have a valid Repository
		$courseManager =& Services::getService("CourseManagement");
		$idManager =& Services::getService("Id");
		$courseManagementId =& $idManager->getId("edu.middlebury.coursemanagement");

		
		// First, verify that we chose a parent that we can add children to.
		$authZ =& Services::getService("AuthZ");
		if ($authZ->isUserAuthorized(
						$idManager->getId("edu.middlebury.authorization.add_children"), 
						$courseManagementId))
		{
			$values = $wizard->getAllValues();
			printpre($values);  
		
			$searchoption = RequestContext::value('search_option');
			if ($searchoption == 'canonicalcourse') {
				$values = $wizard->getAllValues();
				printpre($values);
			
				$courseType =& $courseManager->_indexToType($values['namedescstep']['type'],'can');
				$statusType =& $courseManager->_indexToType($values['namedescstep']['statusType'],'can_stat');
			
				/*$courseType =& new Type($values['namedescstep']['title'], $values['namedescstep']['number'], 
			    						  $values['namedescstep']['type']);
				$statusType =& new Type($values['namedescstep']['title'], $values['namedescstep']['number'], 
										$values['namedescstep']['statusType']);*/
				$canonicalCourseA =& $courseManager->createCanonicalCourse($values['namedescstep']['title'], 
																		   $values['namedescstep']['number'], 	
																		   $values['namedescstep']['description'], 
																		   $courseType, $statusType, 
																		   $values['namedescstep']['credits']);
			} else if ($searchoption == 'courseoffering') {
				$termId =& $idManager->getId($values['namedescstep']['term']); 
          	
				$courseType =& $courseManager->_indexToType($values['namedescstep']['type'],'offer');
				$statusType =& $courseManager->_indexToType($values['namedescstep']['statusType'],'offer_stat');
				$courseGradeType =& $courseManager->_indexToType($values['namedescstep']['courseGrade'],'grade');
			
				$id =& $idManager->getId($values['namedescstep']['courseid']);   
				$canonicalCourse =& $courseManager->getCanonicalCourse($id);														   
																		   
				$courseOffering =& $canonicalCourse->createCourseOffering($canonicalCourse->getTitle(), 
		    															  $canonicalCourse->getNumber(),
																	      $canonicalCourse->getDescription(),	
																	      //$values['namedescstep']['description'], 
																	      $termId, $courseType, $statusType, 
																	      $courseGradeType);
			} else {
				$termId =& $idManager->getId($values['namedescstep']['term']); 
          	
          		$sectionType =& $courseManager->_indexToType($values['namedescstep']['type'],'section');
				$statusType =& $courseManager->_indexToType($values['namedescstep']['statusType'],'section_stat');
				$courseGradeType =& $courseManager->_indexToType($values['namedescstep']['courseGrade'],'grade');
				$location =& $values['namedescstep']['location'];
			
				$id =& $idManager->getId($values['namedescstep']['courseid']);   
				$courseOffering =& $courseManager->getCanonicalCourse($id);														   
																	   
				$courseOffering =& $canonicalCourse->createCourseOffering($canonicalCourse->getTitle(), 
																		  $canonicalCourse->getNumber(),
																		  $canonicalCourse->getDescription(),	
																		  $sectionType, $statusType, 
																		  $location);	
			}
			
			RequestContext::sendTo($this->getReturnUrl());
			exit();
			return TRUE;
		}
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
			return FALSE;
		}
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL("admin", "main");
		return $url->write();
	}
}

?>