<?php
/**
 * @since 6/5/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Display a success message and a link to re-send the confirmation email.
 * 
 * @since 6/5/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class visitor_reg_successAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/4/08
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 6/4/08
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough('returnModule');
		$harmoni->request->passthrough('returnAction');
		$harmoni->request->passthrough('returnKey');
		$harmoni->request->passthrough('returnValue');
		
		$authNMethodMgr = Services::getService("AuthNMethodManager");
		$visitorAuthType = new Type ("Authentication", "edu.middlebury.harmoni",
			"Visitors");
		
		$centerPane =$this->getActionRows();
		
		$centerPane->add(new Heading(dgettext("polyphony", "Registration Success"), 1));
		
		ob_start();
		print dgettext("polyphony", "Visitor registration was successful.")." <br/>";
		print dgettext("polyphony", "A confirmation email has been sent to you.")." <br/>";
		print dgettext("polyphony", "You must click on the confirmation link in the email before you will be able to log in.");
		
		if (RequestContext::value('email')) {
			$authMethod = $authNMethodMgr->getAuthNMethodForType($visitorAuthType);
			$tokens = $authMethod->createTokensObject();
			$tokens->initializeForIdentifier(RequestContext::value('email'));
			
			// Check for previous registration
			if ($authMethod->tokensExist($tokens)
				&& !$authMethod->isEmailConfirmed($tokens)) 
			{
				print "\n\n<p>";
				print dgettext("polyphony", "Re-send confirmation email?");
				$harmoni = Harmoni::instance();
				print " <a href='".$harmoni->request->quickURL('user', 'send_confirmation', array('email' => RequestContext::value('email')))."'><button>";
				print dgettext("polyphony", "Send");
				print "</button></a>";
				print "</p>";
			}
		}
		
		$returnUrl = $this->getReturnUrl();
		if ($returnUrl) {
			print "\n<p><a href='$returnUrl'>".dgettext("polphony", "Return to original location.")."</a></p>";
		}
		
		$centerPane->add(new Block(ob_get_clean(), STANDARD_BLOCK));
	}
	
	/**
	 * Answer a return Url if we have return information, null otherwise
	 * 
	 * @return string
	 * @access protected
	 * @since 6/6/08
	 */
	public function getReturnUrl () {
		$harmoni = Harmoni::instance();
		
		$harmoni->request->forget('returnModule');
		$harmoni->request->forget('returnAction');
		$harmoni->request->forget('returnKey');
		$harmoni->request->forget('returnValue');
		
		if (RequestContext::value('returnModule') && RequestContext::value('returnAction') 
				&& RequestContext::value('returnKey')) 
			return $harmoni->request->quickURL(RequestContext::value('returnModule'), RequestContext::value('returnAction'), array(RequestContext::value('returnKey') => RequestContext::value('returnValue')));
		
		if (RequestContext::value('returnModule') && RequestContext::value('returnAction')) 
			return $harmoni->request->quickURL(RequestContext::value('returnModule'), RequestContext::value('returnAction'));
		
		return null;
	}
	
}

?>