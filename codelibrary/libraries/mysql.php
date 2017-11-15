<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cedric
 * Date: 13/10/13
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */
# debugs mysql comments

class Mysql_Library
{
	var $id, $result, $rows, $data, $a_rows;

	# array of all queries for debug and PGP purposes
	var	$queriesArray	=	array();
	# counter for number of queries
	var	$queries		=	0;


	# this version of the class will automatically connect to the database
	public function __construct($host=MYSQL_HOST,$user=MYSQL_USER,$pass=MYSQL_PASS,$database=MYSQL_DBASE)
	{
#		$host 		=	MYSQL_HOST;
#		$user 		=	MYSQL_USER;
#		$pass	 	=	MYSQL_PASS;
#		$database 	= 	MYSQL_DBASE;

		$this->queries			=	0;
		$this->queriesArray 	=	array();


		# store the id for future use
		$this->id	=	mysql_connect($host, $user, $pass) or
			$this->error("Unable to connect to MySQL server: $host : $database",__FILE__,__LINE__);

		# select the database
		mysql_select_db($database, $this->id) or
			$this->error("Unable to select database: $database",__FILE__,__LINE__);

		mysql_query("SET NAMES 'utf8';");
		

	}

	# close the connections and clean up
	function __destruct()
	{
		mysql_close($this->id);
	}


	# close connection
	function close()
	{
		mysql_close($this->id);
	}


	# sets the database
	function setDB($database)
	{
		mysql_select_db($database, $this->id) or
			$this->error("Unable to select database: $database",__FILE__,__LINE__);
	}






	# explains the query - so we can see if wwe are at least a bit optimized
	private function explain($query,$file,$line)
	{
		if	(DEBUG_MYSQL == false)
			return;

		$query		=	'explain '.$query;

		$result		=	mysql_query($query);

		if (!$result)
		{
			echo	"could not run query $query";
			return;
		}

		$string		=	"";

		$string		.=	$query;

		$string		.=	"\n";

		$num		=	mysql_num_fields($result);

		for ($i=0; $i < $num; $i++)
		{
			$string		.=	mysql_field_name($result, $i);
			$string		.=	"\t";
		}

		$string		.=	"\n";

		while ($row = mysql_fetch_row($result))
		{
			$string	.=	implode($row,"\t")."\n";

		}



		$mysql_timer	=	new Timer_Library();

		$result			=	mysql_query($query);

		$mysql_time		=	$mysql_timer->ReturnTimer(5);

		$seconds		=	$mysql_timer->pCalcTime();

		if	($seconds > .009)
		{

			if	(array_key_exists('REMOTE_ADDR',$_SERVER))
			{
				$page	=	"http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
				$page	.=	iif(!empty($_SERVER['QUERY_STRING']), "?{$_SERVER['QUERY_STRING']}", "");
				$page	=	"\n";
			}
			else
			{
				$page	=	"";
			}


			if (!$handle = fopen(MYSQL_LOG_FILE, 'a+')) { echo("Failed to open MYSQLlog file"); return; }
			$time		=	date("Y-m-d G:i:s");

			// Create log line
			$logline	=	'-------------------------------------------------------------------------------'	. "\n"	.	$string .	"\n"	.	$time . '  Query Time:' .	$mysql_time	.	"\n"	.	$page	.	'-------------------------------------------------------------------------------'	. "\n\n\n";

			// Write $logline to our logfile.
			if (fwrite($handle, $logline) === FALSE) { die("Failed to write to log file"); } fclose($handle);
		}
	}









	# Use this function is the query will return multiple rows.  Use the Fetch
	# routine to loop through those rows.
	public function query($query,$file,$line,$time=MYSQL_CACHE_TIME_LONG)
	{
		$start = $this->getTime();

		$this->result = mysql_query($query, $this->id) or
			$this->error ("Unable to perform query: $query",$file,$line);
		$this->rows = mysql_num_rows($this->result);
		$this->a_rows = mysql_affected_rows($this->id);


		if	(DEBUG_MYSQL == true)
			$this->explain($query,$file,$line);

		$this->logQuery($query, $start,$file,$line);
	}

	# Use this function if the query will only return a
	# single data element.
	function queryItem ($query,$file,$line,$time=MYSQL_CACHE_TIME_LONG)
	{
		global	$gCache;

		$start = $this->getTime();

		# cache active?
		if	(is_object($gCache) == true)
		{
			if	($gCache->mysql_cache_check($query,$this->data[0],$start) == true)
			{
				return	$this->data[0];
			}
		}

		$this->result = mysql_query($query, $this->id) or
			$this->error ("Unable to perform query: $query",$file,$line);
		$this->rows = mysql_num_rows($this->result);
		$this->a_rows = mysql_affected_rows($this->id);
		$this->data = mysql_fetch_row($this->result) or
			$this->data[0] = "";
		mysql_free_result($this->result);

		if	(DEBUG_MYSQL == true)
			$this->explain($query,$file,$line);

		$this->logQuery($query, $start,$file,$line);

		# cache active?
		if	(is_object($gCache) == true)
		{
			# stores in cache if possible
			$gCache->mysql_cache_store($query,$this->data[0],$start,$time);
		}

		return($this->data[0]);
	}



	# This function is useful if the query will only return a
	# single row.
	function queryRow ($query,$file,$line,$time=MYSQL_CACHE_TIME_LONG)
	{
		global	$gCache;
		$start = $this->getTime();

		# cache
		# cache active?
		if	(is_object($gCache) == true)
		{
			if	($gCache->mysql_cache_check($query,$this->data,$start) == true)
			{
				return	$this->data;
			}
		}
	  	$this->result = mysql_query($query, $this->id) or
	  		$this->error ("Unable to perform query: $query",$file,$line);
	  	$this->data	=	mysql_fetch_assoc($this->result) or "";


	  	if	(DEBUG_MYSQL == true)
	  		$this->explain($query,$file,$line);

	  	unset($this->result);
	  	$this->logQuery($query, $start,$file,$line);

		# cache active?
		if	(is_object($gCache) == true)
		{
			# stores in cache if possible
			$gCache->mysql_cache_store($query,$this->data,$start,$time);
		}

	  	return	($this->data);
	}



		# single line but allows error messages
	function insert ($query,$file,$line)
	{
		global $gCache;
		$start = $this->getTime();
		$this->result = mysql_query($query, $this->id) or
			$this->error ("Unable to perform insert: $query",$file,$line);
		$this->a_rows = mysql_affected_rows($this->id);
		$this->logQuery($query, $start,$file,$line);

		# cache active?
		if	(is_object($gCache) == true)
		{
			# delete after modifying the table
			$gCache->mysql_cache_delete($query);
		}

		return mysql_affected_rows($this->id);
	}

	function insertID ()
	{
		return mysql_insert_id();
	}





	# single line but allows error messages
	function update ($query,$file,$line)
	{
		global	$gCache;
		$start = $this->getTime();
		$this->result = mysql_query($query, $this->id) or
			$this->error ("Unable to perform update: $query",$file,$line);
		$this->a_rows = mysql_affected_rows($this->id);
		$this->logQuery($query, $start,$file,$line);

		# cache active?
		if	(is_object($gCache) == true) {
			# delete after modifying the table
			$gCache->mysql_cache_delete($query);
		}
	}


	# single line but allows error messages
	function delete($query,$file,$line)
	{
		global	$gCache;
		$start = $this->getTime();
		$this->result = mysql_query($query, $this->id) or
			$this->error ("Unable to perform Delete: $query",$file,$line);
		$this->a_rows = mysql_affected_rows($this->id);

		$this->logQuery($query, $start,$file,$line);

		# cache active?
		if	(is_object($gCache) == true)
		{
			# delete after modifying the table
			$gCache->mysql_cache_delete($query);
		}

	}





	# Returns an array[columnName] = value of a $table_name
	function selectToArray($query,$file,$line,$time=MYSQL_CACHE_TIME_LONG)
	{
		global	$gCache;

		$start	= $this->getTime();

		$got 	= array();

		# cache active?
		if	(is_object($gCache) == true)
		{
			# cache
			if	($gCache->mysql_cache_check($query,$got,$start) == true)
			{
				return	$got;
			}
		}

		$got 	= array();

		$result = mysql_query($query) or  $this->error ("Unable to perform query: $query",$file,$line);
		while ($row = mysql_fetch_assoc($result))
		{
			array_push($got, $row);
		}

		if	(DEBUG_MYSQL == true)
			$this->explain($query,$file,$line);


		$this->logQuery($query, $start,$file,$line);

		unset($result);

		# cache active?
		if	(is_object($gCache) == true)
		{
			# stores in cache if possible
			$gCache->mysql_cache_store($query,$got,$start,$time);
		}


		return $got;
	}





	# stores the query and time in an array
	function logQuery(&$sql, &$start,&$file,&$line)
	{
		$this->queries++;

		if	(DEBUG_PROFILER)
		{

			$query = array(
				'sql' => $sql,
				'file' => $file,
				'line' => $line,
				'time' => ($this->getTime() - $start)*1000   );

			if	(is_array($this->queriesArray) == false)
			{
				$this->queriesArray	=	array();
			}

			array_push($this->queriesArray, $query);
		}
	}


	function getTime()
	{
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;
		return $start;
	}



	# Print out an MySQL error message
	function error($msg,$file,$line)
	{
		# Close out a bunch of HTML constructs which might prevent
		# the HTML page from displaying the error text.
		$text	=	"<p>";

		echo("</ul></dl></ol>\n");
		echo("</table></script>\n");

		# check for logged in amember user and if so, splice
		if	(isset($_SESSION['_amember_user']['login']))
		{
			$user_msg	=	"USER:" . $_SESSION['_amember_user']['login'];
			$msg		.=	$user_msg.	", ";
			$text		.=	$user_msg.	", ";
		}
		$msg	=	"FILE:$file, Line:$line, error:$msg :";
		$msg	.=	mysql_error();


		# Display the error message
		$text	.=	"File:$file, Line:$line, error: $msg :";
		$text	.=	mysql_error();
		$text	.=	"\n";


		// this was also called - sends an sms message
		//		ReportProblem($msg);
		$this->addMysqlComment($msg);


		die($text);
	}




	#----------------------------------------------------------------------------------------------------
	# logs info to logfile
	private function addMysqlComment($comment = "",$fn=MYSQL_LOG_FILE)
	{

		# Getting the information
		if	(array_key_exists('REMOTE_ADDR',$_SERVER))
		{
			$ipaddress		=	$_SERVER['REMOTE_ADDR'];
			$page			=	"http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
			$page			.=	$_SERVER['QUERY_STRING'];
			$useragent		=	$_SERVER['HTTP_USER_AGENT'];
			$remotehost		=	@getHostByAddr($ipaddress);
		}
		else
		{
			$ipaddress		=	"";
			$page			=	"";
			$useragent		=	"";
			$remotehost		=	"";
		}

		$time			=	date("Y-m-d G:i:s");

		if (!$handle = fopen($fn, 'a+'))
		{
			echo("Failed to open addcomment log file $fn");
			return;
		}

		// Create log line
		$logline	=	$time . '|' . $ipaddress . '|' . $page . '|' . $comment . "\n";


		// Write $logline to our logfile.
		if (fwrite($handle, $logline) === FALSE)
		{
			echo("Failed to write to main log file");
		}

		fclose($handle);

	}




	# adds a log
	public function addLog($type,$ref,$action="")
	{
		global $gMysql;
		global $gSession;

		if	($gSession)
		{
			$user	=	$gSession->getUserName();
		}
		else
		{
			$user	=	"no user logged";
		}

		$sql	=	"insert into fp_log (id,type,ref,user,action,timestamp) values(0,'$type','$ref','$user','$action',NOW())";
		$gMysql->insert($sql,__FILE__,__LINE__);
	}



}