<?php
class Log {
	private $filename;
	
	public function __construct($filename) {
		$this->filename = $filename;
	}
	
	public function write($message) {
        umask(0);

		$file = DIR_LOGS . $this->filename;
		
		$handle = fopen($file, 'a+'); 
		
		fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true)  . "\n");
			
		fclose($handle); 
	}

    public function getContents() {

        $file = DIR_LOGS . $this->filename;

        $data = '';

        if(file_exists($file) && is_file($file)) {

            $handle = fopen($file, 'r');

            $data = fread($handle,filesize($file));

            fclose($handle);

        }

        return $data;

    }

    public function cover($message) {

        $file = DIR_LOGS . $this->filename;

        $handle = fopen($file, 'w');

        fwrite($handle, $message);

        fclose($handle);

    }
}
?>