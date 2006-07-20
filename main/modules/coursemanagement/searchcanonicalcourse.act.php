<?php

/**
 * @package polyphony.modules.coursemanagement
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: searchcanonicalcourse.act.php,v 1.14 2006/07/20 20:32:16 jwlee100 Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");


class searchcanonicalcourseAction 
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
		return dgettext("polyphony", "Search Canonical Courses by Term");
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
		print ("<p align='center'><b><font size=+1>Search for a canonical course by the following criteria").": 
				</font></b></p>";
		print "<form action='$self' method='post'>
			<div>
			Title: <input type='text' name='search_title'>
			<br>Number: <input type='text' name='search_number'>";
			
		print "<br>Course Type: <select name='search_type'>";
		print "<option value='' selected='selected'>Choose a course type</option>";
		
		$typename = "can";	
		$dbHandler =& Services::getService("DBHandler");
		$query =& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$query->addColumn('keyword');
		$res =& $dbHandler->query($query);
		while ($res->hasMoreRows()) {
			$row = $res->getCurrentRow();
			$res->advanceRow();
			print "<option value='".$row['keyword']."'>".$row['keyword']."</option>";
		}
		
		print "\n\t</select>";
		
		print "<br>Course Status Type<select name='search_status'>";
		print "<option value='' selected='selected'>Choose a course status type</option>";
		
		$typename = "can_stat";	
		$dbHandler =& Services::getService("DBHandler");
		$query =& new SelectQuery;
		$query->addTable('cm_'.$typename."_type");
		$query->addColumn('id');
		$query->addColumn('keyword');
		$res =& $dbHandler->query($query);
		while ($res->hasMoreRows()) {
			$row = $res->getCurrentRow();
			$res->advanceRow();
			print "<option value='".$row['keyword']."'>".$row['keyword']."</option>";
		}
		
		print "\n\t</select>";
		
		print "\n\t<p><input type='submit' value='"._("Search!")."' />";
		//print "\n\t<a href='".$harmoni->request->quickURL()."'>";
		
		print "\n</p>\n</div></form>";
		print "\n  <p align='center'><i>Search may take a few minutes.  Please be patient.</i></p>";
		
		print "<p><hr><p><a href='".$harmoni->request->quickURL("coursemanagement","createcanonicalcourse")."'>";
		print _("Click here to create a canonical course.");
		print "<p><a href='".$harmoni->request->quickURL("coursemanagement","browseanonicalcourse")."'>";
		print _("Click here to browse through all existing canonical courses.");
		
		$actionRows->add(new Block(ob_get_contents(), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		ob_end_clean();
		
		$searchTitle = RequestContext::value('search_title');
		$searchNumber = RequestContext::value('search_number');
		$searchType = RequestContext::value('search_type');
		$searchStatus = RequestContext::value('search_status');
		
		if ($searchTitle != "" || $searchNumber != "" || $searchType != "" || $searchStatus != "") {
		  	$pageRows->add(new Heading("Canonical course search results", STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		  	ob_start();
		  	
			print "\n<table border=1>";
			print "\n\t<tr>";
			print "\n\t<td>";
			print "<b>Title</b>";
			print "\n\t<td>";
			print "<b>Number</b>";
			print "\n\t<td>";
			print "<b>Description</b>";
			print "\n\t<td>";
			print "<b>Course Type</b>";
			print "\n\t<td>";
			print "<b>Course Status</b>";
			print "\n\t<td>";
			print "<b>Credits</b>";
			print "\n\t</tr>";
			$canonicalCourseIterator = $cmm->getCanonicalCourses();
			while ($canonicalCourseIterator->hasNext()) {
				$canonicalCourse = $canonicalCourseIterator->next();
				$title = $canonicalCourse->getTitle();
				$number = $canonicalCourse->getNumber();
				$cType = $canonicalCourse->getCourseType();
				$courseType = $cType->getKeyword();
				$courseStatusType = $canonicalCourse->getStatus();
				$courseStatus = $courseStatusType->getKeyword();
				if (($searchTitle == $title || $searchTitle == "") && ($searchNumber == "" || $searchNumber == $number) 
					&& ($searchType == "" || $searchType == $courseType) && 
					($searchStatus == "" || $searchStatus == $courseStatus))		
				{
					$description = $canonicalCourse->getDescription();
					$credits = $canonicalCourse->getCredits();
				
					print "<tr>";
					print "<td>";
					print $title;
					print "<td>";
					print $number;
					print "<td>";
					print $description;
					print "<td>";
					print $courseType;
					print "<td>";
					print $courseStatus;
					print "<td>";
					print $credits;
					print "</tr>";
				}
			}
			
			$groupLayout =& new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			
			$pageRows->add($groupLayout, "100%", null, LEFT, CENTER);	
			$actionRows->add($pageRows, "100%", null, LEFT, CENTER);
		}	
	}
}