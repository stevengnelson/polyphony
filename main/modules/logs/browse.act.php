<?php

/**
 * @package polyphony.logging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse.act.php,v 1.21 2008/02/07 20:09:19 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/ResultPrinter/TableIteratorResultPrinter.class.php");
require_once(HARMONI."GUIManager/Components/Blank.class.php");

/**
 * This action provides browsing access for logs.
 *
 * @since 11/10/04 
 * 
 * @package polyphony.agents
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse.act.php,v 1.21 2008/02/07 20:09:19 adamfranco Exp $
 */
class browseAction 
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
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		if ($authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.modify"),
 					$idManager->getId("edu.middlebury.authorization.root")))
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
		return dgettext("polyphony", "Browse Logs");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$defaultTextDomain = textdomain("polyphony");
		
		$actionRows =$this->getActionRows();
		$harmoni = Harmoni::instance();
		
		$harmoni->request->startNamespace("polyphony-logs");
		$harmoni->request->passthrough('log', 'priority',
			'startYear', 'startMonth', 'startDay', 'startHour',
			'endYear', 'endMonth', 'endDay', 'endHour',
			'agent_id', 'node_id', 'category');

		$agentManager = Services::getService("Agent");
		$idManager = Services::getService("Id");
		$hierarchyManager = Services::getService("Hierarchy");
		
		
		
		/*********************************************************
		 * the log search form
		 *********************************************************/
		// Log header
		$actionRows->add(new Heading(_("Logs"), 2), "100%", null, LEFT, CENTER);
		
		
		$loggingManager = Services::getService("Logging");
		
		$log =$loggingManager->getLogForWriting("test_log");
		$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
						"A format in which the acting Agent[s] and the target nodes affected are specified.");
		$priorityType = new Type("logging", "edu.middlebury", "normal",
						"An action which involves reading.");
		
		
		// Links to other logs
		$logNames =$loggingManager->getLogNamesForReading();
		ob_start();
		
		if (RequestContext::value("log"))
			$currentLogName = RequestContext::value("log");
		
		while ($logNames->hasNext()) {
			$logName = $logNames->next();
			
			if (!isset($currentLogName))
				$currentLogName = $logName;
			
			if ($logName != $currentLogName) {
				print "\n<a href='";
				print $harmoni->request->quickURL("logs", "browse",
						array(	"log" => $logName));
				print "'>".$logName."</a>";
			} else
				print $logName;
			
			if ($logNames->hasNext())
				print " | ";
		
		}
		
		print " 
	<table border='0'>
		<tr>
			<th valign='top'>"._("Date Range: ")."</th>
			<td>

";
		
		$startDate =$this->getStartDate();
		$endDate =$this->getEndDate();
		$this->printDateRangeForm($startDate, $endDate);

		print "

			</td>
		</tr>
";
		
		print "
		<tr>
			<th>"._("Filters:")."</th>
			<td>
";		
	
		if (RequestContext::value('agent_id')) {
			print "\n\t\t\t";
			$id =$idManager->getId(RequestContext::value('agent_id'));
			$url = $harmoni->request->mkURL("logs", "browse");
			$url->setValue('agent_id', null);
			$agent =$agentManager->getAgent($id);
			print $agent->getDisplayName();
			print "\n\t\t\t\t<input type='button' onclick='window.location=\"";
			print str_replace('&amp;', '&', $url->write());
			print "\"' value='X'/>";
			
		}
		
		if (RequestContext::value('agent_id') && RequestContext::value('node_id'))
			print "\n\t\t\t &nbsp; &nbsp; &nbsp; &nbsp; ";
		
		if (RequestContext::value('node_id')) {
			print "\n\t\t\t";
			$id =$idManager->getId(RequestContext::value('node_id'));
			$url = $harmoni->request->mkURL("logs", "browse");
			$url->setValue('node_id', null);
			try {
				$node = $hierarchyManager->getNode($id);
				if ($node->getDisplayName())
					print $node->getDisplayName();
				else
					print _("Id: ").$nodeId->getIdString();
			} catch (UnknownIdException $e) {
				print $id->getIdString();
			} catch (UnimplementedException $e) {
				print $id->getIdString();
			}
			print "\n\t\t\t\t<input type='button' onclick='window.location=\"";
			print str_replace('&amp;', '&', $url->write());
			print "\"' value='X'/>";
		}
		
		if ((RequestContext::value('agent_id') || RequestContext::value('node_id')) && RequestContext::value('category'))
			print "\n\t\t\t &nbsp; &nbsp; &nbsp; &nbsp; ";
		
		if (RequestContext::value('category')) {
			print "\n\t\t\t";
			$url = $harmoni->request->mkURL("logs", "browse");
			$url->setValue('category', null);
			print  urldecode(RequestContext::value('category'));
			print "\n\t\t\t\t<input type='button' onclick='window.location=\"";
			print str_replace('&amp;', '&', $url->write());
			print "\"' value='X'/>";
		}
		
		print "\n<br/><br/>";
		if (isset($currentLogName)) {
			$log = $loggingManager->getLogForReading($currentLogName);
			if (method_exists($log, 'getCategories')) {
				print "\n\t\t\t\t<form action='";
				$url = $harmoni->request->mkURL('logs', 'browse'); 
				$url->setValue('category', null);
				print $url->write();
				print "' method='post'>";
				print "\n\t\t\t\t\t<input type='submit' value='"._('Set Category Filter:')."'/>";
				print "\n\t\t\t\t\t<select name='".RequestContext::name('category')."'>";
				foreach ($log->getCategories() as $category) {
					print "\n\t\t\t\t\t\t<option value='$category'>$category</option>";
				}
				print "\n\t\t\t\t\t</select>";
				print "\n\t\t\t\t</form>";
			}
		}
		
		print "\n\t\t\t\t<form action='";
		$url = $harmoni->request->mkURL('logs', 'browse'); 
		$url->setValue('node_id', null);
		print $url->write();
		print "' method='post'>";
		print "\n\t\t\t\t\t<input type='submit' value='"._('Set Node Id Filter:')."'/>";
		print "\n\t\t\t\t\t<input name='".RequestContext::name('node_id')."' type='text'/>";
		print "\n\t\t\t\t</form>";
		
		print "\n\t\t\t\t<form action='";
		$url = $harmoni->request->mkURL('logs', 'browse'); 
		$url->setValue('agent_id', null);
		print $url->write();
		print "' method='post'>";
		print "\n\t\t\t\t\t<input type='submit' value='"._('Set Agent Id Filter:')."'/>";
		print "\n\t\t\t\t\t<input name='".RequestContext::name('agent_id')."' type='text'/>";
		print "\n\t\t\t\t</form>";
		
		print "\n\t\t\t</td></tr>";
		
		print "\n\t</table>";
		
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		
		// --- The Current log ---
		if (isset($currentLogName)) {
			$log =$loggingManager->getLogForReading($currentLogName);
			$actionRows->add(new Heading($log->getDisplayName(), 3), "100%", null, LEFT, CENTER);
			ob_start();
			
			
			// Links to other priorities
			print "<strong>"._("Priority: ")."</strong>";
			if (RequestContext::value("priority")) {
				$currentPriorityType = HarmoniType::fromString(
											RequestContext::value("priority"));
				$entries =$log->getEntries($formatType, $currentPriorityType);
				if (!$entries->hasNext()) {
					unset($currentPriorityType, $entries);
				}
			}
				
			$priorityTypes =$loggingManager->getPriorityTypes();
			$priorityLinks = array();
			while ($priorityTypes->hasNext()) {
				$priorityType = $priorityTypes->next();
				
				// Only print priority types with entries
				$entries = $log->getEntries($formatType, $priorityType);
				if ($entries->hasNext()) {
					
					if (!isset($currentPriorityType))
						$currentPriorityType =$priorityType;
					
					if (!$priorityType->isEqual($currentPriorityType)) {
						$string = "\n<a href='";
						$string .= $harmoni->request->quickURL("logs", "browse",
								array(	"log" => RequestContext::value("log"),
										"priority" => $priorityType->asString()));
						$string .= "'>".$priorityType->getKeyword()."</a>";
					} else
						$string = $priorityType->getKeyword();
					
					$priorityLinks[] = $string;
				}
				unset($entries);
			}
			
			print implode(" | ", $priorityLinks);
			
			if (isset($currentPriorityType)) {
				// Entries
				print<<<END
		
		<script type='text/javascript'>
			/* <![CDATA[ */
		
			function showTrace(buttonElement) {
				newWindow = window.open("", "traceWindow", 'toolbar=no,width=600,height=500,resizable=yes,scrollbars=yes,status=no')
				// the next sibling is text, the one after that is our hidden div
				newWindow.document.write(buttonElement.nextSibling.nextSibling.innerHTML)
				newWindow.document.bgColor="lightpink"
				newWindow.document.close() 
			}
		
			/* ]]> */
		</script>
		
END;
				// Do a search if needed
				if (!$startDate->isEqualTo($this->minDate())
					|| !$endDate->isEqualTo(DateAndTime::tomorrow())
					|| RequestContext::value('agent_id')
					|| RequestContext::value('node_id')
					|| RequestContext::value('category'))
				{
					$criteria = array();
					$criteria['start'] =$startDate;
					$criteria['end'] =$endDate;
					if (RequestContext::value('agent_id'))
						$criteria['agent_id'] =$idManager->getId(
													RequestContext::value('agent_id'));
					if (RequestContext::value('node_id'))
						$criteria['node_id'] =$idManager->getId(
													RequestContext::value('node_id'));
					if (RequestContext::value('category'))
						$criteria['category'] = urldecode(RequestContext::value('category'));
					
					$searchType = new Type("logging_search", "edu.middlebury", "Date-Range/Agent/Node");
					$entries =$log->getEntriesBySearch($criteria, $searchType, 
											$formatType, $currentPriorityType);
				} else {
					$entries =$log->getEntries($formatType, $currentPriorityType);
				}
				
				
				$headRow = "
		<tr>
			<th>timestamp</th>
			<th>category</th>
			<th>description</th>
			<th>trace</th>
			<th>agents</th>
			<th>nodes</th>
		</tr>";
				$resultPrinter = new TableIteratorResultPrinter($entries, $headRow,
										20, "printLogRow", 1);
				print $resultPrinter->getTable();
			}
			
			if (isset($currentLogName) && isset($currentPriorityType)) {
				$url = $harmoni->request->quickURL('logs', 'browse_rss',
						array("log" => $currentLogName,
							"priority" => $currentPriorityType->asString()));
				$title = $currentLogName." ".$currentPriorityType->getKeyword()." "._("Logs");
				
				$outputHandler =$harmoni->getOutputHandler();
				$outputHandler->setHead($outputHandler->getHead()
					."\n\t\t<link rel='alternate' type='application/rss+xml'"
					." title='".$title."' href='".$url."'/>");
				
				print "\n\t\t<div style='text-align: right'>";
				print "\n\t\t<a href='".$url."' style='white-space: nowrap;' title='".$title."'>";
				print "\n\t\t\t<img src='".POLYPHONY_PATH."icons/rss_icon02.png' border='0' alt='RSS Icon'/>";
				print "\n\t\t\t"._("Subscribe to the RSS feed of this log");
				print "\n\t\t</a>";
				print "\n\t\t</div>";
			}
			
			$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		}
 		
 		textdomain($defaultTextDomain);
 		$harmoni->request->endNamespace();
	}
	
	/**
	 * Answer the current starting date
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/8/06
	 */
	function getStartDate () {
		if (RequestContext::value("startYear"))
			return DateAndTime::withYearMonthDayHourMinute(
								RequestContext::value("startYear"),
								RequestContext::value("startMonth"),
								RequestContext::value("startDay"),
								RequestContext::value("startHour"),
								0);
		else
			return $this->minDate();
	}
	
	/**
	 * Answer the current end date
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/8/06
	 */
	function getEndDate () {
		if (RequestContext::value("endYear"))
			return DateAndTime::withYearMonthDayHourMinute(
								RequestContext::value("endYear"),
								RequestContext::value("endMonth"),
								RequestContext::value("endDay"),
								RequestContext::value("endHour"),
								0);
		else
			return DateAndTime::tomorrow();
	}
	
	/**
	 * Answer the minumum date to display
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/8/06
	 */
	function minDate () {
		return DateAndTime::withYearDay(2000, 1);
	}
	
	/**
	 * Print the dateRange form
	 * 
	 * @param object DateAndTime $startDate
	 * @param object DateAndTime $endDate
	 * @return void
	 * @access public
	 * @since 3/8/06
	 */
	function printDateRangeForm( $startDate, $endDate ) {
		$min =$this->minDate();
		$max = DateAndTime::tomorrow();
		$harmoni = Harmoni::instance();
		
		print "\n<form action='";
		
		$harmoni->request->forget('startYear');
		$harmoni->request->forget('startMonth');
		$harmoni->request->forget('startDay');
		$harmoni->request->forget('startHour');
		$harmoni->request->forget('endYear');
		$harmoni->request->forget('endMonth');
		$harmoni->request->forget('endDay');
		$harmoni->request->forget('endHour');
		
		print $harmoni->request->quickURL('logs', 'browse');
		
		$harmoni->request->passthrough('log', 'priority',
			'startYear', 'startMonth', 'startDay', 'startHour',
			'endYear', 'endMonth', 'endDay', 'endHour',
			'agent_id', 'node_id', 'category');
		print "' method='post'>";
		
		print "\n\t<select name='".RequestContext::name("startMonth")."'>";
		$month = 1;
		while ($month <= 12) {
			print "\n\t\t<option value='".$month."'";
			print (($month == $startDate->month())?" selected='selected'":"");
			print ">".Month::nameOfMonth($month)."</option>";
			$month++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("startDay")."'>";
		$day = 1;
		while ($day <= 31) {
			print "\n\t\t<option value='".$day."'";
			print (($day == $startDate->dayOfMonth())?" selected='selected'":"");
			print ">".$day."</option>";
			$day++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("startYear")."'>";
		$year = $max->year();
		$minYear = $min->year();
		while ($year >= $minYear) {
			print "\n\t\t<option value='".$year."'";
			print (($year == $startDate->year())?" selected='selected'":"");
			print ">$year</option>";
			$year--;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("startHour")."'>";
		$hour = 0;
		while ($hour <= 23) {
			print "\n\t\t<option value='".$hour."'";
			print (($hour == $startDate->hour())?" selected='selected'":"");
			print ">".sprintf("%02d", $hour).":00</option>";
			$hour++;
		}
		print "\n\t</select>";
		
		print "\n\t<strong> to: </strong>";
		
		print "\n\t<select name='".RequestContext::name("endMonth")."'>";
		$month = 1;
		while ($month <= 12) {
			print "\n\t\t<option value='".$month."'";
			print (($month == $endDate->month())?" selected='selected'":"");
			print ">".Month::nameOfMonth($month)."</option>";
			$month++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("endDay")."'>";
		$day = 1;
		while ($day <= 31) {
			print "\n\t\t<option value='".$day."'";
			print (($day == $endDate->dayOfMonth())?" selected='selected'":"");
			print ">".$day."</option>";
			$day++;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("endYear")."'>";
		$year = $max->year();
		$minYear = $min->year();
		while ($year >= $minYear) {
			print "\n\t\t<option value='".$year."'";
			print (($year == $endDate->year())?" selected='selected'":"");
			print ">$year</option>";
			$year--;
		}
		print "\n\t</select>";
		
		print "\n\t<select name='".RequestContext::name("endHour")."'>";
		$hour = 0;
		while ($hour <= 23) {
			print "\n\t\t<option value='".$hour."'";
			print (($hour == $endDate->hour())?" selected='selected'":"");
			print ">".sprintf("%02d", $hour).":00</option>";
			$hour++;
		}
		print "\n\t</select>";
		
		print "\n\t<input type='submit' value='"._("Set Range")."'/>";
		
		print "\n\t<a href='";
		print $harmoni->request->quickURL('logs', 'browse', array(
				'startYear' => $min->year(),
				'startMonth' => $min->month(),
				'startDay' => $min->dayOfMonth(),
				'startHour' => $min->hour(),
				'endYear' => $max->year(),
				'endMonth' => $max->month(),
				'endDay' => $max->dayOfMonth(),
				'endHour' => $max->hour()
		));
		print "'>";
		print "\n\t\t<input type='button' value='"._("Clear")."' />";
		print "</a>";
		print "\n</form>";
	}
}

/**
 * Print the row for an entry
 * 
 * @param object Entry $entry
 * @return void
 * @access public
 * @since 3/9/06
 */
function printLogRow ( $entry ) {
	$harmoni = Harmoni::instance();
	$agentManager = Services::getService("Agent");
	$idManager = Services::getService("Id");
	$hierarchyManager = Services::getService("Hierarchy");
	
	print "\n\t<tr>";
			
	$timestamp =$entry->getTimestamp();
	$timezone =$timestamp->timeZone();
	$timezoneOffset =$timezone->offset();
	print "\n\t\t<td title='";
	print $timezone->name()." (".$timezoneOffset->hours().":".sprintf("%02d", abs($timezoneOffset->minutes())).")";
	print "' style='white-space: nowrap'>";
	print $timestamp->monthName()." ";
	print $timestamp->dayOfMonth().", ";
	print $timestamp->year()." ";
	print $timestamp->hmsString();
	print "</td>";
	
	$item =$entry->getItem();
	
	print "\n\t\t<td style='white-space: nowrap'>";
	print "\n\t\t\t<a href='";
	print $harmoni->request->quickURL("logs", "browse",
			array(	"category" => urlencode($item->getCategory())));
	print "'>";
	print $item->getCategory();
	print "</a>";
	print "\n\t\t</td>";
	
	print "\n\t\t<td>".$item->getDescription()."</td>";
	
	print "\n\t\t<td>";
	if ($trace = $item->getBacktrace()) {
		print "\n\t\t\t<input type='button' value='"._("Show Trace")."' onclick='showTrace(this)'/>";
		print "\n\t\t\t<div style='display: none'>".$trace."</div>";
	}
	print "</td>";
	
	print "\n\t\t<td style='white-space: nowrap'>";
	$agentIds =$item->getAgentIds(true);
	while ($agentIds->hasNext()) {
		$agentId =$agentIds->next();
		if ($agentManager->isAgent($agentId) || $agentManager->isGroup($agentId)) {
			$agent =$agentManager->getAgent($agentId);
			print "<a href='";
			print $harmoni->request->quickURL("logs", "browse",
					array(	"agent_id" => $agentId->getIdString()));
			print "'>";
			print $agent->getDisplayName();
			print "</a>";
		} else
			print _("Id: ").$agentId->getIdString();
		if ($agentIds->hasNext())
			print ", <br/>";
	}
	print "\n\t\t</td>";
	
	print "\n\t\t<td style='white-space: nowrap'>";
	$nodeIds =$item->getNodeIds(true);
	while ($nodeIds->hasNext()) {
		$nodeId =$nodeIds->next();
		print "<a href='";
		print $harmoni->request->quickURL("logs", "browse",
				array(	"node_id" => $nodeId->getIdString()));
		print "'>";
		if ($hierarchyManager->nodeExists($nodeId)) {
			$node =$hierarchyManager->getNode($nodeId);
			if ($node->getDisplayName())
				print $node->getDisplayName();
			else
				print _("Id: ").$nodeId->getIdString();
		} else {
			print _("Id: ").$nodeId->getIdString();
		}
		print "</a>";
		if ($nodeIds->hasNext())
			print ", <br/>";
	}
	print "\n\t\t</td>";
	
	print "\n\t</tr>";
}