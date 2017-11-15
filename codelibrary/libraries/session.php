<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 25/08/14
 * Time: 19:22
 */
# we should be able to overload some variables and functions in this basic class soon

class Session_Library
{
	protected	$data;
	public		$table				=	"";
	protected	$session_id			=	"";
	protected	$logged_in_url		=	"";
	protected	$logged_out_url		=	"";
	protected	$login_url			=	"";
	protected	$registered_url		=	"";


	# we probably need accessors to set the dbase table
	public function __construct($params=array())
	{
		$this->data				=	"";
		$this->session_id		=	(isset($params['session_id']))		?	$params['session_id']		:	"";
		$this->table			=	(isset($params['table']))			?	$params['table']			:	"";
		$this->logged_out_url	=	(isset($params['logged_out_url']))	?	$params['logged_out_url']	:	"";
		$this->logged_in_url	=	(isset($params['logged_in_url']))	?	$params['logged_in_url']	:	"";
		$this->login_url		=	(isset($params['login_url']))		?	$params['login_url']		:	"";
		$this->registered_url	=	(isset($params['registered_url']))	?	$params['registered_url']	:	"";

		session_start();
	}

	public function getLoggedinURL()
	{
		return $this->logged_in_url;
	}


	public function gotoLoggedinURL()
	{
		$this->gotoURL($this->logged_in_url);

	}

	public function setSessionID($session_id)
	{
		$this->session_id	=	$session_id;
	}


	# sets session data
	public function setSessionVar($variable_name,$data)
	{
		$_SESSION[$this->session_id][$variable_name]	=	$data;
	}

	# gets session variable
	public function getSessionVar($variable_name="")
	{
		if	(!empty($variable_name))
		{
			if	($this->isSessionVarSet($variable_name) == true)
			{
				return $_SESSION[$this->session_id][$variable_name];
			}
		}
		else
		{
			return $_SESSION[$this->session_id];
		}
	}




	# sets session data
	public function setSessionDataVar($variable_name,$data)
	{
		$_SESSION[$this->session_id]['data'][$variable_name]	=	$data;
	}

	# gets session variable
	public function getSessionDataVar($variable_name="")
	{
		if	(!empty($variable_name))
		{
			if	($this->isSessionDataVarSet($variable_name) == true)
			{
				return $_SESSION[$this->session_id]['data'][$variable_name];
			}
		}
		else
		{
			return $_SESSION[$this->session_id]['data'];
		}
	}


	public function isSessionDataVarSet($variable_name)
	{
		return isset($_SESSION[$this->session_id]['data'][$variable_name]);
	}






	# returns session id
	public function getSessionID()
	{
		return $this->session_id;
	}




	public function isSessionVarSet($variable_name)
	{
		return isset($_SESSION[$this->session_id][$variable_name]);
	}



	# logs in this person and stores the data
	public function loginUser($email,$password)
	{
		if	($this->login($email,$password) == true)
		{
			$this->setSessionVar("data",$this->data);
			$this->setSessionVar("email",$email);

			return true;
		}

		return false;
	}




	# logs in this person or returns error
	public function login($email,$password)
	{
		global	$gMysql;
		$bCorrect	=	false;

		# grab user data
		if	(($this->data	=	$gMysql->queryRow("select * from " . $this->table	.	" where email='$email'",__FILE__,__LINE__)))
		{
			# we need to check if they are verified.
			if	($this->data['active'] == 0)
			{
				$bCorrect 	=	false;
			}
			# now check the type of password
			if (hash_equals($this->data['password_hash'], crypt($password, $this->data['password_hash'])) )
			{
				$bCorrect	=	true;
			}
			# **** revert to the old method temporarily ****
			else if	(strcasecmp($password,$this->data['password']) == 0)
			{
				$bCorrect	=	true;
			}
		}

		return  $bCorrect;
	}


	# check if I am logged in
	public function isLoggedIn()
	{
		# we can probably do some keep-alive timeout here
		if	($this->isSessionVarSet("data"))
		{
			return true;
		}
	}



	# handles the post
	public function loginCheck()
	{
		# check if we are logged in
		if	($this->isLoggedIn($this->session_id) == true)
		{
			return true;
		}
		else
		{
			$this->gotoURL($this->login_url);
		}
	}





	# handles the post
	public function logout()
	{
		if	(isset($_SESSION[$this->session_id]))
		{
			unset($_SESSION[$this->session_id]);
		}

		$this->gotoURL($this->logged_out_url);

	}



	# just logs in and goes to the specific URL
	public function gotoURL($url)
	{
		$headerURL	=	"Location: " .	$url .	"";
		header($headerURL);
		exit();
	}


	# we can initiallize it as a string, but we can also do an array version later
	public function postURL($url,$post_data_string)
	{
		file_get_contents($url.$post_data_string);
		exit();
	}







	# lost password
	public function checkEmailPassword($email)
	{
		global	$gMysql;

		$strSQL	=	"select * from " . $this->table ." where email='$email' ";

		if	(($data	=	$gMysql->queryRow($strSQL,__FILE__,__LINE__)))
		{
			$email	=	$data['email_address'];

		}

		#
		else
		{
		}
	}


	public function getUserName()
	{
		if	($this->isLoggedIn() == true)
		{
			return $this->getSessionDataVar("name");
		}
	}


	# check if I am logged in
	public function isAdmin()
	{
		if	($this->isLoggedIn() == true)
		{
			$value 	=	$this->getSessionDataVar("admin");

			if	($value == 1)
			{
				return true;
			}
		}
	}





}