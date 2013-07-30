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

	static function rollbackComplete( Page $page, User $user, $target, $current ) {
	
		$title = $page->getTitle()->getDBkey();
		$undidRevision = $target->mTextId;

		$filterDbHandler = new BayesianFilterDBHandler();
		$row = $filterDbHandler->getRevertedEditsInfo( $undidRevision );

		$filterDbHandler->insertRevertedEdits( $row, $title, "", $user->getId(), $user->getName(), "rollback", "" ) ;

		return true;
	}

	/**
	* Hook function for EditFilterMerged
	*
	* This is a postmerge hook, meaning it is run when article is saved. This functions saves
	* the edits which were an undo operation in the database
	* @context is the instance of type RequestContext
	* @content is the intance of class Content.
	* @summary is the summary entered by the user while editing the page
	* @user is the instance of User and is the current user who is editing the page
	* @minoredit is true when the user checks the minoredit checkbox, otherwise false
	*/
	
	
	static function filterMerged( EditPage $editPage, $content, &$hookErr, $summary ) {

		$context = $editPage->mArticle->getContext();
		$request = $context->getRequest();
		$title = $request->getVal( 'title' );

		global $wgUser;

		$undidRevision = $request->getVal( 'wpUndidRevision' );
		if( isset( $undidRevision ) && !empty( $undidRevision ) )
		{
			//the edit was an undo operation, so we store it in reverted_edits.
			//By default we have assumed it not to be spam, because it was undid to 
			//a previous non-spam edit.

			$spam = 0;
			$wpSpam = $request->getval( 'wpSpam' );

			if( isset( $wpSpam ) )
				$spam = 1;

			$filterDbHandler = new BayesianFilterDBHandler();
			$row = $filterDbHandler->getRevertedEditsInfo( $undidRevision );
			$filterDbHandler->insertRevertedEdits( $row, $title, $summary, $wgUser->getId(), $wgUser->getName(), "undo", $spam ) ;
			return true;
		}
		
		else
		{
			$filterObj = new BayesianFilter();
			// $result = $filterObj->checkSpam( $content );
			// if ( $result !== false ) {
			// 	$editPage->spamPageWithContent( $result );
			// }
			// // Return convention for hooks is the inverse of $wgFilterCallback
			// return ( $result === false );
			return true;
		}
		
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

		return true;
	}
}


