<?php

if ( !defined( 'MEDIAWIKI' ) ) {     exit; }

class BayesianFilter {

	public function getRevertedEditsInfo($undidRevision)
	{
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
			array(	't1' => "revision", 
					't2' => "revision",	
					't3' => "text",
					't4' => "text"
				),
			array(	'undid_rev_id' => "t1.rev_id", 
					'curr_rev_id' => "t2.rev_id", 
					'undid_rev_text_id' => "t1.rev_text_id",
					'undid_rev_text' => "t3.old_text",
					'curr_rev_text_id' => "t2.rev_text_id",
					'curr_rev_text' => "t4.old_text",
					'undid_rev_user_id' => "t1.rev_user",
					'undid_rev_user' => "t1.rev_user_text",
					'curr_rev_user_id' => "t2.rev_user",
					'curr_rev_user' => "t2.rev_user_text",
					'page_id' => "t1.rev_page"
				),
			array('t1.rev_id' => $undidRevision),
			__METHOD__,
			array(),
			array(	't2' => array('INNER JOIN', 't2.rev_id = t1.rev_parent_id'),
					't3' => array('INNER JOIN', 't3.old_id = t1.rev_text_id'),
					't4' => array('INNER JOIN', 't4.old_id = t2.rev_text_id')
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


