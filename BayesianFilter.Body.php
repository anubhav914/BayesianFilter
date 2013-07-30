<?php

if ( !defined( 'MEDIAWIKI' ) ) {     exit; }

class BayesianFilter {

	public function getLinks( $text )
	{
		global $wgParser, $wgUser, $wgTitle;
		$options = new ParserOptions();
		$modifiedText = $wgParser->preSaveTransform( $text, $wgTitle, $wgUser, $options );
		$output = $wgParser->parse( $modifiedText, $wgTitle, $options );
		$links = array_keys( $output->getExternalLinks() );
		return $links;
	}

	public function checkSpam( $text )
	{
		$links = $this->getLinks( $text );
		
		$this->sanitize( $text );
		$delimiters = " \n\t\r,.";
		$words = $this->tokenize( $text, $delimiters );
		$this->removeStopWords( $words );
		$this->stem( $words );

		$filterDbHandler = new BayesianFilterDBHandler();
		
		//wordArray has keys as words and spam frequency as $word['word']['spam']
		$wordArray =  $filterDbHandler->getWordsWithFrequency();   //this function returns all the words and their frequency in spam and ham edits
		$probMsgGivenSpam = 1.0;
		$probMsgGivenHam = 1.0;

		foreach ($words as $word ) 
		{

			if( array_key_exists( $word, $wordArray ) )
			{
				$probMsgGivenSpam = $probMsgGivenSpam * ( $wordArray[$word]['spam'] + 1);
				$probMsgGivenSpam = $probMsgGivenSpam / $wordArray['allTheWords']['spam'];

				$probMsgGivenHam = $probMsgGivenHam * ( $wordArray[$word]['ham'] + 1);
				$probMsgGivenHam = $probMsgGivenHam / $wordArray['allTheWords']['ham'];
			}	
			
		}
		
		global $wgThreshold;
		$spamHamCount = $filterDbHandler->getSpamHamCount();
		$probSpam = $spamHamCount['spam'] / ( $spamHamCount['spam'] + $spamHamCount]['ham'] );
		if( $probSpam > $wgThreshold )
			return true;
		else
			return fasle;

	}

	public function sanitize( &$text)
	{
		//do nothing yet
	}

	public function tokenize( $text, $delimiters )
	{
		$words = array();
		$tok = strtok( $text, $delimiters );
		while( $tok !== false )
		{
			$words[] = $tok;
			$tok = strtok( $delimiters );
		}
		return $words;
	}

	public function removeStopWords( &$words )
	{

		$handle = fopen("StopWords.txt", "r");
		$stopWords = array();
		if( $handle )
		{
			while( ( $buffer = fgets( $handle ) ) != false )
			{
				$stopWords[] = trim( $buffer );
			}		
		}

		//optimizing the code,
		$optimizedStopWordsArray = array();
		foreach( $stopWords  as $stopWord )
		{
			$optimizedStopWordsArray[$stopWord] = 1;
		}

		foreach( $words as $key => $word )
		{
			if( array_key_exists( $word, $optimizedStopWordsArray ) )
			{
				//unset doesn't changes the indexes
				unset($words[$key]);
			}
		}
	}

}