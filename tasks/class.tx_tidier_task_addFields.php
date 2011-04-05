<?php
class tx_tidier_task_addFields 
	implements tx_scheduler_AdditionalFieldProvider {
	
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {

		if (empty($taskInfo['domain'])) {
			if($parentObject->CMD == 'edit') {
				$taskInfo['domain'] = $task->getDomain();
			} else {
				$taskInfo['domain'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').'/';
			}
		}
		
		if (empty($taskInfo['page'])) {
			if($parentObject->CMD == 'edit') {
				$taskInfo['page'] = $task->getPage();
			} else {
				$taskInfo['page'] = '';
			}
		}
		
		if (empty($taskInfo['depth'])) {
			if($parentObject->CMD == 'edit') {
				$taskInfo['depth'] = $task->getDepth();
			} else {
				$taskInfo['depth'] = 1;
			}
		}
		
		if (empty($taskInfo['beusers'])) {
			$taskInfo['beusers'] = array();
			if ($parentObject->CMD == 'edit') {
					// In case of editing the task, set to currently selected value
				$taskInfo['beusers'] = $task->getBeusers();
			}
		}
		
		$additionalFields = array();
		
		// Write the code for the field
		$fieldID = 'domain';
		$fieldCode = '<input type="text" name="tx_scheduler['. $fieldID .']" id="' . $fieldID . '" value="' . $taskInfo['domain'] . '" size="30" />';
		$additionalFields[$fieldID] = array(
			'code'	 => $fieldCode,
			'label'	=> $GLOBALS['LANG']->sL('LLL:EXT:tidier/locallang_db.xml:scheduler.tidier.domain')
		);
		
		$fieldID = 'page';
		$fieldCode = '<input type="text" name="tx_scheduler['. $fieldID .']" id="' . $fieldID . '" value="' . $taskInfo['page'] . '" size="30" />';
		$additionalFields[$fieldID] = array(
			'code'	 => $fieldCode,
			'label'	=> $GLOBALS['LANG']->sL('LLL:EXT:tidier/locallang_db.xml:scheduler.tidier.page')
		);
		
		
		
		// input for depth
		$fieldID = 'depth';
		$fieldValueArray = array(
			'0' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.depth_0'),
			'1' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.depth_1'),
			'2' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.depth_2'),
			'3' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.depth_3'),
			'4' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.depth_4'),
			'999' => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.depth_infi'),
		);
		$fieldCode = '<select name="tx_scheduler[depth]" id="' . $fieldID . '">';

		foreach ($fieldValueArray as $depth => $label) {
			$fieldCode .= "\t" . '<option value="' .  htmlspecialchars($depth) . '"' . (($depth ==$taskInfo['depth']) ? ' selected="selected"' : '') . '>' . $label . '</option>';
		}

		$fieldCode .= '</select>';
		$label = $GLOBALS['LANG']->sL('LLL:EXT:linkvalidator/locallang.xml:tasks.validate.depth');
		$label = t3lib_BEfunc::wrapInHelp('linkvalidator', $fieldID, $label);
		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			'label' => $label
		);
		

		// Write the code for the field
		$fieldID = 'beusers';
		$fieldOptions = $this->getBEUsersOptions($taskInfo['beusers']);
		$fieldHtml =
			'<select name="tx_scheduler['. $fieldID .'][]" id="' . $fieldId . '" class="wide" size="10" multiple="multiple">' .
				$fieldOptions .
			'</select>';
				
		$additionalFields[$fieldID] = array(
			'code'	 => $fieldHtml,
			'label'	=> $GLOBALS['LANG']->sL('LLL:EXT:tidier/locallang_db.xml:scheduler.tidier.beusers')
		);
		
		return $additionalFields;
	}

	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$success = TRUE;
		if (!$submittedData['domain']) {
			$parentObject->addMessage(
				$GLOBALS['LANG']->sL('LLL:EXT:tidier/locallang_db.xml:scheduler.tidier.domain.invalid'),
				t3lib_FlashMessage::ERROR
			);
			$success = FALSE;
		}
		if (intval($submittedData['page'])< 1) {
			$parentObject->addMessage(
				$GLOBALS['LANG']->sL('LLL:EXT:tidier/locallang_db.xml:scheduler.tidier.page.invalid'),
				t3lib_FlashMessage::ERROR
			);
			$success = FALSE;
		}
		
		if (intval($submittedData['depth']) < 0) { // depth can be 0 as well!
			$parentObject->addMessage(
				$GLOBALS['LANG']->sL('LLL:EXT:tidier/locallang_db.xml:scheduler.tidier.depth.invalid'),
				t3lib_FlashMessage::ERROR
			);
			$success = FALSE;
		}
		
		// be users are optional
		$submittedData['beusers'] = is_array($submittedData['beusers']) ? $submittedData['beusers'] : array();
		return $success;
	}

	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->setDomain($submittedData['domain']);
		$task->setPage($submittedData['page']);
		$task->setDepth($submittedData['depth']);
		$task->setBeusers($submittedData['beusers']);
	}
	
	
	
	protected function getBEUsersOptions(array $tidy_selectedBEUsers) {
		$options = array();
		$BEUsers = $this->getBEUsers();
		foreach ($BEUsers as $BEUser) {
			if (in_array($BEUser['uid'], $tidy_selectedBEUsers)) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$options[] =
				'<option value="'.$BEUser['uid'].'"'.$selected . '>' .
					$BEUser['username'].' - '.$BEUser['realName'].' - '.$BEUser['email'].
				'</option>';
		}
		return implode($options);
	}
	
	protected function getBEUsers() {
		$records = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'be_users',
			"username NOT LIKE '_cli%' AND email !='' AND disable=0 AND deleted=0"
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			 $records[] = $row;
		} 
		$GLOBALS['TYPO3_DB']->sql_free_result($result);

		return $records;		
	}
}
?>