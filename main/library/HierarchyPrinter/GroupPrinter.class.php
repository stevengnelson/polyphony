<?php

/**
 * This class will print an expandable view of Groups.
 * 
 * @package polyphony.groupPrinter
 * @version $Id: GroupPrinter.class.php,v 1.4 2004/11/17 21:39:40 adamfranco Exp $
 * @date 11/11/04
 * @copyright 2004 Middlebury College
 */

class GroupPrinter {
	
	/**
	 * Print a group and the expanded children (other groups or members)
	 * 
	 * @param object $group
	 * @param string $printGroupFunction Prints current group in group format.
	 * @param string $printMemberFunction Prints current group in member format.
	 * @return void
	 * @access public
	 * @date 11/8/04
	 */
	function printGroup (& $group, & $harmoni,
								$startingPathInfoKey,
								$printGroupFunction,
								$printMemberFunction ) 
	{
		// Get a string of our groupIds
		$groupId =& $group->getId();
		
		// Build a variable to pass around our get terms when expanding
		if (count($_GET)) {
				$get = "?";
				foreach ($_GET as $key => $val)
					$get .= "&".urlencode($key)."=".urlencode($val);
		}
		
		// Break the path info into parts for the enviroment and parts that
		// designate which groups to expand.
		$environmentInfo = array();
		$expandedGroups = array();
		
		for ($i=0; $i<count($harmoni->pathInfoParts); $i++) {
			// If the index equals or is after our starting key
			// it designates an expanded groupId.
			if ($i >= $startingPathInfoKey)
				$expandedGroups[] = $harmoni->pathInfoParts[$i];
			else	
				$environmentInfo[] = $harmoni->pathInfoParts[$i];
		}
		
		print "\n\n<table>\n\t<tr><td valign='top'>";
		
		// Print The Group
		// First check to see whether or not it has any children
		$childGroups =& $group->getGroups(false);
		$childMembers =& $group->getMembers(false);
		if ($childGroups->hasNext() || $childMembers->hasNext()) {
		?>

<div style='
	border: 1px solid #000; 
	width: 15px; 
	height: 15px;
	text-align: center;
	text-decoration: none;
	font-weight: bold;
'>
		<?		
			// The child groups are already expanded for this group. 
			// Show option to collapse the list.		
			if (in_array($groupId->getIdString(), $expandedGroups)) {
				$groupsToRemove = array($groupId->getIdString());
				$newPathInfo = array_merge($environmentInfo, array_diff($expandedGroups,
																		$groupsToRemove)); 
				print "<a style='text-decoration: none;' href='";
				print MYURL."/".implode("/", $newPathInfo)."/";
				print $get."'>-</a>";
			
			// The group is not already expanded.  Show option to expand.	
			} else { 
				$newPathInfo = array_merge($environmentInfo, $expandedGroups); 
				print "<a style='text-decoration: none;' href='";
				print MYURL."/".implode("/", $newPathInfo)."/".$groupId->getIdString()."/";
				print $get."'>+</a>";
			}
			print "\n\t\t</div>";
			
		// The group has no children.  Do not show options to expand/collapse.
		} else {
			print "\n\t\t<div style='width: 15px;'>&nbsp;</div>";
		}
		
		
		print "\n\t</td><td valign='top'>\n\t\t";
		$printGroupFunction($group);
		print "\n\t</td></tr>\n</table>";
		
		
		// If the group was expanded, we need to recursively print its children.
		
		if (in_array($groupId->getIdString(), $expandedGroups)) {
			?>

<div style='
	margin-left: 13px; 
	margin-right: 0px; 
	margin-top:0px; 
	padding-left: 10px;
	border-left: 1px solid #000;
'>
		<?
			while ($childGroups->hasNext()) {
				$childGroup =& $childGroups->next();
				GroupPrinter::printGroup( $childGroup,
											$harmoni,
											$startingPathInfoKey,
											$printGroupFunction,
											$printMemberFunction);
			}
			
			// And finally print all the members for the group
			
			while ($childMembers->hasNext()) {
				$childMember =& $childMembers->next();
				print "\n\n<table>\n\t<tr><td valign='top'>";
				print "\n\t\t<div style='width: 15px;'>&nbsp;</div>";
				print "\n\t</td><td valign='top'>\n\t\t";
				$printMemberFunction($childMember);
				print "\n\t</td></tr>\n</table>";
			}			
			print "\n</div>";

		}
	}
	
	
	
}

?>