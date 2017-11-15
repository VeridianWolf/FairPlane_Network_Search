<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cedric
 * Date: 14/10/13
 * Time: 16:41
 * To change this template use File | Settings | File Templates.
 */

require_once($_SERVER["DOCUMENT_ROOT"]	.	"/../codelibrary/includes/pqp/classes/PhpQuickProfiler.php");



class Profiler_Library {

	private $profiler;
	private $db = '';

	public function __construct($gMysql=null)
	{
		$this->profiler	=	new PhpQuickProfiler();
	}

	# sets the mysql ptr
	public function setup($gMysql=null)
	{
		$this->profiler->setup($gMysql);
	}

	/*-------------------------------------------
	     EXAMPLES OF THE 4 CONSOLE FUNCTIONS
	-------------------------------------------*/

	public function sampleConsoleData() {
		try {
			Console::log('Begin logging data');
			Console::logMemory($this, 'PQP Example Class : Line '.__LINE__);
			Console::logSpeed('Time taken to get to line '.__LINE__);
			Console::log(array('Name' => 'Ryan', 'Last' => 'Campbell'));
			Console::logSpeed('Time taken to get to line '.__LINE__);
			Console::logMemory($this, 'PQP Example Class : Line '.__LINE__);
			Console::log('Ending log below with a sample error.');
			throw new Exception('Unable to write to log!');
		}
		catch(Exception $e) {
			Console::logError($e, 'Sample error logging.');
		}
	}

	/*-----------------------------------
	     EXAMPLE MEMORY LEAK DETECTED
	------------------------------------*/

	public function sampleMemoryLeak() {
		$ret = '';
		$longString = 'This is a really long string that when appended with the . symbol
					  will cause memory to be duplicated in order to create the new string.';
		for($i = 0; $i < 10; $i++) {
			$ret = $ret . $longString;
			Console::logMemory($ret, 'Watch memory leak -- iteration '.$i);
		}
	}


	# my logging of an item
	public function log($text,$line=__LINE__,$file=__FILE__) {

		Console::log("$text line:$line, file:$file");
	}


	# my logging of an item
	public function logSpeed($text,$line=__LINE__) {
		Console::logSpeed("$text line:$line");
	}

	# my logging of an memory
	public function logMemory($line=__LINE__,$variable="",$name="")
	{

		Console::logMemory($variable, "$name : Line:$line");
	}


	public function __destruct() {
#		$this->profiler->display($this->db);
	}


	public function display()
	{
		if	(DEBUG_PROFILER == true)
		{
			$this->profiler->display($this->db);
		}
	}

}




