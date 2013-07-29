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
		$words = $this->tokenize( $text );
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
		
		$spamHamCount = $filterDbHandler->getSpamHamCount();
		$probSpam = $spamHamCount['spam'] / ( $spamHamCount['spam'] + $spamHamCount]['ham'] );


	}

}