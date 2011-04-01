<?php
$extensionPath = t3lib_extMgm::extPath('tidier');

return array(
	'tx_tidier_processor' 		=> $extensionPath.'classes/class.tx_tidier_processor.php',
	'tx_tidier_task' 			=> $extensionPath.'tasks/class.tx_tidier_task.php',
	'tx_tidier_task_addFields' 	=> $extensionPath.'tasks/class.tx_tidier_task_addFields.php',
);
?>