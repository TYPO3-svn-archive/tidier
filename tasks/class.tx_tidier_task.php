<?php
class tx_tidier_task extends tx_scheduler_Task {

	public function execute() {
    	mail('firma@sfroemken.de', 'Server Error', 'Fehler: '.$errstr.CHR(10).
                'Fehlernummer: '.$errno.CHR(10).
                'Server: '.$this->ip.CHR(10).
                'Port: '.$this->port);
        return true;
    }
    
    public function getAdditionalInformation() {
        return 'Server: '.$this->ip.' wird auf Port: '.$this->port.' gescannt.';
    }
}
?>