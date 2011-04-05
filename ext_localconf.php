<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


// possibly bug, that it doesn't use ext_autoload.php
if(TYPO3_MODE != 'FE'){
	require_once(t3lib_extMgm::extPath('tidier').'tasks/class.tx_tidier_task.php');	
	require_once(t3lib_extMgm::extPath('tidier').'tasks/class.tx_tidier_task_addFields.php');	
}

// Add task to scheduler
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_tidier_task'] = array(
	'extension'		=> $_EXTKEY,
	'title'			=> 'Tidier task',
	'description'	  => 'Check with the scheduler the quality of your HTML code.',
	'additionalFields' => 'tx_tidier_task_addFields',
);

// add extension configuration
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY] = unserialize($_EXTCONF);
?>