<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
	'tx_tidier_tidy_errors' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:tidier/locallang_db.xml:pages.tx_tidier_tidy_errors',		
		'config' => array (
			'type' => 'none',
		)
	),
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages','tx_tidier_tidy_errors;;;;1-1-1');
?>