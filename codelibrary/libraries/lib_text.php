<?php

#if(eregi($_SERVER["SCRIPT_NAME"], $_SERVER["REQUEST_URI"]))
#exit;

if ( !class_exists( "TEXT" ) )
{
	define	('NTOKEN_USER',1);
	define	('NTOKEN_TEAM',2);
	define	('NTOKEN_DRIVER',3);
	define	('NTOKEN_TEST_CAR_PART',4);
	define	('NTOKEN_MISSION_VALUE',5);
	define	('NTOKEN_MISSION_COMPLETE',6);


	define	('NTOKEN_ENGINE',7);
	define	('NTOKEN_CHASSIS',8);
	define	('NTOKEN_TYRES',9);
	define	('NTOKEN_TURNS',10);
	define	('NTOKEN_CREDITS',11);

	define	('NTOKEN_GLOBAL_POINTS',12);


	define	('NTOKEN_AMOUNT_1',13);
	define	('NTOKEN_AMOUNT_2',14);

	define	('NTOKEN_LEVEL',15);
	define	('NTOKEN_NAME',16);


	define	('NTOKEN_AMOUNT_3',17);
	define	('NTOKEN_AMOUNT_4',18);
	define	('NTOKEN_AMOUNT_5',19);
	define	('NTOKEN_AMOUNT_6',20);
	define	('NTOKEN_AMOUNT_7',21);
	define	('NTOKEN_AMOUNT_8',22);
	define	('NTOKEN_AMOUNT_9',23);

	define	('NTOKEN_TURN',24);
	define	('NTOKEN_LAP',25);

	define	('NTOKEN_ROUNDS',26);
	define	('NTOKEN_COMPS',27);
	define	('NTOKEN_DAYS',28);
	define	('NTOKEN_TIMES',29);
	define	('NTOKEN_TARGET_VALUE',30);
	define	('NTOKEN_STAT_NAME',31);


	# this is the driver class
	class	TEXT
	{

		var	$lines;



		var	$task_current_value;
		var	$task_percentage_complete;
		var	$task_template_value;

		var	$amount_1;
		var	$amount_2;
		var	$amount_3;
		var	$amount_4;
		var	$amount_5;
		var	$amount_6;
		var	$amount_7;
		var	$amount_8;
		var	$amount_9;
		var	$target_value;
		var	$stat_name;



		#---------------------------------------------------------------------------------------
		# returns a text string from the file with the label.
		function GetTextStringLanguage($label,$language_id=0)
		{
			if	(isset($_SERVER['HTTP_HOST']))
			{
				if	(strcasecmp($_SERVER['HTTP_HOST'],"localhost") == 0)
				{
			#		return	$label;
				}
			}


			# load the language
			$gLanguageString	=	cache_or_get(MEMCACHE_CACHE_TIME_BEYOND,"language","$language_id", "get_language_array","$language_id");



#AddGameComment("GetTextStringLanguage 2 $label language_id:$language_id");

			# this gets the text from array
			if	(isset($gLanguageString[$label]) == true)
			{
#AddComment("GetTextStringLanguage (language_id:$language_id GOT $label)");
				# this should do the replacement
      			return	$gLanguageString[$label];
			}
	      else
			{
#AddComment("MISSING TEXT ERROR:$label language_id:$language_id $num items cached ");
			}

#			$num	=	count($gLanguageString);
#AddGameComment("GetTextStringLanguage ERROR (language_id:$language_id found $num items cached ($label))");
			return	$label;

		}



		#---------------------------------------------------------------------------------------
		# returns a text string from the file with the label and also parses it.
		function GetParsedStringLanguage($label,$language_id=0)
		{
			return	$this->ReplaceTokens($this->GetTextStringLanguage($label,$language_id));
		}





		#---------------------------------------------------------------------------------------
		# returns a text string from the file with the label.
		function GetTextString($label)
		{
			global	$gLanguageString;

			# this gets the text from array
			if	(isset($gLanguageString[$label]) == true)
			{
				# this should do the replacement
      			return	$gLanguageString[$label];
			}
      else
      {
#AddComment("MISSING TEXT ERROR:$label");
      }

			return	$label;

		}



		#---------------------------------------------------------------------------------------
		# returns a text string from the file with the label and also parses it.
		function GetParsedString($label)
		{
			return	$this->ReplaceTokens($this->GetTextString($label));
		}





		#---------------------------------------------------------------------------------------
		# parses the text string and replaces tokens - we are takning for granted things like $team_id being yours
		function ReplaceTokens($string)
		{
			# two ways
			#	1. go thru list of tokens, doing the search/replace scan through whole list of tokens, then call the appropriate routine
			#	2. go thru the string, building the token (where it may be), then search for it

			if	(empty($string))
			{
				return	"";
			}

			$tokens	=	array(

								array	(		"[AMOUNT_1]",			NTOKEN_AMOUNT_1				),
								array	(		"[AMOUNT_2]",			NTOKEN_AMOUNT_2				),
								array	(		"[AMOUNT_3]",			NTOKEN_AMOUNT_3				),
								array	(		"[AMOUNT_4]",			NTOKEN_AMOUNT_4				),
								array	(		"[AMOUNT_5]",			NTOKEN_AMOUNT_5				),
								array	(		"[AMOUNT_6]",			NTOKEN_AMOUNT_6				),
								array	(		"[AMOUNT_7]",			NTOKEN_AMOUNT_7				),
								array	(		"[AMOUNT_8]",			NTOKEN_AMOUNT_8				),
								array	(		"[AMOUNT_9]",			NTOKEN_AMOUNT_9				),

							);


			$len	=	count($tokens);

			# loop thru string - find the match
			for	($i=0;$i<$len;$i++)
			{
				# call the function to replace $token$ at each point
				$token_string	=	$tokens[$i][0];
				$token			=	$tokens[$i][1];

				# go thru the list to find the correct callback
				if	(strpos($string,$token_string) === false)
				{
				}
				else
				{
					switch	($token)
					{
						case	NTOKEN_CREDITS:
								$string	=	str_replace($token_string, "$this->credits", $string);
								break;

						case	NTOKEN_AMOUNT_1:
								$string	=	str_replace($token_string, "$this->amount_1", $string);
								break;
						case	NTOKEN_AMOUNT_2:
								$string	=	str_replace($token_string, "$this->amount_2", $string);
								break;
						case	NTOKEN_AMOUNT_3:
								$string	=	str_replace($token_string, "$this->amount_3", $string);
								break;
						case	NTOKEN_AMOUNT_4:
								$string	=	str_replace($token_string, "$this->amount_4", $string);
								break;
						case	NTOKEN_AMOUNT_5:
								$string	=	str_replace($token_string, "$this->amount_5", $string);
								break;
						case	NTOKEN_AMOUNT_6:
								$string	=	str_replace($token_string, "$this->amount_6", $string);
								break;
						case	NTOKEN_AMOUNT_7:
								$string	=	str_replace($token_string, "$this->amount_7", $string);
								break;
						case	NTOKEN_AMOUNT_8:
								$string	=	str_replace($token_string, "$this->amount_8", $string);
								break;
						case	NTOKEN_AMOUNT_9:
								$string	=	str_replace($token_string, "$this->amount_9", $string);
								break;

						default:
								break;
					}
				}
			}

			return	$string;

		}

	}






}


?>
