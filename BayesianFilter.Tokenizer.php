<?

/**
 * This class contains the definitions of all the functions used for tokenizing the input
 */

class BayesianFilterTokenizer{

	/**
	* A wiki text consists of square brackets and ~, == section headling, signatures.
	* sanitizes removes all such transformations that wiki does.
	*/
	public function sanitize( $text ){

		$text = stripslashes( $text ); //strip slashes behing quotes
		$text = strip_tags( $text );   //strips the html tags like <br /> and <nowiki>

		//remove the special charachters which hold significance in wiki formatting
		$specialChars = array( "'", "\"", "=", "--", "*", "|" );
		$text = str_replace( $specialChars, "", $text );

		//remove the [[]] types of text
		$pattern = "/\[\[.*?\]\]/";
		$text = preg_replace( $pattern, "", $text );

		//remove links
		$pattern = "/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i" ;
		$text = preg_replace( $pattern, "", $text );

		//remove the other special charachters
		$specialChars = array( "[", "]", "{", "}", ":", "/" , ";" );
		$text = str_replace( $specialChars, "", $text );

		return $text;
	} 


	/**
	* returns an iterator to the next token in the text;
	*/
	public function tokenize($text = null){

		static $tok = true;
		$delimiters = " \n\t\r,.";

		if( $tok == false )
			return null;
		else if( $text )
			$tok = strtok( $text, $delimiters );
		else
			$tok = strtok( $delimiters );

		return $tok;
	}

	/**
	* returns if a word is present in stopWords or not
	*/
	public function isStopWord( $word ){

		static $stopWordDict = array();
		if(empty($stopWordDict))
		{
			$stopWords = array();
			$handle = fopen("StopWords.txt", "r");
			if( $handle )
			{
				while( ( $buffer = fgets( $handle ) ) != false )
				{
					$stopWords[] = trim( $buffer );
				}		
			}

			foreach( $stopWords  as $stopWord )
				$stopWordDict[$stopWord] = 1;
		}

		if( array_key_exists( $word, $stopWordDict ) )
			return true;
		return false;
	}

	/**
	* stems a word to its root
	*/
	public function stem( $word ){

		if( !class_exists('PorterStemmer') )
			require_once( './Stemmer.php' );

		return PorterStemmer::Stem( $word );
	}
}