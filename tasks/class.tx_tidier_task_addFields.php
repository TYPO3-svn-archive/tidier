<?php
class tx_tidier_task_addFields implements tx_scheduler_AdditionalFieldProvider {
    
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {

		if (empty($taskInfo['tidy_domain'])) {
            if($parentObject->CMD == 'edit') {
                $taskInfo['tidy_domain'] = $task->tidy_domain;
            } else {
                $taskInfo['tidy_domain'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').'/';
            }
        }
		
        if (empty($taskInfo['tidy_pid'])) {
            if($parentObject->CMD == 'edit') {
                $taskInfo['tidy_pid'] = $task->tidy_pid;
            } else {
                $taskInfo['tidy_pid'] = '';
            }
        }
        
		if (empty($taskInfo['tidy_selectedBEUsers'])) {
			$taskInfo['tidy_selectedBEUsers'] = array();
			if ($parentObject->CMD == 'edit') {
					// In case of editing the task, set to currently selected value
				$taskInfo['tidy_selectedBEUsers'] = $task->tidy_selectedBEUsers;
			}
		}
        
        $additionalFields = array();
        
        // Write the code for the field
        $fieldID = 'tidy_domain';
        $fieldCode = '<input type="text" name="tx_scheduler['. $fieldID .']" id="' . $fieldID . '" value="' . $taskInfo['tidy_domain'] . '" size="30" />';
        $additionalFields[$fieldID] = array(
            'code'     => $fieldCode,
            'label'    => 'Domain to tidy'
        );
		
		$fieldID = 'tidy_pid';
        $fieldCode = '<input type="text" name="tx_scheduler['. $fieldID .']" id="' . $fieldID . '" value="' . $taskInfo['tidy_pid'] . '" size="30" />';
        $additionalFields[$fieldID] = array(
            'code'     => $fieldCode,
            'label'    => 'Page id to tidy'
        );

        // Write the code for the field
        $fieldID = 'tidy_selectedBEUsers';
        //$fieldCode = '<input type="text" name="tx_scheduler['. $fieldID .']" id="' . $fieldID . '" value="' . $taskInfo['tidy_email_to'] . '" size="30" />';
        $fieldOptions = $this->getBEUsersOptions($taskInfo['tidy_selectedBEUsers']);
        $fieldHtml =
			'<select name="tx_scheduler['. $fieldID .'][]" id="' . $fieldId . '" class="wide" size="10" multiple="multiple">' .
				$fieldOptions .
			'</select>';
				
        $additionalFields[$fieldID] = array(
            'code'     => $fieldHtml,
            'label'    => 'Email to send to'
        );
        
        return $additionalFields;
    }

    public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$submittedData['tidy_domain'] = trim($submittedData['tidy_domain']);
		$submittedData['tidy_pid'] = trim($submittedData['tidy_pid']);
		$submittedData['tidy_selectedBEUsers'] = $submittedData['tidy_selectedBEUsers'];
        //$submittedData['tidy_email_to'] = trim($submittedData['tidy_email_to']);
        return true;
    }

    public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
        $task->tidy_domain = $submittedData['tidy_domain'];
        $task->tidy_pid = $submittedData['tidy_pid'];
        $task->tidy_selectedBEUsers = $submittedData['tidy_selectedBEUsers'];
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