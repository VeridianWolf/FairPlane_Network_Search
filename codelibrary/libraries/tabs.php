<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 15/09/14
 * Time: 23:40
 */


class Tabs_Library
{


	# checks the pattern with a parent
	public function getParentPattern($link_pattern)
	{
		global $menu_list;

		foreach	($menu_list as $tab)
		{
			if	(strcmp($tab['pattern'],$link_pattern) == 0)
			{
				return	$tab['pattern'];
			}

			if	( isset($tab['subnav']) && ($tab['subnav-on'] == true) )
			{
				foreach	($tab['subnav'] as $subnav)
				{
					if	(strcmp($subnav['pattern'],$link_pattern) == 0)
					{
						return	$tab['pattern'];
					}
				}
			}

		}
	}





	# this should return the javascript to activate the tab and the tab list
	# if we are going to another page, then we do not need ajax tabs \_ these tabs can just call the url#
	# if all tabs are within the same page, then we need normal tabs /
	public static function doBuildAjaxTabs($tab_list,$pattern,$tabs_id="myTabs")
	{

		$tab_index			=	0;
		$active_tab_id		=	$tab_list[0]['id'];
		$tab_string_top		=	'<ul class="nav nav-tabs" role="tablist" id="'. $tabs_id .'">';
		$tab_string_bot		=	'<div class="tab-content">';


		foreach	($tab_list as $tab)
		{
			# we can just use the ID
			if	(strcmp($tab['pattern'],$pattern) == 0)
			{
				$active_tab_id	=	$tab['id'];
				$in				=	' in active';
				$active_class	=	' class="active"';
			}
			else
			{
				$active_class	=	'';
				$in				=	'';
			}

			# *** problem with faded tabs is that initial ajax wont trigger (as the class is already marked as 'active'
				$active_class	=	'';
				$in				=	'';

			$rr	=	rand(0,1111);
			$tab_string_top		.=	'<li '.$active_class.'>';
			$tab_string_bot		.=	'<div class="tab-pane fade'. $in .'" id="'. $tab['id'] .'">This is a test'. $rr.' </div>';

			# build the full string of the top section
			$tab_string_top		.=	'<a href="#'. $tab['id'] .'" role="tab" data-toggle="tab" data-url="'. $tab['data_url'] .'">'. $tab['name'] .'</a></li>';
			$tab_index			+=	1;
		}

		$tab_string_top		.=	'</ul>';
		$tab_string_bot		.=	'</div>';
/*
		$tab_string_bot		.=	'<script>
		$(document).ready(function(){
		$(\'a[href="#'. $active_tab_id .'"]\').tab(\'show\');
		});
		</script>';
*/
		$tab_string_bot		.=	'<script>
		$(document).ready(function(){

		$(\'a[href="#'. $active_tab_id .'"]\').tab(\'show\');
//		setTimeout(2000,$(\'a[href="#'. $active_tab_id .'"]\').tab(\'show\'));

		});


		</script>';


		$tab_string			=	$tab_string_top .	$tab_string_bot;

		return	$tab_string;
	}





	# builds a tab menu and fills in
	/*
	 *
	 *	member_id	=	can link to the database to extract member specific data
	 *	menu_list	=	menu items
	 *	divider		=	do we have a caret divider?
	 *
	 * */
	public static function doBuildTabMenu($pattern,$menu_list,$member_id=0)
	{
		$string	=	"";

		if	(is_array($menu_list))
		{
			foreach ($menu_list as $tab)
			{
				# get parent pattern so we can set main tab
				$parent_pattern	=	self::getParentPattern($pattern);

				if	(strcmp($tab['pattern'],$parent_pattern) == 0)
				{
					$active = " active";
				}
				else
				{
					$active = "";
				}


				# we have a subnav and it's on, so lets build
				if	( isset($tab['subnav']) && ($tab['subnav-on'] == true) )
				{
					# base of the string
					$string	.=	"<li class='dropdown ".$active."'><a href='/". $tab['pattern'] . "' class='dropdown-toggle' data-toggle='dropdown' >";

					# test for glyphs at front
					if	(isset($tab['glyph']))
					{
						if	(!empty($tab['glyph']))
						{
							$string .= "<span class='". $tab['glyph']."'></span>&nbsp;&nbsp;";
						}
						else
						{
						}
					}
					if	(isset($tab['text']))
					{
						$string .=	$tab['text'];
					}
					# test for badge with text
					if	(isset($tab['badge']))
					{
						$string	.=	self::buildBadge($member_id,$tab);
					}


					$string		.=	"</a><ul class='dropdown-menu  dropdown-menu-topnav ' role='menu'>";

					# now build a subnav
					foreach	($tab['subnav'] as $subnav)
					{
						$string	.=	"<li><a href='/". $subnav['pattern'] . "'>". $subnav['text'] ."</a></li>";
					}
					$string	.=	"</ul></li>";
				}
				else
				{
					# base of the string
					$string	.=	"<li ".$active."><a href='/". $tab['pattern'] . "'>";

					# test for glyphs at front
					if	(isset($tab['glyph']))
					{
						if	(!empty($tab['glyph']))
						{
							$string .= "<span class='". $tab['glyph']."'></span>&nbsp;&nbsp;";
						}
						else
						{
						}
					}
					# base of the string
					$string	.=	$tab['text'];

					# test for badge with text
					if	(isset($tab['badge']))
					{
						$string	.=	self::buildBadge($member_id,$tab);
					}

					$string	.=	'</a></li>';
				}
			}
		}

		return	$string;
	}




	# build a subnav badge if needed but maybe slightly different in a subnav
	protected function buildBadge($member_id,$tab)
	{
		global	$gMysql;

		$string	=	"";
		if ($tab['item'] == "inbox")
		{
			if	(($num	=	$gMysql->queryItem("select count(*) from sm_messages where member_id='$member_id' and opened=0",__FILE__,__LINE__,MYSQL_CACHE_TIME_TINY)) != -1)
			{
				$string	.=	'&nbsp;&nbsp;<span class="badge badge-danger">'. $num  .'</span>';
			}
		}
		else if ($tab['item'] == "views")
		{
			if	(($num	=	$gMysql->queryItem("select count(*) from sm_views where member_id='$member_id' and opened=0",__FILE__,__LINE__,MYSQL_CACHE_TIME_TINY)) != -1)
			{
				$string	.=	'&nbsp;&nbsp;<span class="badge badge-danger">'. $num  .'</span>';
			}
		}
		else if ($tab['item'] == "winks")
		{
			if	(($num	=	$gMysql->queryItem("select count(*) from sm_winks where member_id='$member_id' and opened=0",__FILE__,__LINE__,MYSQL_CACHE_TIME_TINY)) != -1)
			{
				$string	.=	'&nbsp;&nbsp;<span class="badge badge-danger">'. $num  .'</span>';
			}
		}
		else if ($tab['item'] == "favourites")
		{
			if	(($num	=	$gMysql->queryItem("select count(*) from sm_fav where member_id='$member_id' and opened=0",__FILE__,__LINE__,MYSQL_CACHE_TIME_TINY)) != -1)
			{
				$string	.=	'&nbsp;&nbsp;<span class="badge badge-danger">'. $num  .'</span>';
			}
		}
		return	$string;
	}
























}