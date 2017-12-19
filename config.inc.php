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






$gPopupLookUp	=
    array(


        "IDNotFound" 		=> array("html" =>	"
		$.alert({
			
				title: 'Thank You',
				confirmButton: 'Ok',
				content: 'Thank you for registering. An account activation link has been sent to your email address.',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'material',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
				escapeKey: true,
				backgroundDismiss: true,
		
				theme: 'material',
				animation: 'scale',
				type: 'green',
				typeAnimated: 'true',
							

    			});

				"),
        "passwordSent" 		=> array("html" =>	"
		$.alert({
			
				title: 'Thank You',
				confirmButton: 'Ok',
				content: 'Your password change link has been sent. Please use it to create a new password for your account.',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'material',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
				escapeKey: true,
				backgroundDismiss: true,
		
				theme: 'material',
				animation: 'scale',
				type: 'green',
				typeAnimated: 'true',
							

    			});

				"),

        "passwordChanged" 		=> array("html" =>	"
		$.alert({
			
				title: 'Thank You',
				confirmButton: 'Ok',
				content: 'Your password has been changed. Please use it to log in to your account.',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'material',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
				escapeKey: true,
				backgroundDismiss: true,

		
				theme: 'material',
				animation: 'scale',
				type: 'green',
				typeAnimated: 'true',
	
    			});

				"),
        "loginProblem" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'Error',
				confirmButton: 'Ok',
				content: 'Your login details were not recognised.<br>Please try again.',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'material',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,
    			
		
				theme: 'material',
				animation: 'scale',
				type: 'red',
				typeAnimated: 'true',
	    			
    			
    			});

				"),
        "loggedOut" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'Thank You',
				confirmButton: 'Ok',
				content: 'You have now been logged out.',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'material',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,

		
				theme: 'material',
				animation: 'scale',
				type: 'green',
				typeAnimated: 'true',
	
    			});

				"),

        "contactSent" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'Thank You',
				confirmButton: 'Ok',
				content: 'Your message has been received.<br>We will be in touch shortly',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'material',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,
    			
		
				theme: 'material',
				animation: 'scale',
				type: 'green',
				typeAnimated: 'true',
	    			
    			
    			});

				"),

        "activateOK" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'Thank You',
				confirmButton: 'Ok',
				content: 'Your account has been activated.<br>You may now login',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'bootstrap',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,
    			
		
				theme: 'material',
				animation: 'scale',
				type: 'green',
				typeAnimated: 'true',
	    			
    			
    			});

				"),

        "activateInvalid" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'A Problem has occured',
				confirmButton: 'Ok',
				content: 'Your code has expired or is invalid.<br>Please request another.',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'bootstrap',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,
    			
		
				theme: 'material',
				animation: 'scale',
				type: 'red',
				typeAnimated: 'true',
	    			
    			
    			});

				"),



        "linkExpired" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'A Problem has occured',
				confirmButton: 'Ok',
				content: 'Your link has expired or is invalid.<br>Please request another.',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'bootstrap',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,
    			
		
				theme: 'material',
				animation: 'scale',
				type: 'red',
				typeAnimated: 'true',
	    			
    			});

				"),




        "activated" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'Thank you',
				confirmButton: 'Ok',
				content: 'Your account has been activated already.<br>You may now login',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'bootstrap',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,
    			
		
				theme: 'material',
				animation: 'scale',
				type: 'green',
				typeAnimated: 'true',
	    			
    			
    			});

				"),




        "claimError" 		=> array("html" =>	"
		$.alert({
		
			
				title: 'A Problem has occured',
				confirmButton: 'Ok',
				content: 'There was an error in the policy reference supplied.<br>Please select a policy to view',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
 	 			theme: 'bootstrap',
				confirmButtonClass: 'btn-success',
				cancelButtonClass: 'btn-danger',
				confirmButton: 'Ok',
				cancelButton: 'Cancel',
    			escapeKey: true,
    			
		
				theme: 'material',
				animation: 'scale',
				type: 'red',
				typeAnimated: 'true',
	    			
    			
    			});

				"),





        "error" 		=> array("html" =>	"
		$.alert({
				title: 'Error',
				confirmButton: 'Ok',
				content: 'You seem to have arrived here in error',
 				closeIcon: true,
    			closeIconClass: 'fa fa-close',
    			animationSpeed: 200,
    			});

				"),





    );






