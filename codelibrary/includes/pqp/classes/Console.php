<?php

/* - - - - - - - - - - - - - - - - - - - - -

 Title : PHP Quick Profiler Console Class
 Author : Created by Ryan Campbell
 URL : http://particletree.com/features/php-quick-profiler/

 Last Updated : April 22, 2009

 Description : This class serves as a wrapper around a global
 php variable, debugger_logs, that we have created.

- - - - - - - - - - - - - - - - - - - - - */

class Console {
	
	/*-----------------------------------
	     LOG A VARIABLE TO CONSOLE
	------------------------------------*/
	
	public static function log($data) {
		$logItem = array(
			"data" => $data,
			"type" => 'log'
		);

		if(!isset($GLOBALS['debugger_logs']['logCount'])) $GLOBALS['debugger_logs']['logCount'] = 0; 

		$GLOBALS['debugger_logs']['console'][] = $logItem;
		$GLOBALS['debugger_logs']['logCount'] += 1;
	}
	
	/*---------------------------------------------------
	     LOG MEMORY USAGE OF VARIABLE OR ENTIRE SCRIPT
	-----------------------------------------------------*/
	
	public static function logMemory($object = false, $name = 'PHP') {
		$memory = memory_get_usage();
		if($object) $memory = strlen(serialize($object));
		$logItem = array(
			"data" => $memory,
			"type" => 'memory',
			"name" => $name,
			"dataType" => gettype($object)
		);

		if(!isset($GLOBALS['debugger_logs']['memoryCount'])) $GLOBALS['debugger_logs']['memoryCount'] = 0; 

		$GLOBALS['debugger_logs']['console'][] = $logItem;
		$GLOBALS['debugger_logs']['memoryCount'] += 1;
	}
	
	/*-----------------------------------
	     LOG A PHP EXCEPTION OBJECT
	------------------------------------*/
	
	public function logError($exception, $message) {
		$logItem = array(
			"data" => $message,
			"type" => 'error',
			"file" => $exception->getFile(),
			"line" => $exception->getLine()
		);

		if(!isset($GLOBALS['debugger_logs']['errorCount'])) $GLOBALS['debugger_logs']['errorCount'] = 0; 

		$GLOBALS['debugger_logs']['console'][] = $logItem;
		$GLOBALS['debugger_logs']['errorCount'] += 1;
	}
	
	/*------------------------------------
	     POINT IN TIME SPEED SNAPSHOT
	-------------------------------------*/
	
	public function logSpeed($name = 'Point in Time') {
		$logItem = array(
			"data" => PhpQuickProfiler::getMicroTime(),
			"type" => 'speed',
			"name" => $name
		);

		if(!isset($GLOBALS['debugger_logs']['speedCount'])) $GLOBALS['debugger_logs']['speedCount'] = 0; 

		$GLOBALS['debugger_logs']['console'][] = $logItem;
		$GLOBALS['debugger_logs']['speedCount'] += 1;
	}
	
	/*-----------------------------------
	     SET DEFAULTS & RETURN LOGS
	------------------------------------*/
	


	function getLogs() { 



	if(!isset($GLOBALS['debugger_logs']['memoryCount'])) $GLOBALS['debugger_logs']['memoryCount'] = 0; 
	if(!isset($GLOBALS['debugger_logs']['logCount'])) $GLOBALS['debugger_logs']['logCount'] = 0; 
	if(!isset($GLOBALS['debugger_logs']['speedCount'])) $GLOBALS['debugger_logs']['speedCount'] = 0; 
	if(!isset($GLOBALS['debugger_logs']['errorCount'])) $GLOBALS['debugger_logs']['errorCount'] = 0; 
	return $GLOBALS['debugger_logs']; }


}

?>