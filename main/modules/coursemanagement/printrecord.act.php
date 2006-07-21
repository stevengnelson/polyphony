<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: printrecord.act.php,v 1.1 2006/07/21 15:56:56 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class searchcourseofferingAction 
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
		// Check for authorization
 		$authZManager =& Services::getService("AuthZ");
 		$idManager =& Services::getService("IdManager");
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.view"),
 					$idManager->getId("edu.middlebury.coursemanagement")))
 		{
			return TRUE;
 		} else {
 			
 			return FALSE;
		}
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Search Course Offerings");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
	
		$actionRows =& $this->getActionRows();
		$pageRows =& new Container(new YLayout(), OTHER, 1);
		$harmoni =& Harmoni::instance();
		
		$cmm =& Services::getService("CourseManagement");
		
		ob_start();
		$self = $harmoni->request->quickURL();
		
		ob_end_clean();
		
		$searchTitle = RequestContext::value('search_title');
		$searchNumber = RequestContext::value('search_number');
		$searchType = RequestContext::value('search_type');
		$searchStatus = RequestContext::value('search_status');
		$searchGrade = RequestContext::value('search_grade');
		$searchTerm = RequestContext::value('search_term');
		
		if ($searchTitle != "" || $searchNumber != "" || $searchType != "" || $searchStatus != "" ||
			$searchGrade != "" || $searchTerm != "") 
		{
			$pageRows->add(new Heading("Course offering search results", STANDARD_BLOCK), "100%", null, LEFT, CENTER);
			
			ob_start();
			
			print "<p>Click on the title of the course to see the course roster.</p>";
			
			print "<p>";
			print "\n<table border=1>";
			print "\n\t<tr align=center>";
			print "\n\t<td>";
			print "<b>Title</b>";
			print "\n\t<td>";
			print "<b>Number</b>";
			print "\n\t<td>";
			print "<b>Description</b>";
			print "\n\t<td>";
			print "<b>Course Offering Type></b>";
			print "\n\t<td>";
			print "<b>Course Offering Status</b>";
			print "\n\t<td>";
			print "<b>Course Grade Type</b>";
			print "\n\t<td>";
			print "<b>Term</b>";
			print "\n\t</tr>";
			$canonicalCourseIterator = $cmm->getCanonicalCourses();
			while ($canonicalCourseIterator->hasNext()) {
				$canonicalCourse = $canonicalCourseIterator->next();
				$courseOfferingIterator = $canonicalCourse->getCourseOfferings();
				while ($courseOfferingIterator->hasNext()) {
					$courseOffering =& $courseOfferingIterator->next();
					$title = $courseOffering->getTitle();
	  				$number = $courseOffering->getNumber();
	  				$oType = $courseOffering->getOfferingType();
	  				$offeringType = $oType->getKeyword();
	  				$offeringStatusType =& $courseOffering->getStatus();
	  				$offeringStatus = $offeringStatusType->getKeyword();
	  				$offeringGradeType =& $courseOffering->getCourseGradeType();
	  				$offeringGrade = $offeringGradeType->getKeyword();
	  				$offeringTerm =& $courseOffering->getTerm();
	  				$term = $offeringTerm->getDisplayName();
					if (($searchTitle == $title || $searchTitle == "") && 
						($searchNumber == "" || $searchNumber == $number) &&
						($searchType == $offeringType || $searchType == "") && 
						($searchStatus == "" || $searchStatus == $offeringStatus) &&
						($searchGrade == "" || $searchGrade == $offeringGrade) &&
						($searchTerm == "" || $searchTerm == $term)) 		
					{
						$description = $canonicalCourse->getDescription();
						$credits = $canonicalCourse->getCredits();
						
						$courseId =& $courseOffering->getId();
						$courseIdString = $courseId->getIdString();
		
						$harmoni->history->markReturnURL("polyphony/coursemanagement/printroster");
						$link = $harmoni->request->quickURL("coursemanagement", "printroster", 
															array("courseId"=>$courseIdString));
						
						print "<tr>";
						print "<td>";
						print "<a href='$link'>".$title."</a>";
						print "<td>";
						print $number;
						print "<td>";
						print $description;
						print "<td>";
						print $offeringType;
						print "<td>";
						print $offeringStatus;
						print "<td>";
						print $offeringGrade;
						print "<td>";
						print $term;
						print "</tr>";
					}
				}
			}
			
			$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			
			$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
			$actionRows->add($pageRows, "100%", null, LEFT, CENTER);
		}	
	}
}