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

	function insertRevertedEdits($row, $title, $summary, $userId, $userName, $action, $spam){


		$dbw = wfGetDB( DB_MASTER ) ;
		$dbw->begin();

		$dbw->insert( 
				"reverted_edits",
				array(
						'undid_rev_id' 		=> $row->undid_rev_id,
						'curr_rev_id'  		=> $row->curr_rev_id,
						'page_id'      		=> $row->page_id,
						'title'		   		=> $title,
						'undid_rev_text_id' => $row->undid_rev_text_id,
						'curr_rev_text_id'	=> $row->curr_rev_text_id,
						'rev_comment' 		=> $summary,
						'undid_user_id'		=> $row->undid_rev_user_id,
						'undid_user'		=> $row->undid_rev_user,
						'curr_user_id'		=> $row->curr_rev_user_id,
						'curr_user'			=> $row->curr_rev_user,
						'reversion_user_id'	=> $userId,
						'reversion_user'	=> $userName,
						'action'			=> $action,
						'spam'				=> $spam,
					),
				__METHOD__,
				array()
			);


		$dbw->commit();

	}
}


