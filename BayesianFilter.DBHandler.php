<?php

class BayesianFilterDBHandler {

	private function optimizeArray( $array )
	{
		$optimizedArray = array();
		foreach ($array as $value ) {
			if( isset( $optimizedArray[$value] ) )
				$optimizedArray[$value] += 1;
			else
				$optimizedArray[$value] = 1;
		}
		return $optimizedArray;
	}

	private function arrayToCondition( $array )
	{
		$condition = implode( "," , $array );
		$condition = "'" . str_replace( ",", "', '", $condition ) . "'";
		return $condition;
	}


	//returns the spam and ham frequecy of the words in $words array 
	//chunksize defines the size of the chunk that should be queried from a db in  
	//a single db_query. If 0 it means get all the words from words array.
	public function getFrequency( $words , $chunksize )
	{
		$optimizedWordsArray = $this->optimizeArray( $words );
		$words = array_keys( $optimizedWordsArray );
		$dbr = wfGetDB( DB_SLAVE );

		$wordsFrequency = array();
		
		if( $chunksize ) 
		{
			//array_chunk returns a multidimensional array, where is each array 
			//is of size  = $chunksize
			$wordsMultiArray = array_chunk( $words , $chunksize );
			foreach ( $wordsMultiArray as $words ) {
				
				$condition = $this->arrayToCondition( $words );
				$res = $dbr->select(
						"word_frequency",
						array( "wf_word", "wf_spam", "wf_ham" ),
						array( $condition ),
						__METHOD__,
						array()
					);
				foreach ($res as $row ) {
					$wordsFrequency[$row->wf_word] = array();
					$wordsFrequency[$row->wf_word]['spam'] = $row->wf_spam;
					$wordsFrequency[$row->wf_word]['spam'] = $row->wf_ham;
				}
			}
		}
		else
		{
			$condition = $this->arrayToCondition( $words );
			$res = $dbr->select(
					"word_frequency",
					array( "wf_word", "wf_spam", "wf_ham" ),
					array( "wf_word in " . $condition ),
					__METHOD__,
					array()
				);
			foreach ($res as $row ) {
				$wordsFrequency[$row->wf_word] = array();
				$wordsFrequency[$row->wf_word]['spam'] = $row->wf_spam;
				$wordsFrequency[$row->wf_word]['spam'] = $row->wf_ham;
			}
		}

		return $wordsFrequency;
	}

	//updates the frquency of words in words array in ham or spam column depending 
	//upon the value of $category

	public function insertFrequencyTable( $words, $category )
	{
		$optimizedWordsArray = $this->optimizeArray( $words );
		$words = array_keys( $optimizedWordsArray );
		$condition = $this->arrayToCondition( $words );
		
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				"word_frequency",
				array( "wf_word", "wf_spam", "wf_ham" ),
				array( "wf_word in " . $condition ),
				__METHOD__,
				array(),
			);
		$exists = array();
		foreach ( $res as $row ) {
			$exists[$row->wf_word] = array();
			$exists[$row->wf_word]['spam'] = $row->wf_spam;
			$exists[$row->wf_word]['ham'] = $row->wf_ham;
		}
		$dbw = wfGetDB( DB_MASTER ) ;
		$fieldName = "wf_" . $category; 
		foreach ( $words as $word ) 
		{
			
			if( isset( $exists[$word] ) )
			{
				$dbw->update(
					"word_frequency",
					array( $fieldName => ( $exists[$word][$category] + $optimizedWordsArray[$word] ) ),
					array( 'wf_word' => $word ),
					__METHOD__,
					array();
				);
			}
			else
			{
				$dbw->insert(
					"word_frequency",
					array( $fieldName => $optimizedWordsArray[$word] ),
					array( 'wf_word' => $word ),
					__METHOD__,
					array();
				);
			}
		}
	}

	public function getRevertedText( $undidRevision )
	{
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
				array(	"text", "revision" ),
				array( 'old_text' => "text" ),
				array( 'revision.rev_id' => $undidRevision ),
				__METHOD__,
				array(),
				array('text' => array( 'INNER JOIN', 'text.old_id = revision.rev_id' )
			);
		return $res->text;

	}

	public function insertSpamText( $content, $spam=true );
	{
		$dbw = wfGetDB( DB_MASTER ) ;
		$dbw->insert(
				"spam_ham_texts",
				array(
					'sht_spam' => $spam,
					'sht_text' => $text
					),
				__METHOD__,
				array()
			);
	}

	public function getSpamHamCount()
	{
		$result = array('spam' => 0, 'ham' => 0);
		$dbr = wfGetDB( DB_SLAVE ) ;
		$res = $dbr->select(
				"spam_ham_texts",
				array("id", "sht_spam"),
				array()
				__METHOD__,
				array()
			);
		foreach ($res as $row ) {
			if($row->sht_spam == 1)
				$result['spam']++;
			else
				$result['ham']++;
		}
		return $result;
	}
}