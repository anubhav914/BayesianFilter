<?php

if ( !defined( 'MEDIAWIKI' ) ) {     exit; }

class BayesianFilterDBHandler {

	public function getRevertedEditsInfo($undidRevision)
	{
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
			array(	'revision_previousEdit' => "revision", 
					'revision_currentEdit' => "revision"
				),
			array(	'undid_rev_id' 		=> "revision_previousEdit.rev_id", 
					'curr_rev_id' 		=> "revision_currentEdit.rev_id", 
					'undid_rev_text_id' => "revision_previousEdit.rev_text_id",
					'curr_rev_text_id' 	=> "revision_currentEdit.rev_text_id",
					'undid_rev_user_id' => "revision_previousEdit.rev_user",
					'undid_rev_user' 	=> "revision_previousEdit.rev_user_text",
					'curr_rev_user_id' 	=> "revision_currentEdit.rev_user",
					'curr_rev_user' 	=> "revision_currentEdit.rev_user_text",
					'page_id' 			=> "revision_previousEdit.rev_page"
				),
			array('revision_previousEdit.rev_id' => $undidRevision),
			__METHOD__,
			array(),
			array(	'revision_currentEdit' => array('INNER JOIN', 
													'revision_currentEdit.rev_id = revision_previousEdit.rev_parent_id'
													),
				)
			);

		return $res;
	}

	function insertRevertedEdits($row, $title, $summary, $userId, $userName, $action, $spam)
	{


		$dbw = wfGetDB( DB_MASTER ) ;
	
		$dbw->insert( 
				"reverted_edits",
				array(
						're_undid_rev_id' 		=> $row->undid_rev_id,
						're_curr_rev_id'  		=> $row->curr_rev_id,
						're_page'	      		=> $row->page_id,
						're_title'		   		=> $title,
						're_undid_rev_text_id' 	=> $row->undid_rev_text_id,
						're_curr_rev_text_id'	=> $row->curr_rev_text_id,
						're_comment' 			=> $summary,
						're_undid_user'			=> $row->undid_rev_user_id,
						're_undid_user_text'	=> $row->undid_rev_user,
						're_curr_user'			=> $row->curr_rev_user_id,
						're_curr_user_text'		=> $row->curr_rev_user,
						're_user'				=> $userId,
						're_user_text'			=> $userName,
						're_action'				=> $action,
						're_spam'				=> $spam,
					),
				__METHOD__,
				array()
			);
	}
}


