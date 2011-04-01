<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


// possibly bug, that it doesn't use ext_autoload.php
if(TYPO3_MODE != 'FE'){
	require_once(t3lib_extMgm::extPath('tidier').'tasks/class.tx_tidier_task.php');	
	require_once(t3lib_extMgm::extPath('tidier').'tasks/class.tx_tidier_task_addFields.php');	
}


// Register with "crawler" extension:
$TYPO3_CONF_VARS['EXTCONF']['crawler']['procInstructions']['tx_tidier_tidy'] = 'Check HTML with Tidy';
$TYPO3_CONF_VARS['EXTCONF']['crawler']['cli_hooks']['tx_tidier_crawl'] = 'EXT:tidier/class.tx_tidier_crawler.php:&tx_tidier_crawler';

// Add task to scheduler
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_tidier_task'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Tidier task',
    'description'      => 'Check with the scheduler the quality of your HTML code.',
    'additionalFields' => 'tx_tidier_task_addFields',
);
?>