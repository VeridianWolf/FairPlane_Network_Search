<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Cedric
 * Date: 15/10/13
 * Time: 21:24
 * To change this template use File | Settings | File Templates.
 */
define	('TEMPLATES_DIR',		'views/templates/');

//-------------------------------------------------------------------------------------------------
# handles basic templates like in perl
class Template_Library
{

	var $templateFile;

	var $output;
	var $overall;

	var $tags = array();

	var $gCache;

	# this includes ueruery in the headers or not
	var	$jquery_ui	=	false;
	var	$jquery		=	true;

	# this will initiate the template, and keep pointer to relevant friend classes
	public function __construct($templateFile=NULL)
	{
		# We can probably pass these values along in the route
		global	$gCache;

		if	(empty($gCache))
		{
			$gCache			=	new Cache_Library();
		}

		$this->gCache			=	$gCache;

		# the template file name
		$this->templateFile		=	$templateFile;
		# unique name for the cache
		$cache_filename			=	str_replace("/","",$this->templateFile);

		# gets the data from cache or loads it
		$this->output			=	$gCache->cache_or_get(CACHE_CACHE_TIME_TINY,"tmpl",$cache_filename, "load_template_file","$this->templateFile");

		if	(empty($this->output))
		{
			echo "Template Library Error: File not found: $templateFile";
			exit;
		}
	}







	# substitutes the tags
	function sub_tags($tags)
	{
		$num	=	0;

		foreach ($tags as $key => $value)
		{
			$this->output = str_replace ($key, $value, $this->output);

			$num++;
		}

		return	$num;
	}








	# displays content of page with header and footer
	function display($tags)
	{
		global	$gProfiler;

		# substitute other tags
		$this->sub_tags($tags);


		echo $this->output;

		if	($gProfiler)
		{
			$len	=	number_format(strlen($this->output)/ 1024,2) ;
			$gProfiler->log("template page size:$len Kb");

			# displays profiler output if turned on
			$gProfiler->display();
		}

	}










	# displays content of page header
	function setHeader($header_template_name)
	{

		$header_string		=		$this->gCache->cache_or_get(CACHE_CACHE_TIME_TINY, "",$header_template_name, "load_template_file",$header_template_name);

		# the header has special substitutions for jquery css and jquery js
		$jquery_ui_css_string	=	"<link rel='stylesheet' href='" . 	JQUERY_UI_CSS	.	"' />";
		$jquery_ui_js_string	=	'<script src="' . 	JQUERY_UI_JS	.	'"></script>';

		$jquery_js_string		=	'<script src="' . 	JQUERY_JS		.	'"></script>'. "\n";
		$jquery_js_string		.=	'<script> window.jQuery || document.write("<script src=/app/includes/js/jquery/jquery-1.10.2.min.js><\/script>")</script>';


		$basic_tags			=

			array
			(
				"{{header}}"				=>	$header_string,
				"{{jquery_ui_css}}"			=>	$jquery_ui_css_string,
				"{{jquery_ui_js}}"			=>	$jquery_ui_js_string,
				"{{jquery_js}}"				=>	$jquery_js_string,
			);


		# append the array
		$this->sub_tags($basic_tags);


	}








	# displays content of page with header and footer
	function setFooter($footer_template_name)
	{
		global	$site_path;

		$footer_string		=		$this->gCache->cache_or_get(CACHE_CACHE_TIME_TINY, "template",$footer_template_name, "load_template_file",$footer_template_name);


		$basic_tags			=

			array
			(
				"{{footer}}"				=>	$footer_string,
				"{{site_path}}"				=>	$site_path,
			);


		# append the array
		$this->sub_tags($basic_tags);

	}


















	# gets the side navigation
	function getNavigation()
	{


		return;

		global $gMysql;
		global $member_id;
		# grab these items
		$num_views			=	$gMysql->queryItem("select count(*) from sm_views where member_id='$member_id'",__FILE__,__LINE__);
		$num_winks			=	$gMysql->queryItem("select count(*) from sm_winks where member_id='$member_id'",__FILE__,__LINE__);
		$num_favs			=	$gMysql->queryItem("select count(*) from sm_fav where member_id='$member_id'",__FILE__,__LINE__);
		$num_messages		=	$gMysql->queryItem("select count(*) from sm_messages where member_id='$member_id'",__FILE__,__LINE__);



		$menuArray	=	array(
		array(
		"title"			=>	"Profile",
		"bShowNumber"	=>	false,
		"number"		=>	0,
		"bHeader"		=>	true,
		"className"		=>	"icon-user",
		"link" 			=>	"http://bbc.co.uk",
		),
		array(
		"title"			      =>	"Search",
		"bShowNumber"	  =>	false,
		"number"		      =>	0,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-search",
		"link" 			      =>	"search",
		),
		array(
		"title"			      =>	"Messages",
		"bShowNumber"	  =>	true,
		"number"		      =>	$num_messages,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-mail",
		"link" 			      =>	"inbox",
		),
		array(
		"title"			      =>	"Views",
		"bShowNumber"	  =>	true,
		"number"		      =>	$num_views,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-binoculars",
		"link" 			      =>	"views",
		),
		array(
		"title"			      =>	"Winks",
		"bShowNumber"	  =>	true,
		"number"		      =>	$num_winks,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-eye",
		"link" 			      =>	"winks",
		),
		array(
		"title"			      =>	"Favourites",
		"bShowNumber"	  =>	true,
		"number"		      =>	$num_favs,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-bookmarks",
		"link" 			      =>	"favourites",
		),
		array(
		"title"			      =>	"Matches",
		"bShowNumber"	  =>	false,
		"number"		      =>	0,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-heart",
		"link" 			      =>	"matches",
		),
		array(
		"title"			      =>	"Membership",
		"bShowNumber"	  =>	false,
		"number"		      =>	0,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-unlocked",
		"link" 			      =>	"membership",
		),
		array(
		"title"			      =>	"Profile",
		"bShowNumber"	  =>	false,
		"number"		      =>	0,
		"bHeader"		     =>	true,
		"className"		   =>	"icon-user",
		"link" 			      =>	"profile",
		),
		array(
		"title"				=>	"Logout",
		"bShowNumber"		=>	false,
		"number"			=>	0,
		"bHeader"			=>	true,
		"className"			=>	"icon-switch",
		"link"				=>	"javacript:confirm('app/m/login/logout');",
		),
		);

		$selectedItem	=	3;
		$index			=	0;


		$menuString	=	' <ul> ';


		foreach ($menuArray as $menuItem)
		{
			# build html of menu
			if	($selectedItem == $index)
			{
				$menu_line	=	'<li class="selected">';
			}
			else
			{
				$menu_line	=	'<li>';
			}


			$menu_line	.=	'<a href="' . $menuItem['link'] . '" class="' . $menuItem['className'] . '" >' . $menuItem['title'];
			# numerics?
			if	($menuItem['bShowNumber'] == true)
			{
				$menu_line	.=		' <span class="pip">' . $menuItem['number'] . '</span>';
			}
			$menu_line   .= "</a></li>\r\n";

			$menuString	.=	$menu_line;

			$index++;
		}
		$menuString	.=	"</ul>";

		return	$menuString;
	}










}


?>