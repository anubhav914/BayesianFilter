<?php
/**
 * Class representing a web view of a MediaWiki page
 */
class BayesianFilterPageView extends ContextSource {

	public function addFlagSpamCheckbox( array &$checkboxes, &$tabindex ){

		$request = $this->getRequest();
		$undo = $request->getVal( 'undo' );

		if( isset( $undo ) )
		{
			$checkbox = Xml::check(
				'wpSpam',
				false,
				array( 'tabindex' => ++$tabindex, 'id' => 'wpSpam' )
			);
			$attribs = array( 'for' => 'wpSpam' );
			$attribs['title'] = $this->msg( 'flag-spam-check-title' )->text();
			$labelMsg = $this->msg( 'flag-spam-check' )->text();
			$label = Xml::element( 'label', $attribs, $labelMsg );
			$checkboxes['flaggedSpam'] = $checkbox . '&#160;' . $label;	
		}
	}
}