<?php

class BayesianFilter {

	public function __construct()
	{
		if( !class_exists( 'BayesianFilterTokenizer' ) )
			require_once( __DIR__ . '/BayesianFilter.Tokenizer.php' );
		if( !class_exists( 'BayesianFilterDBHandler' ) )
			require_once( __DIR__ . '/BayesianFilter.DBHandler.php' );
	}

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
		
		$tokenizer = new BayesianFilterTokenizer;
		$text = $tokenizer->sanitize( $text );

		$words = array();

		$token = $tokenizer->tokenize( $text );
		while( $token )
		{
			if( !$tokenizer->isStopWord( $token ) )
				$words[] = $tokenizer->stem( $token );
			$token = $tokenizer->tokenize();
		}

		$filterDbHandler = new BayesianFilterDBHandler;
		global $wgChunksize;
		$wordsFrequency = $filterDbHandler->getFrequency( $words , $wgChunksize );
		
		$probMsgGivenSpam = 1.0;
		$probMsgGivenHam = 1.0;
		$spamCount =  $hamCount = 0;

		foreach( $wordsFrequency as $word => $frequency )
		{
			$spamCount += $frequency['spam'];
			$hamCount += $frequency['ham'];
		}

		$wordCount = count( $words );

		foreach ($words as $word ) 
		{
			if( isset($wordsFrequency[$word]) )
			{
				$probMsgGivenSpam = $probMsgGivenSpam * ( $wordsFrequency[$word]['spam'] + 1);
				$probMsgGivenHam = $probMsgGivenHam * ( $wordFrequency[$word]['spam'] + 1);
			}
			
			$probMsgGivenSpam = $probMsgGivenSpam / ( $spamCount + $wordCount );
			$probMsgGivenHam = $probMsgGivenHam / ( $hamCount + $wordCount );
		}
		
		global $wgSpamThreshold;
		$spamHamCount = $filterDbHandler->getSpamHamCount();
		$spamProb = ( $spamHamCount['spam'] ) / ( $spamHamCount['spam'] + $spamHamCount['ham'] );
		$hamProb = 1.0 - $spamProb;

		$probMsgGivenSpam = $probMsgGivenSpam * $spamProb;
		$probMsgGivenHam = $probMsgGivenHam * $hamProb;

		if( $probMsgGivenSpam > $probMsgGivenHam )
			return true;
		else
		{
			$filterDbHandler->insertFrequencyTable( $words, "ham" );
			return false;
		}
	}

	public function train( $text, $category )
	{
		
		$tokenizer = new BayesianFilterTokenizer;
		$text = $tokenizer->sanitize( $text );

		$token = $tokenizer->tokenize( $text );
		$words = array();

		while( $token )
		{
			if( !$tokenizer->isStopWord( $token ) )
				$words[] = $tokenizer->stem( $token );
			$token = $tokenizer->tokenize();
		}

		$filterDbHandler = new BayesianFilterDBHandler;
		$filterDbHandler->insertFrequencyTable( $words, $category );
	}
}
