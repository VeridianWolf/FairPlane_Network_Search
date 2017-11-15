<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 17/05/14
 * Time: 23:39
 */




# This controller routes all incoming requests to the appropriate controller
//Automatically includes files containing classes that are called
function autoloadMVC($className)
{

	//parse out filename where class should be located
	$filename	=	reverse_strrchr($className,'_');
	$suffix		=	substr(strrchr($className, '_'), 1);

#	list($filename , $suffix) = explode('_' , $className);
	$filename	=	strtolower($filename);
	//select the folder where class should be located based on suffix
	switch (strtolower($suffix))
	{
		case 'controller':
			$file = __SITE_PATH . '/mvc/'. $filename	.	'/controller_' . $filename  .	'.php';
			break;

		case 'view':
			$file = __SITE_PATH . '/mvc/'. $filename	.	'/view_' . $filename  .	'.php';
			break;

		case 'model':
			$file = __SITE_PATH . '/mvc/'. $filename	.	'/model_' . $filename  .	'.php';
			break;

		case 'library':
			# special case for shared code library in root
			$folder = '/../../libraries/';
			$file = dirname(__FILE__) . $folder . $filename . '.php';

			break;

		case 'class':

			$folder = '/includes/classes/';
			$file = __SITE_PATH . $folder . $filename . '.class.php';

			break;

		default:
			$folder	=	'';
			$file	=	'';

	}

	if	(class_exists($className))
	{
		return;
	}

	//fetch file
	if (file_exists($file))
	{
		//get file
		include_once($file);
	}
	else
	{
		//file does not exist!
	//	die("File '$filename' containing class '$className' not found in '$folder'.");
	}
}


spl_autoload_register("autoloadMVC");

