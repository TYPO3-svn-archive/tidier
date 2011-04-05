<?php

require_once(PATH_t3lib . 'class.t3lib_tsfebeuserauth.php');
class tx_tidier_task extends tx_scheduler_Task {

	/**
	 * @var arary Extension configuration
	 **/
	private $conf;
	
	/**
	 * holds all errorcodes and their corresponding uids
	 **/
	private $error_code = array();
	
	
	/**
	 * pid to start from
	 * @var int 
	 **/
	private $page;
	
	/**
	 * depth to scan from above Pid
	 * @var int 
	 **/
	private $depth = 4;
	
	/**
	 * Domain to be checked
	 * @var string 
	 **/
	private $domain;
	
	/**
	 * Be users to be notified
	 * @var array
	 **/
	private $beusers = array();
	/**
	 * Execute the tidy command on selected pages
	 * @param void
	 **/
	public function execute() {
		$success = false;
		// preload error_code
		$this->init();
		// execute the command on all pages selected
		$pids = $this->extGetTreeList($this->getPage(), $this->getDepth(), 0, "(fe_group=0 OR fe_group='') and hidden < 1");
		t3lib_div::devLog($pids,12345);
		$pages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','pages', 'uid in('.$pids.')');
		if(count($pages) > 0){
			foreach($pages as $page){
				$this->executeTidy($page);
			}
			$success = true;
		}
		
		/*mail('firma@sfroemken.de', 'Server Error', 'Fehler: '.$errstr.CHR(10).
				'Fehlernummer: '.$errno.CHR(10).
				'Server: '.$this->ip.CHR(10).
				'Port: '.$this->port);
		*/
		return $success;
	}
	
	public function getAdditionalInformation() {
		return '';
	}
	
	private function executeTidy($page){
	
		// cleanup records of this page first
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_tidier_error','error_pid = '.intval($page['uid']));
		
		// build the command
		$search =array(
			'###TEMP_RESULT###',
			'###TEMP_FILE###', 
			'###TIDY_ACCESS###',
		);
		$replace = array(
			$this->tempFilename($page['uid'], 'result'),
			$this->tempFilename($page['uid'], 'file'),
			(intval($this->conf['tidyAccess']) > 0 ? intval($this->conf['tidyAccess']) : 2),
		);
		$cmd = str_replace($search, $replace, $this->conf['tidyCommand']);
		
		// this has to be cleaned up, some error handling!
		// get content
		$content = t3lib_div::getURL($this->getDomain() . '/?id='.intval($page['uid']));
		file_put_contents($this->tempFilename($page['uid'], 'file'), $content);
		
		// exec tidy
		exec($cmd);
		// remove html file
		@unlink($this->tempFilename($page['uid'], 'file'));
		
		
	}
	
	private function parseTidy($page){
		$tstamp = time();
		
		$contents = @file_get_contents($this->tempFilename($page['uid'], 'result'));
		$lines = explode("\n",$contents);
		foreach($lines as $line){
			$this->parseTidyReport($page,$line);
		}
	}
	
	private function parseTidyReport($page, $line){
		$match = '/(line)\s(\d+)\s(column)\s(\d+)\s-\s(Access):\s\[([0-9\.]+)\]/';
		preg_match($match,$line,$matches);
		$report = array(
			'tstamp'=> $tstamp,
			'error_pid'=> $page['uid'],
			'error_line'=> $matches[2],
			'error_column'=> $matches[4],
			'error_code_uid' => $this->error_code[$matches[6]],
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_tidier_error',$report);
	}
	
	private function addReport($report = array()){
		
	}
	
	/**
	 * Preload all errors to reduce database access
	 **/
	private function init(){
		$this->conf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tidier'];
		// cache all tidy results
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,error_code','tx_tidier_errorcode');
		while(list($uid,$error_code) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)){
			$this->error_codes[$error_code] = $uid;
		}
	}
	
	public function tempFilename($pid,$what = 'result'){
		global $TYPO3_CONF_VARS;
		return PATH_site . 'typo3temp/tidy_' . md5($pid . $what . $TYPO3_CONF_VARS['SYS']['encryptionKey']);
	}
	/**
	 * Get the value of the protected property page.
	 *
	 * @return  integer      UID of the start page for this task.
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Set the value of the private property page.
	 *
	 * @param  integer      UID of the start page for this task.
	 * @return void
	 */
	public function setPage($page) {
		$this->page =$page;
	}

	/**
	 * Get the value of the protected property depth.
	 *
	 * @return  integer     Level of pages the task should check.
	 */
	public function getDepth() {
		return $this->depth;
	}

	/**
	 * Set the value of the private property depth.
	 *
	 * @param  integer     Level of pages the task should check.
	 * @return void
	 */
	public function setDepth($depth) {
		$this->depth = $depth;
	}
	
	/**
	 * @param 
	 * @return void
	 **/
	public function setDomain($domain){
		$this->domain = trim($domain);
	}
	
	/**
	 * @param void
	 * @return 
	 **/
	public function getDomain(){
		return $this->domain;
	}
	
	public function setBeusers($beusers){
		$this->beusers = $beusers;
	}
	
	public function getBeusers(){
		return $this->beusers;
	}
	
	
	
	
	
	
	/**
	 * Calls t3lib_tsfeBeUserAuth::extGetTreeList.
	 * Although this duplicates the function t3lib_tsfeBeUserAuth::extGetTreeList
	 * this is necessary to create the object that is used recursively by the original function.
	 *
	 * Generates a list of page uids from $id. List does not include $id itself.
	 * The only pages excluded from the list are deleted pages.
	 *
	 *							  level in the tree to start collecting uids. Zero means
	 *							  'start right away', 1 = 'next level and out'
	 *
	 * @param	integer		Start page id
	 * @param	integer		Depth to traverse down the page tree.
	 * @param	integer		$begin is an optional integer that determines at which
	 * @param	string		Perms clause
	 * @return	string		Returns the list with a comma in the end (if any pages selected!)
	 */
	public function extGetTreeList($id, $depth, $begin = 0, $permsClause) {
		$depth = intval($depth);
		$begin = intval($begin);
		$id = intval($id);
		$theList = '';

		if ($depth > 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid,title',
				'pages',
				'pid=' . $id . ' AND deleted=0 AND ' . $permsClause
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($begin <= 0) {
					$theList .= $row['uid'] . ',';
				}
				if ($depth > 1) {
					$theList .= $this->extGetTreeList($row['uid'], $depth - 1, $begin - 1, $permsClause);
				}
			}
		}
		return $theList;
	}
}
?>