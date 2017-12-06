<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cedric
 * Date: 14/10/13
 * Time: 17:20
 * To change this template use File | Settings | File Templates.
 */
$gMysql	=	NULL;

/*** error reporting on ***/
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('html_errors', true);
set_time_limit(90000);
/*** define the site path constant ***/
$site_path = realpath(dirname(__FILE__));
define ('__SITE_PATH', $site_path);
define ('SERVER_ROOT', $_SERVER['DOCUMENT_ROOT']);
define ('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . "/app/");

# domain name
$domain_path = $_SERVER['HTTP_HOST'];

# logfile global
$gLogfile						=	'logfile.log';


# defines if we echo add comment data
define	('DEBUG_ADD_COMMENT',	true);
# my pqp debug which also logs mysql queries and EXPLAINS them
define	('DEBUG_PROFILER',		false);

# minifies the loaded templates HTML
define	('DEBUG_MINIFY',		false);
# set this to turn on caching
define	('DEBUG_CACHE_ON',		false);
# debug will log all queries to file and EXPLAIN them
define	('DEBUG_MYSQL',			false);





# new caching modes
define	('CACHE_ENGINE_MEMCACHE',			0);
define	('CACHE_ENGINE_APC',				1);
define	('CACHE_ENGINE_FILE',				3);

# default engine (at present, APC is causing issues - ** 11/03/2014 ** since front controller method used)
define	('CACHE_ENGINE',				CACHE_ENGINE_MEMCACHE);


# cache mysql queries
define	('MYSQL_CACHE',					false);
define	('MYSQL_CACHE_ENGINE',			CACHE_ENGINE_MEMCACHE);

define	('MYSQL_LOG_FILE',				$site_path . '/logfile_mysql.txt');
define	('MYSQL_HOST',					'localhost');
define	('MYSQL_USER',					'7apO3nBbB2a');
define	('MYSQL_PASS',					'PIKkip91sD312');
define	('MYSQL_DBASE',					'newfairp_website');

# set if we want to debug mysql queries and store in MYSQL_LOG_FILE
define	('MYSQL_CACHE_TIME_TINY',		30);
define	('MYSQL_CACHE_TIME_NORMAL',		5*60);
define	('MYSQL_CACHE_TIME_LONG',		60*60);
define	('MYSQL_CACHE_TIME_HUGE',		24*60*60);
define	('MYSQL_CACHE_TIME_INFINITE',	7*24*60*60);
define	('MYSQL_CACHE_TIME_BEYOND',		60*24*60*60);
