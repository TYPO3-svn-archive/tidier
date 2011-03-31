<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Register with "crawler" extension:
$TYPO3_CONF_VARS['EXTCONF']['crawler']['procInstructions']['tx_tidier_tidy'] = 'Check HTML with Tidy';
$TYPO3_CONF_VARS['EXTCONF']['crawler']['cli_hooks']['tx_tidier_crawl'] = 'EXT:tidier/class.tx_tidier_crawler.php:&tx_tidier_crawler';
?>