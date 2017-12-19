<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cedric mcmillan
 * Date: 13/10/13
 * Time: 21:57
 * common functions.

 testing 1234
 *

 */

// Helper function(s) ...
define('X', "\x1A"); // a placeholder character
$SS = '"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'';
$CC = '\/\*[\s\S]*?\*\/';
$CH = '<\!--[\s\S]*?-->';
$TB = '<%1$s(?:>|\s[^<>]*?>)[\s\S]*?<\/%1$s>';
function __minify_x($input) {
	return str_replace(array("\n", "\t", ' '), array(X . '\n', X . '\t', X . '\s'), $input);
}
function __minify_v($input) {
	return str_replace(array(X . '\n', X . '\t', X . '\s'), array("\n", "\t", ' '), $input);
}
/***
 * =======================================================
 *  HTML MINIFIER
 * =======================================================
 * -- CODE: ----------------------------------------------
 *
 *    echo minify_html(file_get_contents('affiliate_data.html'));
 *
 * -------------------------------------------------------
 */
function _minify_html($input) {
	return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#', function($m) {
		if(isset($m[2])) {
			// Minify inline CSS declaration(s)
			if(stripos($m[2], ' style=') !== false) {
				$m[2] = preg_replace_callback('#( style=)([\'"]?)(.*?)\2#i', function($m) {
					return $m[1] . $m[2] . minify_css($m[3]) . $m[2];
				}, $m[2]);
			}
			return '<' . $m[1] . preg_replace(
					array(
						// From `defer="defer"`, `defer='defer'`, `defer="true"`, `defer='true'`, `defer=""` and `defer=''` to `defer` [^1]
						'#\s(checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped)(?:=([\'"]?)(?:true|\1)?\2)#i',
						// Remove extra white-space(s) between HTML attribute(s) [^2]
						'#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
						// From `<img />` to `<img/>` [^3]
						'#\s+\/$#'
					),
					array(
						// [^1]
						' $1',
						// [^2]
						' $1$2',
						// [^3]
						'/'
					),
					str_replace("\n", ' ', $m[2])) . '>';
		}
		return '<' . $m[1] . '>';
	}, $input);
}
function minify_html($input) {
	if( ! $input = trim($input)) return $input;
	global $CH, $TB;
	// Keep important white-space(s) after self-closing HTML tag(s)
	$input = preg_replace('#(<(?:img|input)(?:\s[^<>]*?)?\s*\/?>)\s+#i', '$1' . X . '\s', $input);
	// Create chunk(s) of HTML tag(s), ignored HTML group(s), HTML comment(s) and text
	$input = preg_split('#(' . $CH . '|' . sprintf($TB, 'pre') . '|' . sprintf($TB, 'code') . '|' . sprintf($TB, 'script') . '|' . sprintf($TB, 'style') . '|' . sprintf($TB, 'textarea') . '|<[^<>]+?>)#i', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	$output = "";
	foreach($input as $v) {
		if($v !== ' ' && trim($v) === "") continue;
		if($v[0] === '<' && substr($v, -1) === '>') {
			if($v[1] === '!' && substr($v, 0, 4) === '<!--') { // HTML comment ...
				// Remove if not detected as IE comment(s) ...
				if(substr($v, -12) !== '<![endif]-->') continue;
				$output .= $v;
			} else {
				$output .= __minify_x(_minify_html($v));
			}
		} else {
			// Force line-break with `&#10;` or `&#xa;`
			$v = str_replace(array('&#10;', '&#xA;', '&#xa;'), X . '\n', $v);
			// Force white-space with `&#32;` or `&#x20;`
			$v = str_replace(array('&#32;', '&#x20;'), X . '\s', $v);
			// Replace multiple white-space(s) with a space
			$output .= preg_replace('#\s+#', ' ', $v);
		}
	}
	// Clean up ...
	$output = preg_replace(
		array(
			// Remove two or more white-space(s) between tag [^1]
			'#>([\n\r\t]\s*|\s{2,})<#',
			// Remove white-space(s) before tag-close [^2]
			'#\s+(<\/[^\s]+?>)#'
		),
		array(
			// [^1]
			'><',
			// [^2]
			'$1'
		),
		$output);
	$output = __minify_v($output);
	// Remove white-space(s) after ignored tag-open and before ignored tag-close (except `<textarea>`)
	return preg_replace('#<(code|pre|script|style)(>|\s[^<>]*?>)\s*([\s\S]*?)\s*<\/\1>#i', '<$1$2$3</$1>', $output);
}
/**
 * =======================================================
 *  CSS MINIFIER
 * =======================================================
 * -- CODE: ----------------------------------------------
 *
 *    echo minify_css(file_get_contents('test.css'));
 *
 * -------------------------------------------------------
 */
function _minify_css($input) {
	// Keep important white-space(s) in `calc()`
	if(stripos($input, 'calc(') !== false) {
		$input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', function($m) {
			return $m[1] . preg_replace('#\s+#', X . '\s', $m[2]) . ')';
		}, $input);
	}
	// Minify ...
	return preg_replace(
		array(
			// Fix case for `#foo [bar="baz"]` and `#foo :first-child` [^1]
			'#(?<![,\{\}])\s+(\[|:\w)#',
			// Fix case for `[bar="baz"] .foo` and `url(foo.jpg) no-repeat` [^2]
			'#\]\s+#', '#\)\s+\b#',
			// Minify HEX color code ... [^3]
			'#\#([\da-f])\1([\da-f])\2([\da-f])\3\b#i',
			// Remove white-space(s) around punctuation(s) [^4]
			'#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
			// Replace zero unit(s) with `0` [^5]
			'#\b(?:0\.)?0([a-z]+\b|%)#i',
			// Replace `0.6` with `.6` [^6]
			'#\b0+\.(\d+)#',
			// Replace `:0 0`, `:0 0 0` and `:0 0 0 0` with `:0` [^7]
			'#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
			// Replace `background(?:-position)?:(0|none)` with `background$1:0 0` [^8]
			'#\b(background(?:-position)?):(0|none)\b#i',
			// Replace `(border(?:-radius)?|outline):none` with `$1:0` [^9]
			'#\b(border(?:-radius)?|outline):none\b#i',
			// Remove empty selector(s) [^10]
			'#(^|[\{\}])(?:[^\s\{\}]+)\{\}#',
			// Remove the last semi-colon and replace multiple semi-colon(s) with a semi-colon [^11]
			'#;+([;\}])#',
			// Replace multiple white-space(s) with a space [^12]
			'#\s+#'
		),
		array(
			// [^1]
			X . '\s$1',
			// [^2]
			']' . X . '\s', ')' . X . '\s',
			// [^3]
			'#$1$2$3',
			// [^4]
			'$1',
			// [^5]
			'0',
			// [^6]
			'.$1',
			// [^7]
			':0',
			// [^8]
			'$1:0 0',
			// [^9]
			'$1:0',
			// [^10]
			'$1',
			// [^11]
			'$1',
			// [^12]
			' '
		),
		$input);
}
function minify_css($input) {
	if( ! $input = trim($input)) return $input;
	global $SS, $CC;
	// Keep important white-space(s) between comment(s)
	$input = preg_replace('#(' . $CC . ')\s+(' . $CC . ')#', '$1' . X . '\s$2', $input);
	// Create chunk(s) of string(s), comment(s) and text
	$input = preg_split('#(' . $SS . '|' . $CC . ')#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	$output = "";
	foreach($input as $v) {
		if(trim($v) === "") continue;
		if(
			($v[0] === '"' && substr($v, -1) === '"') ||
			($v[0] === "'" && substr($v, -1) === "'") ||
			(substr($v, 0, 2) === '/*' && substr($v, -2) === '*/')
		) {
			// Remove if not detected as important comment ...
			if($v[0] === '/' && substr($v, 0, 3) !== '/*!') continue;
			$output .= $v; // String or comment ...
		} else {
			$output .= _minify_css($v);
		}
	}
	// Remove quote(s) where possible ...
	$output = preg_replace(
		array(
			'#(' . $CC . ')|(?<!\bcontent\:)([\'"])([a-z_][-\w]*?)\2#i',
			'#(' . $CC . ')|\b(url\()([\'"])([^\s]+?)\3(\))#i'
		),
		array(
			'$1$3',
			'$1$2$4$5'
		),
		$output);
	return __minify_v($output);
}
/**
 * =======================================================
 *  JAVASCRIPT MINIFIER
 * =======================================================
 * -- CODE: ----------------------------------------------
 *
 *    echo minify_js(file_get_contents('test.js'));
 *
 * -------------------------------------------------------
 */
function _minify_js($input) {
	return preg_replace(
		array(
			// Remove inline comment(s) [^1]
			'#\s*\/\/.*$#m',
			// Remove white-space(s) around punctuation(s) [^2]
			'#\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#',
			// Remove the last semi-colon and comma [^3]
			'#[;,]([\]\}])#',
			// Replace `true` with `!0` and `false` with `!1` [^4]
			'#\btrue\b#', '#false\b#', '#return\s+#'
		),
		array(
			// [^1]
			"",
			// [^2]
			'$1',
			// [^3]
			'$1',
			// [^4]
			'!0', '!1', 'return '
		),
		$input);
}
function minify_js($input) {
	if( ! $input = trim($input)) return $input;
	// Create chunk(s) of string(s), comment(s), regex(es) and
	global $SS, $CC;
	$input = preg_split('#(' . $SS . '|' . $CC . '|\/[^\n]+?\/(?=[.,;]|[gimuy]|$))#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	$output = "";
	foreach($input as $v) {
		if(trim($v) === "") continue;
		if(
			($v[0] === '"' && substr($v, -1) === '"') ||
			($v[0] === "'" && substr($v, -1) === "'") ||
			($v[0] === '/' && substr($v, -1) === '/')
		) {
			// Remove if not detected as important comment ...
			if(substr($v, 0, 2) === '//' || (substr($v, 0, 2) === '/*' && substr($v, 0, 3) !== '/*!' && substr($v, 0, 8) !== '/*@cc_on')) continue;
			$output .= $v; // String, comment or regex ...
		} else {
			$output .= _minify_js($v);
		}
	}
	return preg_replace(
		array(
			// Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}` [^1]
			'#(' . $CC . ')|([\{,])([\'])(\d+|[a-z_]\w*)\3(?=:)#i',
			// From `foo['bar']` to `foo.bar` [^2]
			'#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i'
		),
		array(
			// [^1]
			'$1$2$4',
			// [^2]
			'$1.$3'
		),
		$output);
}

function SetCommentFile($fn="logfile.log")
{
	global $gLogfile;

	if (isset($gLogfile))
	{
		$gLogfile	=	$fn;
	}
}

# logs info to logfile
function AddComment($comment = "",$fn="log_file.txt",$bFull=true,$line=__LINE__,$file=__FILE__)
{
	global $gLogfile;

	if	(DEBUG_ADD_COMMENT	== 0)
	{
		return;
	}

	if (isset($gLogfile))
	{
		$fn	=	$gLogfile;
	}



	$fn	=	__SITE_PATH	.	"/" .	$fn;

	if	(!file_exists("$fn"))
	{
		if (!$handle = fopen($fn, 'w+')) {  }
		// Write $logline to our logfile.
		if (fwrite($handle, "") === FALSE) {  } fclose($handle);
		chmod("$fn", 0777);
	}

	# Getting the information
	if	(array_key_exists('REMOTE_ADDR',$_SERVER))
	{
		$ipaddress		=	$_SERVER['REMOTE_ADDR'];
		$page			=	"http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";

		# add query if needed
		if	($bFull == true)
		{
			$page			.=	iif(!empty($_SERVER['QUERY_STRING']), "?{$_SERVER['QUERY_STRING']}", "");
		}
	}
	else
	{
		$ipaddress		=	"";
		$page			=	"";
	}

	$time			=	date("Y-m-d G:i:s");

	if (!$handle = fopen($fn, 'a+')) { echo("Failed to open addcomment log file $fn"); return;}

	if	(empty($comment))
	{
		$logline	=	$comment . "\n";
	}
	else if ($bFull == true)
	{
		// Create log line
		$logline	=	$time . '|' . $ipaddress . '|' . $page . '|' . $comment . "\n";
	}
	else
	{
		$logline	=	$time . $comment . "\n";
	}


	// Write $logline to our logfile.
	if (fwrite($handle, $logline) === FALSE) { echo("Failed to write to main log file"); } fclose($handle);

	global	$gProfiler;

	if	($gProfiler)
	{
		$logline	=	$time . "\n" . $page . "\n" . "COMMENT: ".$comment . "\n";

		$backtrace	=	debug_backtrace();
    	$last		=	$backtrace[0];
		$gProfiler->log($logline,$last['line'],$last['file']);
	}

}



function iif($expression, $returntrue, $returnfalse = '') {
	return ($expression ? $returntrue : $returnfalse);
}





# loads file
function load_template_file($fn="")
{
	chdir(__SITE_PATH);
#	$fn	=	TEMPLATES_DIR	.	$fn;

	$string	=	"";

	if	(!empty($fn))
	{
		if	(file_exists($fn))
		{
			$string	=	file_get_contents($fn);

			if	(DEBUG_MINIFY)
			{
				$string	=	minify_html($string);
			}
		}
	}

	return	$string;
}





# CHOICE, ARRAY[id,value]
function optionsArray($choice=0,$array)
{
	if(empty($choice))
	{
		$choice	=	0;
	}

	$select_box	=	"";

	# build the box
	$len	=	count($array);

	for	($i=0;$i<$len;$i++)
	{
		$select_box	.=	"<option value=\"{$array[$i][0]}\"";


		if ( $choice == $array[$i][0])
		{
			$select_box .= " selected";
		}

		$str	=	$array[$i][1];

		$select_box	.=	">{$str}</option>\n";
	}

	# end
	$select_box		.=	"</select>";

	return $select_box;
}


# returns an option via index of array ( array(index,value)...  )
function getOptionViaIndex($option,$array)
{
	foreach ($array as $item)
	{
		if (strcasecmp($item[0],$option) == 0)
		{
			return	$item[1];
		}
	}
}








# builds a dropdown select box
function buildSelectBox($choice_name,$choice_value,$value_array,$text_array,$class="",$label="",$default_choice="",$on_change="",$live_search=false,$multiple=false)
{

	$select_box		=	"";



	if	(!empty($label))
	{
		$select_box		.=	"<label for='". $choice_name ."'>$label</label>\n<br>";
	}

	$select_box	.=	"<select name='". $choice_name ."' id='". $choice_name ."' class='". $class ."'";

	# for https://silviomoreto.github.io/bootstrap-select/examples/
	if ($live_search == true)
	{
		$select_box	.=	" data-live-search='true'  ";
	}

	if ($multiple == true)
	{
		$select_box	.=	" multiple='multiple' style='width:100%' ";
	}


	if	(!empty($on_change))
	{
		$select_box	.=	" onchange='" . $on_change . "'>\n";
	}
	else
	{
		$select_box	.=	">\n";
	}

	if	(empty($choice_value))
	{
		$choice_value	=		$default_choice;
	}


	# build the box
	$len	=	count($value_array);

	for	($i=0;$i<$len;$i++)
	{
		$select_box	.=	"<option value='" . $value_array[$i] . "' ";

		if ( htmlspecialchars($choice_value) == htmlspecialchars($value_array[$i]) )
		{
			$select_box .= " selected";
		}

		$select_box	.=	">" . $text_array[$i] . "</option>\n";
	}

	$select_box .=	"</select>\n";

	return $select_box;
}





# this will get a form element from an array, or return the corresponding value from the database or -1
function GetVariable($variable_name,$from_array,$data_array="",$default="")
{
	if (array_key_exists($variable_name,$from_array))
	{
		return	(int)$from_array[$variable_name];
	}
	else
	{
		if (is_array($data_array) == true)
		{
			if	(array_key_exists($variable_name,$data_array))
			{
				return	(int)$data_array[$variable_name];
			}
		}
		else
		{
			return	(int)$data_array;
		}

	}
	return	(int)$default;
}





# this will get a form element from an array, or return the corresponding value from the database or -1
function GetVariableString($variable_name,&$from_array,$data_array="",$default="")
{
	if	(is_array($data_array) == false)
	{
		$data_array	=	array();
	}

	if (array_key_exists($variable_name,$from_array))
	{
		return	sql_quote($from_array[$variable_name]);
	}
	else if (array_key_exists($variable_name,$data_array)  && (is_array($data_array) == true) )
	{
		return	sql_quote($data_array[$variable_name]);
	}
	else 			#if	(!empty($default))
	{
		return	sql_quote("$default");
	}

	return	sql_quote("");
}




# make sure we have a myql pointer, or we're buggered
function sql_quote($value)
{
	# removes items such as javascript attacks
	$value	=	cleanInput($value);

    if(get_magic_quotes_gpc())
    {
      $value = nl2br($value);
	  $value = str_replace("\r\n","",$value);
      $value = stripslashes($value);
    }

/*
    // check if this function exists
    if(function_exists("mysql_real_escape_string"))
    {
      $value = mysql_real_escape_string($value);
    }
    // for PHP < 4.3.0 use addslashes
    else
    {
      $value = addslashes($value);
    }
*/

	# no mysql connection required
    $value = mysql_escape_mimic(transliterateString($value));

# changing the < to [ removes <br>
#	$value	= str_replace('<','[', $value);
#	$value	= str_replace('>',']', $value);
#	$value	= str_replace('(','[', $value);
#	$value	= str_replace(')',']', $value);

    return $value;
  }


function mysql_escape_mimic($inp) {
    if(is_array($inp))
        return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp;
}

function cleanInput($input)
{
$search = array(
    '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
    '@<style[^>]*?>.*?</style>@siU'     // Strip style tags properly
);

/*
#    '@<![\s\S]*?--[ \t\n\r]*>@',        // Strip multi-line comments
#   '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
*/
    $output = preg_replace($search, '', $input);



    return $output;
 }




# returns a full timestamp
function GetTimeStamp()
{
	# update the last action
	return	date("Y-m-d G:i:s");
}



# returns an offsetted timestamp days or minutes etc ahead etc
function GetOffsetTimeStamp($date, $shift="+1 days",$format="Y-m-d H:i")
{
	return date($format, strtotime($shift, strtotime($date)));
}


# seconds time since datetime (works over BST)
function time_since($datetime)
{
#	$datetime	=	"2016-04-14 12:52:00 BST";

	$datetime	.=	" BST";

	$now = new DateTime('now');
	$date = new DateTime($datetime);
	$diff = $now->getTimestamp() - $date->getTimestamp();
	return $diff;


	$datetime1 = date_create(GetTimeStamp());
	$datetime2 = date_create($datetime);
	$time_difference = date_diff($datetime1, $datetime2);
	$result = $time_difference->format('%%S');
	return	$result;

	$time_difference = abs(strtotime('now') - strtotime($datetime));
	return	$time_difference;
}


# days since datetime
function days_since($datetime)
{
	return	round((time_since($datetime)	/	(24 * 60 * 60)));
}






# strips crap from the html and compresses slightly
function stripBuffer(&$bff)
{
	$re = '%# Collapse whitespace everywhere but in blacklisted elements.
			(?>             # Match all whitespans other than single space.
			[^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
			| \s{2,}        # or two or more consecutive-any-whitespace.
			) # Note: The remaining regex consumes no text at all...
			(?=             # Ensure we are not in a blacklist tag.
			[^<]*+        # Either zero or more non-"<" {normal*}
			(?:           # Begin {(special normal*)*} construct
			<           # or a < starting a non-blacklist tag.
			(?!/?(?:textarea|pre|script)\b)
			[^<]*+      # more non-"<" {normal*}
			)*+           # Finish "unrolling-the-loop"
			(?:           # Begin alternation group.
			<           # Either a blacklist start tag.
			(?>textarea|pre|script)\b
			| \z          # or end of file.
			)             # End alternation group.
			)  # If we made it here, we are not in a blacklist tag.
		%Six';

	$bff = mb_ereg_replace($re, " ", $bff);


	$bff=str_replace(array(">\r</h",">\n</h"),"></h",$bff);
	$bff=str_replace(array("\r<u","\n<u"),"<u",$bff);
	$bff=str_replace(" &nbsp;","&nbsp;",$bff);
	$bff=str_replace("&nbsp; ","&nbsp;",$bff);
	$bff=str_replace(array("\r\r\r","\r\r","\r\n","\n\r","\n\n\n","\n\n"),"\n",$bff);
	$bff=str_replace(array(">\r<a",">\n<a"),"><a",$bff);
	$bff=str_replace(array(">\r<b",">\n<b"),"><b",$bff);
	$bff=str_replace(array(">\r<d",">\n<d"),"><d",$bff);
	$bff=str_replace(array(">\r<h",">\n<h"),"><h",$bff);
	$bff=str_replace(array(">\r<i",">\n<i"),"><i",$bff);
	$bff=str_replace(array(">\r<i",">\n<i"),"><i",$bff);
	$bff=str_replace(array(">\r<l",">\n<l"),"><l",$bff);
	$bff=str_replace(array(">\r<m",">\n<m"),"><m",$bff);
	$bff=str_replace(array(">\r<p",">\n<p"),"><p",$bff);
	$bff=str_replace(array(">\r<t",">\n<t"),"><t",$bff);
	$bff=str_replace(array(">\r</u",">\n</u"),"></u",$bff);
	$bff=str_replace(array(">\r</d",">\n</d"),"></d",$bff);
	$bff	= preg_replace ("/(^[40 ]+)|([40 ]+$)/m", "", $bff);
	$bff = preg_replace ("/^[40 12]+/m", "", $bff);
	/* carriage returns, new lines */
	$bff=str_replace(array("\r\r\r","\r\r","\r\n","\n\r","\n\n\n","\n\n"),"\n",$bff);
	/* tabs */
#	$bff=str_replace(array("\t\t\t","\t\t","\t\n","\n\t"),"\t",$bff);
	/* opening HTML tags */
	$bff=str_replace(array(">\r<a",">\r <a",">\r\r <a","> \r<a",">\n<a","> \n<a","> \n<a",">\n\n <a"),"><a",$bff);
	$bff=str_replace(array(">\r<b",">\n<b"),"><b",$bff);
	$bff=str_replace(array(">\r<d",">\n<d","> \n<d",">\n <d",">\r <d",">\n\n<d"),"><d",$bff);
	$bff=str_replace(array(">\r<f",">\n<f",">\n <f"),"><f",$bff);
	$bff=str_replace(array(">\r<h",">\n<h",">\t<h","> \n\n<h"),"><h",$bff);
	$bff=str_replace(array(">\r<i",">\n<i",">\n <i"),"><i",$bff);
	$bff=str_replace(array(">\r<i",">\n<i"),"><i",$bff);
	$bff=str_replace(array(">\r<l","> \r<l",">\n<l","> \n<l",">  \n<l","/>\n<l","/>\r<l"),"><l",$bff);
	$bff=str_replace(array(">\t<l",">\t\t<l"),"><l",$bff);
	$bff=str_replace(array(">\r<m",">\n<m"),"><m",$bff);
	$bff=str_replace(array(">\r<n",">\n<n"),"><n",$bff);
	$bff=str_replace(array(">\r<p",">\n<p",">\n\n<p","> \n<p","> \n <p"),"><p",$bff);
	$bff=str_replace(array(">\r<s",">\n<s"),"><s",$bff);
	$bff=str_replace(array(">\r<t",">\n<t"),"><t",$bff);
	/* closing HTML tags */
	$bff=str_replace(array(">\r</a",">\n</a"),"></a",$bff);
	$bff=str_replace(array(">\r</b",">\n</b"),"></b",$bff);
	$bff=str_replace(array(">\r</u",">\n</u"),"></u",$bff);
	$bff=str_replace(array(">\r</d",">\n</d",">\n </d"),"></d",$bff);
	$bff=str_replace(array(">\r</f",">\n</f"),"></f",$bff);
	$bff=str_replace(array(">\r</l",">\n</l"),"></l",$bff);
	$bff=str_replace(array(">\r</n",">\n</n"),"></n",$bff);
	$bff=str_replace(array(">\r</p",">\n</p"),"></p",$bff);
	$bff=str_replace(array(">\r</s",">\n</s"),"></s",$bff);
	/* other */
	$bff=str_replace(array(">\r<!",">\n<!"),"><!",$bff);
	$bff=str_replace(array("\n<div")," <div",$bff);
	$bff=str_replace(array(">\r\r \r<"),"><",$bff);
	$bff=str_replace(array("> \n \n <"),"><",$bff);
	$bff=str_replace(array(">\r</h",">\n</h"),"></h",$bff);
	$bff=str_replace(array("\r<u","\n<u"),"<u",$bff);
	$bff=str_replace(array("/>\r","/>\n","/>\t"),"/>",$bff);
	$bff=str_replace("> <","><",$bff);
	$bff=str_replace("  <","<",$bff);
	/* non-breaking spaces */
	$bff=str_replace(" &nbsp;","&nbsp;",$bff);
	$bff=str_replace("&nbsp; ","&nbsp;",$bff);
	$bff	= str_replace( "\t", "", $bff);
	$bff	= str_replace("  ", " ", $bff);
	$bff = str_replace(Chr(13), "", $bff);
	$bff=str_replace(array("name=\"select\" /><input"),"name=\"select\" /> <input",$bff);



	return $bff;
}

function clean_data($input)
{
    $input = trim(htmlentities(strip_tags($input,",")));

    if (get_magic_quotes_gpc())
        $input = stripslashes($input);

    $input = mysql_real_escape_string($input);

    return $input;
}



# checks the variable agianst bounds and clips it to ranges.
function boundsCheck(&$Variable,$Delta,$MinVariable,$MaxVariable)
{
	$bVar	=	false;

	$Variable += $Delta;

	if	($Variable < $MinVariable)
	{
		$Variable	=	$MinVariable;
		$bVar		=	true;
	}
	if	($Variable > $MaxVariable)
	{
		$Variable	=	$MaxVariable;
		$bVar		=	true;
	}
	return	$bVar;
}




// fully functional sendmail HTML file sending
function sendEmailNew($name, $email, $to_mail, $subject, $msg, $attachment = "")
{
	if	(IsWindows() == true)
	{
		return true;
	}


	require_once($_SERVER["DOCUMENT_ROOT"].			"/../codelibrary/includes/phpmailer/class.phpmailer.php");


	$mail = new PHPMailer();
	$mail->IsMail();

	$mail->From		=	$email;
	$mail->FromName	=	$name;
	$mail->Sender	=	$email;

	$mail->AddReplyTo($email, $name);

	$mail->AddAddress($to_mail);
	$mail->Subject = $subject;

	$mail->IsHTML(true);
	$mail->Body = $msg;
	$mail->AltBody="";


	if (!empty($attachment))
	{
		$file		=	$attachment;
		$content	=	file_get_contents($file);
		$content	=	chunk_split(base64_encode($content));
		$uid		=	md5(uniqid(time()));
		$f_name		=	$attachment;


	    $mail->AddAttachment($attachment,
	    					$_FILES['file']['name']);
#                        	 $_FILES['fileUpload']['name']);

	}

	if(!$mail->Send())
	{
	   echo "Error sending: " . $mail->ErrorInfo;
AddComment("Error sending: " . $mail->ErrorInfo);
	}
	else
	{
		return	true;
	}
}





# sends email with an attachment
function sendEmailLocal($name, $email, $to_mail, $subject, $msg, $attachment = "")
{
	require_once($_SERVER["DOCUMENT_ROOT"]	.	"/../codelibrary/includes/phpmailer/PHPMailerAutoload.php");

	if (!empty($name) && !empty($email) && !empty($to_mail) && !empty($subject) && !empty($msg)) {
		$sending = true;
	}

	$from_name = $name;
	$from_mail = $email;


	$mail = new PHPMailer;

	$mail->SetLanguage( 'en', 'phpmailer/language/' );

	$mail->isSMTP();                            // Set mailer to use SMTP
	$mail->Host = 'mail.boxlegal.co.uk';             // Specify main and backup SMTP servers
	$mail->SMTPAuth = true;                     // Enable SMTP authentication
	$mail->Username = 'website@boxlegal.co.uk';          // SMTP username
	$mail->Password = 'catch22'; // SMTP password
	$mail->SMTPSecure = 'tls';                  // Enable TLS encryption, `ssl` also accepted
	$mail->Port = 25;                          // TCP port to connect to

	$mail->setFrom($from_mail, $from_name);
	$mail->addReplyTo($from_mail, $from_name);
	$mail->addAddress($to_mail);   // Add a recipient
	#	$mail->addCC('cc@example.com');
	#	$mail->addBCC('bcc@example.com');

	$mail->isHTML(true);  // Set email format to HTML

	$bodyContent = $msg;

	$mail->Subject = $subject;
	$mail->Body    = $bodyContent;

	if(@$mail->send()) {
		#	echo 'Message could not be sent.';
		#	echo 'Mailer Error: ' . $mail->ErrorInfo;
		$a = 0;
	} else {
		#	echo 'Message has been sent';
		$a = 0;
	}
}







function reverse_strrchr($haystack, $needle)
{
     $pos = strrpos($haystack, $needle);

     if ($pos === FALSE)
     {
         return $haystack;
     }

     return substr($haystack, 0, $pos);
}

#$string = "/path/to/the/file/filename.php";
#echo reverse_strrchr($string, '/');    // will echo "/path/to/the/file/"



if (!function_exists('hash_equals')) {

    /**
     * Timing attack safe string comparison
     *
     * Compares two strings using the same time whether they're equal or not.
     * This function should be used to mitigate timing attacks; for instance, when testing crypt() password hashes.
     *
     * @param string $known_string The string of known length to compare against
     * @param string $user_string The user-supplied string
     * @return boolean Returns TRUE when the two strings are equal, FALSE otherwise.
     */
    function hash_equals($known_string, $user_string)
    {
        if (func_num_args() !== 2) {
            // handle wrong parameter count as the native implentation
            trigger_error('hash_equals() expects exactly 2 parameters, ' . func_num_args() . ' given', E_USER_WARNING);
            return null;
        }
        if (is_string($known_string) !== true) {
            trigger_error('hash_equals(): Expected known_string to be a string, ' . gettype($known_string) . ' given', E_USER_WARNING);
            return false;
        }
        $known_string_len = strlen($known_string);
        $user_string_type_error = 'hash_equals(): Expected user_string to be a string, ' . gettype($user_string) . ' given'; // prepare wrong type error message now to reduce the impact of string concatenation and the gettype call
        if (is_string($user_string) !== true) {
            trigger_error($user_string_type_error, E_USER_WARNING);
            // prevention of timing attacks might be still possible if we handle $user_string as a string of diffent length (the trigger_error() call increases the execution time a bit)
            $user_string_len = strlen($user_string);
            $user_string_len = $known_string_len + 1;
        } else {
            $user_string_len = $known_string_len + 1;
            $user_string_len = strlen($user_string);
        }
        if ($known_string_len !== $user_string_len) {
            $res = $known_string ^ $known_string; // use $known_string instead of $user_string to handle strings of diffrent length.
            $ret = 1; // set $ret to 1 to make sure false is returned
        } else {
            $res = $known_string ^ $user_string;
            $ret = 0;
        }
        for ($i = strlen($res) - 1; $i >= 0; $i--) {
            $ret |= ord($res[$i]);
        }
        return $ret === 0;
    }

}

	# just logs in and goes to the specific URL
	function gotoURL($url)
	{
		$headerURL	=	"Location: " .	$url .	"";
		header($headerURL);
		exit();
	}


	# grabs feed from the a web location
	function getFeed($feed_url="",$elements="",$chars="")
	{
		global	$gRssFeed;

		if	($gRssFeed)
		{
			return	$gRssFeed->getFeed($feed_url,$elements,$chars);

		}
	}


	# $date must be in YYYY-MM-DD format
	# You can pass in either an array of holidays in YYYYMMDD format
	# OR a URL for a .ics file containing holidays
	# this defaults to the UK government holiday data for England and Wales
	function addBusinessDays($date="2015-08-27",$numDays=3,$holidays="")
	{
		if ($holidays==='') $holidays = 'https://www.gov.uk/bank-holidays/england-and-wales.ics';

		if (!is_array($holidays))
		{
			$ics	=	file_get_contents($holidays);
			$ics = explode("\n",$ics);
			$ics = preg_grep('/^DTSTART;/',$ics);
			$holidays = preg_replace('/^DTSTART;VALUE=DATE:(\\d{4})(\\d{2})(\\d{2}).*/s','$1-$2-$3',$ics);
		}

		$addDay = 0;

		$nextDates = array();


		while (true)
		{
			$newDate = date('Y-m-d', strtotime("$date +$addDay Days"));
			$newDayOfWeek = date('w', strtotime($newDate));
			if ( $newDayOfWeek>0 && $newDayOfWeek<6 && !in_array($newDate,$holidays))
			{
				if ($numDays-- <=0)
				{
					break;
				}

				$nextDates[] = $newDate;
			}

			$addDay++;
		}

		return $nextDates;
	}


	function redirect($url, $statusCode = 303)
	{
		header('Location: ' . $url, true, $statusCode);
		die();
	}



	# class name eg. MY  "_class" is added
	function buildClassMethods($class_name,$remove_array=array())
	{
		# incase we made an error and sent a full name
#		$class_name			= 	explode('_', $class_name);
#		$class_name			=	$class_name[0];


#		$suffix 			=	strrchr($class_name, "_");
#		$pos 				= 	strpos($class_name  , $suffix);
#		$name 				= 	substr_replace ($class_name, "", $pos);
#		echo $suffix . "<br><br>". $name;





		# create the class name
		$class_name			=	ucfirst(strtolower($class_name))	.	"_Controller";

		# make sure it exists
		if (class_exists($class_name,true) == true)
		{
			$data				=	get_class_methods($class_name);
			$data				=	array_values(array_diff($data, $remove_array));
			return	$data;
		}
	}

	# returns the file list of a directory with the extension $x
	function file_list($d,$x)
	{
		foreach(array_diff(scandir($d),array('.','..')) as $f)if(is_file($d.'/'.$f)&&(($x)?ereg($x.'$',$f):1))$l[]=$f;
#		foreach(array_diff(scandir($d),array('.','..')) as $f)if(is_file($d.'/'.$f)&&(($x)?preg_match('/$x."$"/',$f):1))$l[]=$f;
		return $l;
	}




function strbefore($string, $substring) {
	$pos = strpos($string, $substring);
	if ($pos === false)
		return $string;
	else
		return(substr($string, 0, $pos));
}



# finds position of first number in string (used in flight code)
# we haev to certain that there are n
function first_number($text)
{
	$m	=	"";
	preg_match('/\d/', $text, $m, PREG_OFFSET_CAPTURE);
	if (sizeof($m))
		return $m[0][1]; // 24 in your example

	// return anything you need for the case when there's no numbers in the string
	return -1;
}


# checks flight using the database YYYY-MM-DD and returns a structure
# possibly add these to the database manually
# there is the possibility of returning several flights, but v1.0 will just grab the first
# v2.0 should return an array and we can then decide what to do by traversing it
function getFlightDataFromAPI($airline_code,$flight_number,$date)
{
	# converts date from DD/MM/YYYY to YYYY-MM-DD
	if	(!empty($date))
	{

		$converted_date		=	date( 'Y-m-d', strtotime($date) );

	#	$curl = curl_init("http://affiliate.fairplane.net/Services/GetFlights.ashx?carrier=$airline_code&flightnr=$flight_number&date=$converted_date");
		# use this if on an external server
		$curl = curl_init("http://test.fairplane.co.uk/scripts/getflight.php?carrier=$airline_code&flightnr=$flight_number&date=$converted_date");


		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		if	($data 	=	curl_exec($curl))
		{
			if	($data	=	json_decode($data,true))
			{
				if	(isset($data['segments']))
				{
					if	(!empty($data['segments'][0]))
					{
						# V2.0 will return the array so we can decide what to do
						if	(count($data['segments']) == 1)
						{
							if	(array_key_exists("key",$data['segments'][0]))
							{
								return	$data['segments'][0];
							}
						}
					}
					else
					{
						$a = 0;
					}
				}
			}
		}
	}
}



# returns if the flight was cancelled or delayed
function getFlightDelayedFromAPI($data)
{
	if	(isset($data['departure_status_code']))
	{
		if	(($data['departure_status_code'] == "CX") || ($data['departure_status_code'] == "DY") )
		{
			return true;
		}
	}
	if	(isset($data['arrival_status_code']))
	{
		if	(($data['arrival_status_code'] == "CX") || ($data['arrival_status_code'] == "DY") )
		{
			return true;
		}
	}
}

# are we on windows or unix?
function  IsWindows()
{
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
	{
		return true;
	}

	return false;
}






function crypto_rand_secure($min, $max)
{
	$range = $max - $min;
	if ($range < 1) return $min; // not so random...
	$log = ceil(log($range, 2));
	$bytes = (int) ($log / 8) + 1; // length in bytes
	$bits = (int) $log + 1; // length in bits
	$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	do {
		$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
		$rnd = $rnd & $filter; // discard irrelevant bits
	} while ($rnd >= $range);
	return $min + $rnd;
}

function getToken($length)
{
	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet.= "0123456789";
	$max = strlen($codeAlphabet) - 1;
	for ($i=0; $i < $length; $i++) {
		$token .= $codeAlphabet[crypto_rand_secure(0, $max)];
	}
	return $token;
}



if (!function_exists('array_column')) {
	function array_column($input, $column_key, $index_key = null) {
		$arr = array_map(function($d) use ($column_key, $index_key) {
			if (!isset($d[$column_key])) {
				return null;
			}
			if ($index_key !== null) {
				return array($d[$index_key] => $d[$column_key]);
			}
			return $d[$column_key];
		}, $input);

		if ($index_key !== null) {
			$tmp = array();
			foreach ($arr as $ar) {
				$tmp[key($ar)] = current($ar);
			}
			$arr = $tmp;
		}
		return $arr;
	}
}


function get_string_between($string, $start, $end){
#	$string = ' ' . $string;
	$ini = strpos($string, $start);
#	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}


/**
 * Replace the last occurrence of a string.
 *
 * @param string $search
 * @param string $replace
 * @param string $subject
 * @return string
 */
function strReplaceLast ( $search, $replace, $subject ) {

	$lenOfSearch = strlen( $search );
	$posOfSearch = strrpos( $subject, $search );

	return substr_replace( $subject, $replace, $posOfSearch, $lenOfSearch );

}
/**
 * Remove all characters except letters, numbers, and spaces.
 *
 * @param string $string
 * @return string
 */
function stripNonAlphaNumericSpaces( $string ) {
	return preg_replace( "/[^a-z0-9 ]/i", "", $string );
}
/**
 * Remove all characters except letters and numbers.
 *
 * @param string $string
 * @return string
 */
function stripNonAlphaNumeric( $string ) {
	return preg_replace( "/[^a-z0-9]/i", "", $string );
}
/**
 * Remove all characters except numbers.
 *
 * @param string $string
 * @return string
 */
function stripNonNumeric( $string ) {
	return preg_replace( "/[^0-9]/", "", $string );
}
/**
 * Remove all characters except letters.
 *
 * @param string $string
 * @return string
 */
function stripNonAlpha( $string ) {
	return preg_replace( "/[^a-z]/i", "", $string );
}
/**
 * Transform two or more spaces into just one space.
 *
 * @param string $string
 * @return string
 */
function stripExcessWhitespace( $string ) {
	return preg_replace( '/  +/', ' ', $string );
}
/**
 * Format a string so it can be used for a URL slug
 *
 * @param string $string
 * @return string
 */
function formatForUrl( $string ) {

	$string = stripNonAlphaNumericSpaces( trim( strtolower( $string ) ) );
	return str_replace( " ", "-", stripExcessWhitespace( $string ) );

}
/**
 * Format a slug into human readable string
 *
 * @param string $string
 * @return string
 */
function formatFromUrl( $string ) {
	return str_replace( "-", " ", trim( strtolower( $string ) ) );
}
/**
 * Get an array of unique characters used in a string. This should also work with multibyte characters.
 *
 * @param string $string
 * @return mixed
 */
function getUniqueChars( $string, $returnAsArray=true ) {
	$unique = array_unique( preg_split( '/(?<!^)(?!$)/u', $string ) );
	if ( empty( $returnAsArray ) ) {
		$unique = implode( "", $unique );
	}
	return $unique;
}


if (!function_exists('mb_str_replace')) {
	function mb_str_replace($search, $replace, $subject, &$count = 0) {
		if (!is_array($subject)) {
			// Normalize $search and $replace so they are both arrays of the same length
			$searches = is_array($search) ? array_values($search) : array($search);
			$replacements = is_array($replace) ? array_values($replace) : array($replace);
			$replacements = array_pad($replacements, count($searches), '');
			foreach ($searches as $key => $search) {
				$parts = mb_split(preg_quote($search), $subject);
				$count += count($parts) - 1;
				$subject = implode($replacements[$key], $parts);
			}
		} else {
			// Call mb_str_replace for each subject in array, recursively
			foreach ($subject as $key => $value) {
				$subject[$key] = mb_str_replace($search, $replace, $value, $count);
			}
		}
		return $subject;
	}
}


if (!function_exists('http_response_code')) {
	function http_response_code($code = NULL) {

		if ($code !== NULL) {

			switch ($code) {
			case 100: $text = 'Continue'; break;
			case 101: $text = 'Switching Protocols'; break;
			case 200: $text = 'OK'; break;
			case 201: $text = 'Created'; break;
			case 202: $text = 'Accepted'; break;
			case 203: $text = 'Non-Authoritative Information'; break;
			case 204: $text = 'No Content'; break;
			case 205: $text = 'Reset Content'; break;
			case 206: $text = 'Partial Content'; break;
			case 300: $text = 'Multiple Choices'; break;
			case 301: $text = 'Moved Permanently'; break;
			case 302: $text = 'Moved Temporarily'; break;
			case 303: $text = 'See Other'; break;
			case 304: $text = 'Not Modified'; break;
			case 305: $text = 'Use Proxy'; break;
			case 400: $text = 'Bad Request'; break;
			case 401: $text = 'Unauthorized'; break;
			case 402: $text = 'Payment Required'; break;
			case 403: $text = 'Forbidden'; break;
			case 404: $text = 'Not Found'; break;
			case 405: $text = 'Method Not Allowed'; break;
			case 406: $text = 'Not Acceptable'; break;
			case 407: $text = 'Proxy Authentication Required'; break;
			case 408: $text = 'Request Time-out'; break;
			case 409: $text = 'Conflict'; break;
			case 410: $text = 'Gone'; break;
			case 411: $text = 'Length Required'; break;
			case 412: $text = 'Precondition Failed'; break;
			case 413: $text = 'Request Entity Too Large'; break;
			case 414: $text = 'Request-URI Too Large'; break;
			case 415: $text = 'Unsupported Media Type'; break;
			case 500: $text = 'Internal Server Error'; break;
			case 501: $text = 'Not Implemented'; break;
			case 502: $text = 'Bad Gateway'; break;
			case 503: $text = 'Service Unavailable'; break;
			case 504: $text = 'Gateway Time-out'; break;
			case 505: $text = 'HTTP Version not supported'; break;
			default:
				exit('Unknown http status code "' . htmlentities($code) . '"');
			break;
			}

			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

			header($protocol . ' ' . $code . ' ' . $text);

			$GLOBALS['http_response_code'] = $code;

		} else {

			$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

		}

		return $code;

	}
}


function transliterateString($txt) {
	$transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'E', 'ё' => 'e', 'Ё' => 'E', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
	return str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
}



# sends the file via FTP
function send_ftp_($ftp_server,$ftp_user_name,$ftp_user_pass,$filename,$from_folder,$to_folder)
{
	AddComment("send_ftp sending file $filename ");
	$conn_id 	=	ftp_connect($ftp_server) or die("Couldn't connect to $ftp_server");
	// login with username and password
	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
	# check we have logged in withthe connection
	if ((!$conn_id) || (!$login_result))
	{
		AddComment("send_ftp Error trying to connect $ftp_server ($filename)");
	}
	else
	{
		// turn passive mode on
		ftp_pasv($conn_id, true);
		if (ftp_put($conn_id, $to_folder.$filename, $from_folder.$filename, FTP_BINARY))
		{
			AddComment("Successfully uploaded $filename");
		} else
		{
			AddComment("There was a problem while uploading $filename");
		}
	}
	// close the connection and the file handler
	ftp_close($conn_id);
}