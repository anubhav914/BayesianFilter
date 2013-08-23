<?php

if ( !defined( 'MEDIAWIKI' ) ) {     exit; }

/**
* Hooks for Bayesian Filter Extension
*/

class BayesianFilterHooks {

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
		$filter = new BayesianFilter;
		$filterDbHandler = new BayesianFilterDBHandler;

		// wfDebugLog('BayesianFilter', $content ); 
		$undidRevision = $request->getVal( 'wpUndidRevision' );

		if( isset( $undidRevision ) && !empty( $undidRevision ) )
		{
			$wpSpam = $request->getVal( 'wpSpam' );
			if( isset( $wpSpam ) && !empty( $wpSpam ) )
			{
				$text = $filterDbHandler->getRevertedText( $undidRevision );
				$filter->train( $text, "spam" );
				$filterDbHandler->insertSpamText( $text ) ;
			}
		}
		else
		{
			$result = $filter->checkSpam( $content );
			if( $result )
			{
				$editPage->spamPageWithContent( $result );
				return false;
			}
			else
				$filterDbHandler->insertSpamText( $content, false );
		}
		return true;
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


	public static function addFlagSpamCheckbox( &$editPage, &$checks, &$tabindex ){

		$context = $editPage->mArticle->getContext();
		$view = new BayesianFilterPageView( $context );
		$view->addFlagSpamCheckbox( $checks, $tabindex );

		return true;
	}
}