<?php

if ( !defined( 'MEDIAWIKI' ) ) {     exit; }

/**
* Hooks for Bayesian Filter Extension
*/

class BayesianFilterHooks {

	/**
	* Hook function for RollbackComplete
	*
	* @page is the instance of type Editpage and contains the information about the page being edited
	* @user is the instance of User and is the current user who is editing the page
	* @target is the article which is currently being rollbacked
	* @current is the article to which it is rollbacked 
	*/

	static function rollbackComplete(Page $page, User $user, $target, $current) {
	
		$title = $page->getTitle()->getDBkey();
		$undidRevision = $target->mTextId;

		$filterObj = new BayesianFilter();
		$row = $filterObj->getRevertedEditsInfo($undidRevision);

		$filterObj->insertRevertedEdits( $row, $title, "", $user->getId(), $user->getName(), "rollback", "" ) ;

	}

	/**
	* Hook function for EditFilterMergedContent
	*
	* This is a postmerge hook, meaning it is run when article is saved. This functions saves
	* the edits which were an undo operation in the database
	* @context is the instance of type RequestContext
	* @content is the intance of class Content.
	* @summary is the summary entered by the user while editing the page
	* @user is the instance of User and is the current user who is editing the page
	* @minoredit is true when the user checks the minoredit checkbox, otherwise false
	*/
	
	static function filterMergedContent(RequestContext $context, Content $content, Status $status,
	$summary, User $user,  $minoredit) {

		$request = $context->getRequest();
		$undidRevision = $request->getVal('wpUndidRevision'); //to check if the edit was an undo operation.
		if(!isset($undidRevision))
			return ;

		$title = $request->getVal('title');
		$spam = 'N';
		
		$wpSpam = $request->getval('wpSpam');
		if(isset($wpSpam))
			$spam = 'Y';

		$filterObj = new BayesianFilter();
		
		$row = $filterObj->getRevertedEditsInfo($undidRevision);
		
		$filterObj->insertRevertedEdits( $row, $title, $summary, $user->getId(), $user->getName(), "undo", $spam ) ;
		
	}


	/**
	* Hook function for EditPageBeforeEditChecks
	*
	* This hook is run whenever an article is opened for edit. It adds the "Mark as Spam" checkbox
	* besides "Watch this Page" and "This is a minor edit"
 	* @editpage is passed by reference to this function.
	* @checks is an array that is passed by reference to this function. It is an array of checkboxes
	* @tabindex is the index of current tab.
	*/


	public static function addFlagSpamCheckbox( &$editpage, &$checks, &$tabindex ){

		$view = new BayesianFilterPageView();
		$view->addFlagSpamCheckbox( $checks, $tabindex );
	}
}


