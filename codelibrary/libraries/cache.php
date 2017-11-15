<?php




class Cache_Library
{
	# which cache to use
	private	$cache_version		=	1;

	# logging of all caching
	private $debug_cache_log	=	true;
	# caching engine on
	private $debug_cache_on		=	true;

	# link to the memcache object
	private $memcache			=	"";

	public function __construct()
	{
		$ip	=	$_SERVER['SERVER_ADDR'];

		$this->debug_cache_on	=	DEBUG_CACHE_ON;
		$this->debug_cache_log	=	DEBUG_CACHE_LOG;

		# memcache object
		if (class_exists('Memcache',false))
		{
			$this->memcache	=	new Memcache;
			@$this->memcache->connect($ip, 11211);

			if (defined ('CACHE_FLUSH'))
			{
				# flush all items
				if	(CACHE_FLUSH == true)
				{
					$this->memcache->flush();

					sleep(3);
				}

			}
		}

	}


	# destructor for memcache
	public function __destruct()
	{
		if (class_exists('Memcache',true))
		{
			if	(!empty($this->memcache))
			{
				@$this->memcache->close();
			}
		}
	}






	public function cache_or_get($timeout,$cachetype,$cachekey, $create_item_func, $params="",$bforce=false)
	{
		global	$gProfiler;

		# log the time
		if	($this->debug_cache_log == true)
		{
			$type	=	"SET";
			$start	=	microtime(true);
		}

		if	(CACHE_ENGINE == CACHE_ENGINE_APC)
		{
			$cache_used	=	"APC";
		}
		else
		{
			$cache_used	=	"MEMCACHE";
		}


		# does cache exist and are we allowed to use it?
		if	($this->debug_cache_on == false)
		{
			$cacheitem = call_user_func_array($create_item_func,explode(",", $params));

			# log the time
			if	($this->debug_cache_log == true)
			{
				$end		=	microtime(true);
				$elapsed	=	number_format(($end - $start),6,".","");
			}
		}
		# we need to check or set here
		else
		{
			$bFound			=	false;
			# get unique key
			$key			=	md5($this->cache_version.$cachetype.$cachekey);

			# see if we can use a different cache mechanism
			if	(CACHE_ENGINE == CACHE_ENGINE_APC)
			{
				$cacheitem	=	apc_fetch($key);

				# this could be a null object
				if	($cacheitem !== false)
				{
					$bFound		=	true;
				}
			}
			else
			{
				$cacheitem	=	$this->memcache->get($key);


				# check to see if the key exists
				if ($this->memcache->add($key,0) == false)
				{
					$bFound			=	true;
				}
				else
				{
					# the key does not exist, so we can delete the test case
					$this->memcache->delete($key);
				}

			}

			# in the cache already?
			if (($bFound == false) || ($bforce == true))
			{
				$cacheitem = call_user_func_array($create_item_func,explode(",", $params));

				# check for null returned
				if	(empty($cacheitem))
				{
					# log the time
					if	($this->debug_cache_log == true)
					{
AddComment("cache_or_get $cache_used ZERO, nothing returned from $create_item_func couldnt set the item:$cachetype.$cachekey");
						return;
					}
				}

				if	(CACHE_ENGINE == CACHE_ENGINE_APC)
				{
					$cache_retval	=	apc_store($key, $cacheitem, $timeout);
				}
				else
				{
					$cache_retval	=	$this->memcache->set($key, $cacheitem, 0, $timeout);
				}

				# now set in the cache if possible
			    if ($cache_retval == false)
				{
					$s	=	strlen($cacheitem);
					# maybe we should log something here, or send an email to an administrator
AddComment("cache_or_get $cache_used MISS, couldn't set the item:$cachekey (length:$s)");
		        }
			}
			else
			{
				# for logging only
				$type	=	"GET";
			}
		}

		# log the time
		if	($this->debug_cache_log == true)
		{
			$end 		=	microtime(true);
			$elapsed	=	$end - $start;
			$elapsed	=	number_format($elapsed,6,".","");

			# if we have profiler active, log this
			if	($gProfiler)
			{
				$gProfiler->log("cache_or_get $cache_used $type TIME:$elapsed, item:$cachetype.$cachekey DURATION:$timeout");
			}
AddComment("cache_or_get $cache_used $type TIME:$elapsed, item:$cachetype.$cachekey DURATION:$timeout");

		}



		return	$cacheitem;
	}






	# caching uber function
	public	function cache_set($timeout,$cachetype,$cachekey,$cacheitem)
	{
		# this can be invoked if active for logging cache hits to debugger
		global	$gProfiler;

		if	(CACHE_ENGINE == CACHE_ENGINE_APC)
		{
			$cache_used	=	"APC";
		}
		else
		{
			$cache_used	=	"MEMCACHE";
		}

		# log the time
		if	($this->debug_cache_log == true)
		{
			$start	=	microtime(true);
		}

		# does cache exist and are we allowed to use it?
		if	($this->debug_cache_on == false)
		{
AddComment("cache_set $cache_used cannot because caching is set to OFF");
			return	false;
		}
		# we need to check or set here
		else
		{
			# get unique key
			$key			=	md5($this->cache_version.$cachetype.$cachekey);

			if	(CACHE_ENGINE == CACHE_ENGINE_APC)
			{
				$cache_retval	=	apc_store($key, $cacheitem, $timeout);
			}
			else
			{
				$cache_retval	=	$this->memcache->set($key, $cacheitem, 0, $timeout);
			}

			# now set in the cache if possible
		    if ($cache_retval == false)
			{
				$s	=	strlen($cacheitem);
				# maybe we should log something here, or send an email to an administrator
AddComment("cache_set $cache_used ERROR, couldn't set the item:$cachekey (length:$s)");
	        }

		}
		# log the time
		if	($this->debug_cache_log == true)
		{
			$end = microtime(true);
			$elapsed	=	$end - $start;
			$elapsed	=	round($elapsed,8);

			# if we have profiler active, log this
			if	($gProfiler)
			{
				$gProfiler->log("cache_set $cache_used TIME:$elapsed, item:$cachekey DURATION:$timeout");
			}

AddComment("cache_set $cache_used TIME:$elapsed, item:$cachekey DURATION:$timeout");

		}
	}






	# caching uber function
	public function cache_get($cachetype,$cachekey,&$cacheitem)
	{
		# this can be invoked if active for logging cache hits to debugger
		global	$gProfiler;

		# log the time
		if	($this->debug_cache_log == true)
		{
			$start	=	microtime(true);
		}

		if	(CACHE_ENGINE == CACHE_ENGINE_APC)
		{
			$cache_used	=	"APC";
		}
		else
		{
			$cache_used	=	"MEMCACHE";
		}

		$cacheitem	=	"";

		# does cache exist and are we allowed to use it?
		if	($this->debug_cache_on == false)
		{
AddComment("cache_get $cache_used cannot because caching is set to OFF");
			return	false;
		}
		# we need to check or set here
		else
		{
			# get unique key
			$key			=	md5($this->cache_version.$cachetype.$cachekey);

			# see if we can use a different cache mechanism
			if	(CACHE_ENGINE == CACHE_ENGINE_APC)
			{
				$cacheitem	=	apc_fetch($key);
			}
			else
			{
				$cacheitem	=	$this->memcache->get($key);
			}

			# now set in the cache if possible
			if ($cacheitem == false)
			{
				# maybe we should log something here, or send an email to an administrator
AddComment("cache_get $cache_used ERROR, couldn't set the item:$cachekey ");
	        }

		}
		# log the time
		if	($this->debug_cache_log == true)
		{
			$end = microtime(true);
			$elapsed	=	$end - $start;
			$elapsed	=	round($elapsed,8);

			# if we have profiler active, log this
			if	($gProfiler)
			{
				$gProfiler->log("cache_get $cache_used TIME:$elapsed, item:$cachekey");
			}

AddComment("cache_get $cache_used TIME:$elapsed, item:$cachekey");
		}

		return	true;

	}









	# caching uber function
	public function cache_delete($cachetype,$cachekey)
	{
		# this can be invoked if active for logging cache hits to debugger
		global	$gProfiler;

		# log the time
		if	($this->debug_cache_log == true)
		{
			$start	=	microtime(true);
		}

		if	(CACHE_ENGINE == CACHE_ENGINE_APC)
		{
			$cache_used	=	"APC";
		}
		else
		{
			$cache_used	=	"MEMCACHE";
		}

		# does cache exist and are we allowed to use it?
		if	($this->debug_cache_on == false)
		{
AddComment("cache_delete $cache_used cannot because caching is set to OFF");
			return	false;
		}
		# we need to check or set here
		else
		{
			# get unique key
			$key			=	md5($this->cache_version.$cachetype.$cachekey);

			if	(CACHE_ENGINE == CACHE_ENGINE_APC)
			{
				$cache_retval	=	apc_delete($key);
			}
			else
			{
				$cache_retval	=	$this->memcache->delete($key,0);
			}

			# now set in the cache if possible
		    if ($cache_retval == false)
			{
				# maybe we should log something here, or send an email to an administrator
AddComment("cache_delete $cache_used ERROR, couldn't remove the item:$cachekey ");
	        }
		}
		# log the time
		if	($this->debug_cache_log == true)
		{
			$end = microtime(true);
			$elapsed	=	$end - $start;
			$elapsed	=	round($elapsed,8);
AddComment("cache_delete cache_used TIME:$elapsed, item:$cachekey");
		}

	}























	# checks the cache and fetches
	function mysql_cache_check($query,&$cacheitem,$start)
	{
		# this can be invoked if active for logging cache hits to debugger
		global	$gProfiler;

		if	(MYSQL_CACHE_ENGINE == CACHE_ENGINE_APC)
		{
			$cache_used	=	"APC";
		}
		else
		{
			$cache_used	=	"MEMCACHE";
		}

		# does cache exist and are we allowed to use it?
		if	(MYSQL_CACHE == false)
		{
			if	(DEBUG_MYSQL == true)
			{
AddComment("mysql_cache_check $cache_used cannot because caching is set to OFF");
			}
			return	false;
		}
		# we need to check here
		else
		{
			$cache_status	=	"---";
			$cacheitem		=	"";
			$bFound			=	false;


			if ((MYSQL_CACHE) && (!empty($this->memcache)))
			{
				# see if we can use a different cache mechanism
				if	(MYSQL_CACHE_ENGINE == CACHE_ENGINE_APC)
				{
					if (apc_exists($query))
					{
						$cacheitem		=	apc_fetch($query);

						# this could be a null object
						if	($cacheitem !== false)
						{
							$bFound		=	true;
						}
						$cache_status	=	"GET";
					}
				}
				else
				{
					if	( (class_exists('Memcache')) && (!empty($this->memcache)))
					{
						# get unique key
						$key			=	md5($this->cache_version."mysql".$query);

						# get the actual data - if it exists
						$cacheitem		=	$this->memcache->get($key);

						$cache_status	=	"GET";

						# check to see if the key exists by adding (if the key already exists, will return false)
						if ($this->memcache->add($key,"") == false)
						{
							$bFound			=	true;
						}
						else
						{
							$bFound			=	false;
						}


/*

						# check to see if the key exists by adding (if the key already exists, will return false)

						# cm 19/09/2015 - checking should require a GET, not an ADD
						#						if ($this->memcache->get($key) == true)
						if ($this->memcache->add($key,$cacheitem) == false)
						{
							$bFound			=	true;
						}
						else
						{
							$this->memcache->delete($key);
						}
*/


					}
				}



				$end 		=	microtime(true);
				$elapsed	=	$end - $start;
				$elapsed	=	number_format($elapsed,6,".","");

				#
				if ($bFound == false)
				{
					if	(DEBUG_MYSQL == true)
					{
					# maybe we should log something here, or send an email to an administrator
AddComment("mysql_cache_check action:$cache_status, $cache_used MISS, couldn't get the item:$query ");
					}
					# maybe we should log something here, or send an email to an administrator
					return false;
				}

				# if we have profiler active, log this
				if	($gProfiler)
				{
					$gProfiler->log("mysql_cache_check action:$cache_status, $cache_used TIME:$elapsed, item:$query");

				}


				return	true;
			}
		}
		return false;
	}





	# stores the data for this amount of time
	function mysql_cache_store($query,$data,$start,$time=MYSQL_CACHE_TIME_TINY)
	{
		# this can be invoked if active for logging cache hits to debugger
		global	$gProfiler;

		if	(MYSQL_CACHE_ENGINE == CACHE_ENGINE_APC)
		{
			$cache_used	=	"APC";
		}
		else
		{
			$cache_used	=	"MEMCACHE";
		}

		# does cache exist and are we allowed to use it?
		if	((MYSQL_CACHE == false) && (DEBUG_MYSQL == true))
		{
AddComment("mysql_cache_store $cache_used cannot because caching is set to OFF");
			return	false;
		}
		# we need to check here
		else
		{
			if ((MYSQL_CACHE) && (!empty($this->memcache)))
			{
				if	(MYSQL_CACHE_ENGINE == CACHE_ENGINE_APC)
				{
					$cache_retval	=	apc_store($query, $data, $time);
				}
				else
				{

					# get unique key
					$key			=	md5($this->cache_version."mysql".$query);
					$cache_retval	=	$this->memcache->set($key, $data, 0, $time);
				}

				# now set in the cache if possible
				if ($cache_retval == false)
				{
					if	(DEBUG_MYSQL == true)
					{
					# maybe we should log something here, or send an email to an administrator
AddComment("mysql_cache_store $cache_used ERROR, couldn't set the item:$query ");
					}
				}
			}
			# log the time
			if	($this->debug_cache_log == true)
			{
				$end 		= 	microtime(true);
				$elapsed	=	$end - $start;
				$elapsed	=	round($elapsed,6);
				# if we have profiler active, log this
				if	($gProfiler)
				{
					$gProfiler->log("mysql_cache_store $cache_used  TIME:$elapsed, item:$query DURATION:$time");
				}
				if	(DEBUG_MYSQL == true)
				{
AddComment("mysql_cache_store $cache_used TIME:$elapsed, item:$query DURATION:$time");
				}
			}
		}

	}







	# stores the data for this amount of time
	function mysql_cache_delete($query)
	{
		# this can be invoked if active for logging cache hits to debugger
		global	$gProfiler;

		# log the time
		if	($this->debug_cache_log == true)
		{
			$start	=	microtime(true);
		}

		if	(MYSQL_CACHE_ENGINE == CACHE_ENGINE_APC)
		{
			$cache_used	=	"APC";
		}
		else
		{
			$cache_used	=	"MEMCACHE";
		}

		# does cache exist and are we allowed to use it?
		if	(MYSQL_CACHE == false)
		{
			if	(DEBUG_MYSQL == true)
			{
AddComment("mysql_cache_store $cache_used cannot because caching is set to OFF");
		}
			return	false;
		}
		# we need to check here
		else
		{
			if ((MYSQL_CACHE) && (!empty($this->memcache)))
			{
				# get unique key
				$key			=	md5($this->cache_version."mysql".$query);


				if	(MYSQL_CACHE_ENGINE == CACHE_ENGINE_APC)
				{
					$cache_retval	=	apc_delete($key);
				}
				else
				{
					$cache_retval	=	$this->memcache->delete($key,0);
				}

				# now set in the cache if possible
				if ($cache_retval == false)
				{
					# maybe we should log something here, or send an email to an administrator
					if	(DEBUG_MYSQL == true)
					{
AddComment("cache_delete $cache_used ERROR, couldn't remove the item:$query ");
					}
				}
			}
			# log the time
			if	($this->debug_cache_log == true)
			{
				$end = microtime(true);
				$elapsed	=	$end - $start;
				$elapsed	=	round($elapsed,8);
				if	(DEBUG_MYSQL == true)
				{
AddComment("mysql_cache_delete $cache_used TIME:$elapsed, item:$query");
				}
				if	($gProfiler)
				{
					$gProfiler->log("mysql_cache_delete $cache_used item:$query");
				}
			}

		}
	}



























}












