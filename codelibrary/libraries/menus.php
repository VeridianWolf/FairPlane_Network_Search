<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 15/09/14
 * Time: 23:40
 */

class Menus_Library
{
	private	$current_section_id;


	# pass in the database as an array
	public function __construct($data_array=null)
	{
	}


	# creates a menu, we can pass the current pattern to hilite it, or style the topmost menu
	# data = section_id,ref,title,
	public function createStandardMenu($data_array,$bUppercaseHeader=false,$bPullRight=false,$bIncludeHome=true,$current_pattern="")
	{
		# slight position change
		if	($bPullRight == true)
		{
			$menu_string		=	"<ul class='navigation-menu-pull-right'>";
		}
		else
		{
			$menu_string		=	"<ul class='navigation-menu '>";
		}

		$last_section_id	=	-2;
		$num				=	count($data_array);

		for ($i=0;$i<$num;$i++)
		{
			$current_section_id		=	$data_array[$i]['section_id'];

			# removes the home link
			if	(($bIncludeHome == false) && (empty($data_array[$i]['ref'])))
			{
				continue;
			}


			if	($current_section_id  != $last_section_id)
			{
				# create new menu section
				$section_items		=	$this->getTotalInSection($current_section_id,$data_array);
				$link				=	$data_array[$i]['ref'];
				$title				=	urldecode(html_entity_decode($data_array[$i]['title'],ENT_QUOTES));

				# this is for uppercase top level
				if	($bUppercaseHeader == true)
				{
					$title	=	strtoupper($title);
				}

				# check if this has submenus by counting number of items
				/*
								if	($section_items > 1)
								{
									$menu_string	.=	"<li class='dropdown active'><a class='dropdown-toggle' data-toggle='dropdown' role='button' href='/";
									$menu_string	.=	$link;
									$menu_string	.=	"'>$title</a>";
									$menu_string	.=	"<ul class='dropdown-menu' role='menu'>";

									# sub loop to build next level
									for($j=0;$j<$section_items;$j++,$i++)
									{
										$index			=	$i;
										$menu_string	.=	$this->buildStandardMenuLink($data_array[$index]);
									}
									# end of submenu
									$menu_string	.=	'</ul></li>';

									# correction, as the outer loop increments
									$i--;
				# single link
				}
				else
				*/
				{
					# added to allow single item to be active
					if	((strcasecmp(str_replace("/","",$current_pattern),str_replace("/","",$data_array[$i]['ref'])) == 0))
					{
						$bActive	=	true;
					}
					else
					{
						$bActive	=	false;
					}

					$menu_string	.=	$this->buildStandardMenuLink($data_array[$i],$bUppercaseHeader,$bActive);

					$last_section_id  = $current_section_id;

				}


			}
		}
		$menu_string	.=	'</ul>';

		return $menu_string;
	}


	# subsection link
	function  buildStandardMenuLink($data,$bUppercaseHeader=false,$bActive=false)
	{
		$link			=	$data['ref'];
		$title			=	urldecode(html_entity_decode($data['title'],ENT_QUOTES));
		# this is for uppercase top level
		if	($bUppercaseHeader == true)
		{
			$title	=	strtoupper($title);
		}
		if	($bActive)
		{
			$menu_string	=	"<li class='navigation-menu-dropdown navigation-menu-active'><a href='";
		}
		else
		{
			$menu_string	=	"<li><a href='";
		}

		$menu_string	.=	$link;
		$menu_string	.=	"'>";
		$menu_string	.=	$title;
		$menu_string	.=	'</a></li>';
		return $menu_string;
	}




	# creates a menu, we can pass the current pattern to hilite it, or style the topmost menu
	#
	# $bMobileMenu
	# change this to mobile menu
	# $bLastItemPullRight is to allow the menu item on the right to open pulled left
	# we have the ability to remove items on lower resolutions (mobile etc) so things are simplified
	#
	#
	public function createMenu($data_array,$bUppercaseHeader=false,$bPullRight=false,$bIncludeHome=true,$current_pattern="",$bMobileMenu=false,$bLastItemPullRight=false)
	{

		# slight position change
		if	($bPullRight == true)
		{
			$menu_string		=	"<ul class='nav navbar-nav pull-right'>\n";
		}
		else
		{
			$menu_string		=	"<ul class='nav navbar-nav '>\n";
		}

		$last_section_id	=	-2;
		$num				=	count($data_array);

		for ($i=0;$i<$num;$i++)
		{
			$current_section_id		=	$data_array[$i]['section_id'];

			# removes the home link
			if	(($bIncludeHome == false) && (empty($data_array[$i]['ref'])))
			{
				continue;
			}


			if	($current_section_id  != $last_section_id)
			{
				# create new menu section
				$section_items		=	$this->getTotalInSection($current_section_id,$data_array);
				$link				=	$data_array[$i]['ref'];
				$title				=	urldecode(html_entity_decode($data_array[$i]['title'],ENT_QUOTES));

				# this is for uppercase top level
				if	($bUppercaseHeader == true)
				{
					$title	=	strtoupper($title);
				}

				# check if this has submenus by counting number of items
				if	($section_items > 1)
				{
					$hidden	=	" ";
					# this will remove the item at lower resolutions
					if	($data_array[$i]['prod_slots'] == -1)
					{
						$hidden	=	" hidden_lg ";
					}
					# add 'disabled' so that the top level parent is clickable
					$menu_string	.=	"<li class='dropdown active'><a class='dropdown-toggle $hidden' data-toggle='dropdown'  aria-haspopup='true' aria-expanded='false' role='button' href='/";
					$menu_string	.=	$link;
					$menu_string	.=	"'>$title <span class='caret' style='white-space: normal;'></span></a>";


					$pull =	"";
					# last item should pull away from right edge if required
					if (($i >= ($num-$section_items-1)) && ($bLastItemPullRight==true))
					{
						$pull = "pull-right";
					}

					$menu_string	.=	"<ul class='dropdown-menu $pull' role='menu'>\n";


					# i$bMobileMenu==true f the first item has an alias, that means we should create an extra mobile entry
					if	(($bMobileMenu == true) && (!empty($data_array[$i]['alias'])))
					{
						$menu_string	.=	$this->buildMenuLink($data_array[$i],false,false,true);
					}


					# sub loop to build next level
					for($j=0;$j<($section_items-1);$j++,$i++)
					{
						# start with the next item
						$index			=	$i+1;
						$menu_string	.=	$this->buildMenuLink($data_array[$index]);
					}
					# end of submenu
					$menu_string	.=	"</ul></li>\n";

				}
				# single link
				else
				{
					# added to allow single item to be active
					if	((strcasecmp(str_replace("/","",$current_pattern),str_replace("/","",$data_array[$i]['ref'])) == 0))
					{
						$bActive	=	true;
					}
					else
					{
						$bActive	=	false;
					}

					$menu_string	.=	$this->buildMenuLink($data_array[$i],$bUppercaseHeader,$bActive);

					$last_section_id  = $current_section_id;

				}


			}
		}
		$menu_string	.=	'</ul>';

		return $menu_string;
	}


	# subsection link
	function  buildMenuLink($data,$bUppercaseHeader=false,$bActive=false,$buseAlias=false,$bNolink=false)
	{
		$link			=	$data['ref'];

		# alias is for mobile
		if	($buseAlias == true)
		{
			$title			=	urldecode(html_entity_decode($data['alias'],ENT_QUOTES));
		}
		else
		{
			$title			=	urldecode(html_entity_decode($data['title'],ENT_QUOTES));
		}
		# this is for uppercase top level
		if	($bUppercaseHeader == true)
		{
			$title	=	strtoupper($title);
		}
		# this is for uppercase top level
		if	($bNolink == true)
		{
			$menu_string	=	"<li>";
			$menu_string	.=	$title;
			$menu_string	.=	"</li>\n";
			return $menu_string;
		}


		if	($bActive)
		{
			$menu_string	=	"<li class='dropdown active'><a href='";
		}
		else
		{
			$menu_string	=	"<li><a href='";
		}

		$menu_string	.=	$link;
		$menu_string	.=	"'>";
		$menu_string	.=	$title;
		$menu_string	.=	"</a></li>\n";
		return $menu_string;
	}


	function getTotalInSection($current_section_id,$data_array)
	{
		$num	=	0;

		foreach ($data_array as $item)
		{
			if	($item['section_id'] == $current_section_id)
			{
				$num++;
			}
		}

	#	if	($num > 1)	$num--;


		return	$num;
	}









}
