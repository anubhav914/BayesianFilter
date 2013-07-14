<?php

# Loader for bayesian filter feature
# Include this from LocalSettings.php

if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

$wgExtensionCredits['antispam'][] = array(
	'path'           => __FILE__,
	'name'           => 'BayesianFilter',
	'author'         => array( 'Anbhav Agarwal'),
	'url'            => 'https://www.mediawiki.org/wiki/Extension:BayesianFilter',
	'descriptionmsg' => 'Filters wiki text into spam and hams using bayesian techniques',
);

$dir = __DIR__ . '/';
$wgExtensionMessagesFiles['BayesianFilter'] = $dir . 'BayesianFilter.i18n.php';

/**
 * Array of settings for filter classes
 */
$wgFilterSettings = array();

$wgHooks['ArticleRollbackComplete'][] = 'BayesianFilterHooks::rollbackComplete';
$wgHooks['EditFilterMergedContent'][] = 'BayesianFilterHooks::filterMergedContent';
$wgHooks['EditPageBeforeEditChecks'][] = 'BayesianFilterHooks::addFlagSpamCheckbox';


$wgAutoloadClasses['BayesianFilterHooks'] = $dir . 'BayesianFilter.Hooks.php';
$wgAutoloadClasses['BayesianFilterPageView'] = $dir . 'BayesianFilter.PageView.php';
$wgAutoloadClasses['BayesianFilter'] = $dir . 'BayesianFilter.body.php';