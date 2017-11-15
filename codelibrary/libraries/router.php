<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 27/02/14
 * Time: 17:10



 TODO:

 1. error handling for 404 and missing controller / actions (maybe simple error displays)
 2. ability to manually dispatch by POST / GET
 3. manual database page layer for simpler websites without mysql layer
 4. post-loading of images (change of slider)


 */

class Router_Library
{

	private	$routes_map		=	array();

	# the controller we will use
	private	$controller;
	# the action we will pass to the controller
	private	$action;
	# the post/get method
	private	$method;
	# pattern matched within the route map
	private	$pattern;
	# parameters from the URL
    private	$params;
    # this version of the params features ONLY the ones asked for
    private	$router_params;
    # this is the route chosen
    private	$route;
    # full file path of the controller
    private $controller_file;
    # class of the controller
	private $controller_class;
    # full file path of the controller
    private $view_file;
    # class of the controller
	private $view_class;










	# pass the route map (we should have the whole string and break it down)
	public function __construct(array $routes_map = null)
    {
    	$this->routes_map	=	$routes_map;
	}






	# adds aroute to the list
	public function addRoute(array $route)
	{
		$this->routes_map[]	=	$route;
	}

	# sets the URI
	private function setURI($uri)
	{
		$_REQUEST['uri']	=	$uri;
	}


	# passes the whole message to the dispatcher
	public function dispatchError($uri,$error_title,$error_message,$error_debug)
	{
		$uri	=	ltrim($uri,"/");

		if	(($route	=	$this->getRoute($uri)))
		{
    		$route['passed_params']	=	array_merge($route['passed_params'],array(
    										"error_title"	=>	$error_title,
    										"error_message"	=>	$error_message,
    										"error_debug"	=>	$error_debug,

    										));

			$this->setRoute($route);

			http_response_code('404');
#			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
#			header( 'HTTP/1.1 301 Moved Permanently' );
			# now we need to execute
			$this->executeRoute();
		}
	}



	# dispatch if possible
	public function dispatch($url)
	{
		$url	=	ltrim($url,"/");

		# get a route array
		if ($this->getRoute($url))
		{
			# extracts variables needed from url
			$this->extractVariables();

			# now we need to execute
			$this->executeRoute();
		}
		else
		{
			# we should dispatch an error here
			$this->dispatchError( "/error/404/route","Page Not Found","The page you have requested does not exist or is no longer available","Route $url not found"  );
		}
	}






	# finds the rout and prepares the variables needed for dispatch
	private function getRoute($uri)
	{
		# lowercase
    	$method		=	strtolower($_SERVER['REQUEST_METHOD']);

    	# parse through the routes for exact matches
 		foreach	($this->routes_map	as	$route)
 		{
        	# specific method
        	if (strcmp($uri,$route['pattern']) == 0)
			{
				if	(strcmp($method,$route['method']) == 0)
				{
					# we may need to also match the action function


					$this->setRoute($route);
    	    		return	$route;
				}
			}
		}



		# parse through the routes
		foreach	($this->routes_map	as	$route)
		{
			# post or get method (plus 'any')
			if	( ($method == strtolower($route['method']) ||  strtolower($route['method']) == "") )
			{
				if (strcmp($uri,$route['pattern']) == 0)
				{
					$this->setRoute($route);
					return	$route;
				}
			}
		}



	}


	# setup route
	public function setRoute($route)
	{
		$this->controller		=	$route['controller'];
		$this->action			=	$route['action'];
		$this->method			=	$route['method'];
		$this->pattern			=	$route['pattern'];
    	$this->params     		= 	array();
    	$this->route     		= 	$route;
		# compose file name of controller
		$this->controller_file 	=	__SITE_PATH . '/mvc/'. strtolower($this->controller)	.	'/controller_' . strtolower($this->controller)  .	'.php';
		$this->controller_class	=	$this->controller	.  "_Controller";

		# compose file name of view
		$this->view_file 		=	__SITE_PATH . '/mvc/'. strtolower($this->controller)	.	'/view_' . strtolower($this->controller)  .	'.php';
		$this->view_class		=	$this->controller	.  "_View";


	}



	# extracts all the variables needed, ready for dispatch
	private function extractVariables()
	{
		# grab the pair and store indexed
		foreach ($_REQUEST as $key => $value)
		{
			# we can go a step further and only accept certain variables by using the the 'params_to_extract' array
			$this->route['passed_params'][ $key ]	=	 urldecode((string)$value);
		}
/*
 *  This may be used if looking for EXACT variables and ONLY supplying those to the router
 *
		# append the params we are passing by default
	   	foreach	($this->route['passed_params'] as $key => $value)
		{
			$this->params[ $key ] = urldecode($value);
		}



		# This part is EXTRA - go through param names and match them with variables
	   	foreach	($this->route['params'] as $param_index => $param_name)
		{
			# if we have a specific variable we need
			if	(strpos($param_name, ':') === 0)
       		{
       			# actual variable name we need,
       			$param_name							=	substr($param_name, 1);
       			# store this variable in the router list
				$this->router_params[$param_name]	=	$this->params[$param_name];
			}
			else
			{
				# set variables if necessary
				if	(isset($this->params[$param_name]))
				{
					$this->router_params[$param_name]	=	$this->params[$param_name];
				}
			}
		}
*/

	$a = 1;
	}






	# we need to execute..
	public function executeRoute()
	{
		# now we need to execute if we can validate route
		if	($this->validateRoute() == true)
		{
			# pass two variables and call a function after construction
			call_user_func_array(array(new $this->controller_class($this->route,$this->view_class),$this->action), array());
		}
	}






	# validates if this route can be taken
	public function validateRoute()
	{
		try
		{
		    $this->validateController();
		}
		catch ( Exception $e )
		{
			$this->dispatchError( "/error/404/controller","Page Not Found","The page you have requested does not exist or is no longer available","Controller $this->controller_class is not valid or missing"  );
		}



		try
		{
		    $this->validateAction();
		}
		catch ( Exception $e )
		{
			$this->dispatch( '/error/404/action',"Page Not Found","The page you have requested does not exist or is no longer available","Function $this->controller_class is not valid or missing"  );
		}



		try
		{
		    $this->validateView();
		}
		catch ( Exception $e )
		{
			$this->dispatch( '/error/404/view',"Page Not Found","The page you have requested does not exist or is no longer available","View $this->controller_class is not valid or missing"  );
		}



		return true;
/*
		if	($this->validateController() == true)
		{
			if	($this->validateAction() == true)
			{
				return true;
			}
		}
*/
	}






	# validates the controller exists
	private function validateController()
	{
		# check for the controller file... we should exit with a 404 here really
		if (file_exists($this->controller_file))
		{
			return true;
		}

		throw new Exception("Controller $this->controller_class is not valid or missing ");
	}





	# validate the action exists
	private function validateAction()
	{
		# now check this function exists
 		if (method_exists($this->controller_class,$this->action))
		{
			return true;

		}

		throw new Exception("Action $this->action is not valid or missing for controller:$this->controller_class");
	}







	# validates the view exists
	private function validateView()
	{
		# check for the view file... we should exit with a 404 here really
		if (file_exists($this->view_file))
		{
			return true;
		}

		throw new Exception("View $this->view_class is not valid or missing ");
	}




	# creates a routes map based on the database
	public function createRoutesMap($page="sm_page")
	{
		global	$gMysql;
		# grab all the page data
		$page_data_array		=	$gMysql->selectToArray("select * from $page where status='A' ",__FILE__,__LINE__,MYSQL_CACHE_TIME_HUGE);

		foreach ($page_data_array as $page_data)
		{
			# now extract the basics
			$mvc_header 			=	$page_data['mvc_header'];
			$mvc_footer 			=	$page_data['mvc_footer'];
			$mvc_controller 		=	$page_data['mvc_controller'];
			$mvc_method 			=	$page_data['mvc_method'];
			$mvc_action 			=	$page_data['mvc_action'];
			$mvc_template 			=	$page_data['mvc_template'];
			$mvc_pattern 			=	$page_data['ref'];
			# * new cm 03/06/
			if	(isset($page_data['admin_only']))
			{
				$mvc_admin_only			=	$page_data['admin_only'];
			}
			else
			{
				$mvc_admin_only			=	0;
			}


			$route					=	array(
				'pattern' =>	$mvc_pattern,
				'admin_only' =>	$mvc_admin_only,
				'passed_params' =>	array(
					'header'	=>	$mvc_header,
					'footer'	=>	$mvc_footer,
					'template'	=>	$mvc_template
				),
				'method'		=>	$mvc_method,
				'controller'	=>	$mvc_controller,
				'action'		=>	$mvc_action,
			);

			# merge both
			$this->addRoute($route);
		}
	}




	# creates a routes map based on the database
	public function addRoutes($routes="")
	{
		foreach ($routes as $route)
		{
			# merge both
			$this->addRoute($route);
		}
	}





	# finds the route
	public function getRouteFromPattern($pattern,$method)
	{
		# parse through the routes
		foreach	($this->routes_map	as	$route)
		{
			if (strcmp($pattern,$route['pattern']) == 0)
			{
				# post or get method (plus 'any')
				if	( ($method == strtolower($route['method']) ||  strtolower($route['method']) == "") )
				{
					return	$route;
				}
			}
		}
	}




}