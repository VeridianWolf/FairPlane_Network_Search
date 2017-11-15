<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 15/09/14
 * Time: 23:40
 */

/*
 *
 *
 *
 *
 *
 *
 *
 */
class Forms_Library
{
	private $id;
	private $element_list;

	# build form elements
	public static function doBuildFormElements($id,$element_list)
	{
		# get the seed data via query
		$data_array		=	self::doBuildQueriesToArray($id,$element_list['queries']);
		# error boxes
		$string			=	'
		<div class="form-body"><div class="alert alert-danger display-hide"><button class="close" data-close="alert"></button>You have some form errors. Please check below.</div>
		<div class="alert alert-success display-hide"><button class="close" data-close="alert"></button>Your form validation is successful!</div>';

		# now loop to build all the elements
		foreach ($element_list['elements'] as $element)
		{
			# set value
			$value	=	"";

			if	(isset($data_array[$element['field']]))
			{
			 	$value	=	$data_array[$element['field']];
			}

			$string	.=	'<div class="form-group "><label class="control-label col-md-3">'	.	$element['name'] . '';

			if	($element['required'] == true)
			{
				$string	.=	 '<span class="required"> *</span>';
			}
			$string	.=	'</label><div class="col-md-4">';

			# now time to build the actual input form (dates and dropdowns,checkboxes and radios to be added)
			if ($element['type'] == 'checkbox')
			{
				$string .= '<div class="checkbox-list" data-error-container="#' . $element['field'] . '_error">';
				# loop through all sub-elements
				$string .=	self::doBuildCheckboxElements($data_array,$element['sub_element']);
			}
			else if ($element['type'] == 'datepicker')
			{
				$value	=	date("d/m/Y",strtotime($value));
				$string .= '<div class="input-group date date-picker" data-date-format="dd/mm/yyyy">';
				$string .= '<input name="' . $element['field'] . '"  type="text" class="form-control" value="' . $value . '"><span class="input-group-btn"><button class="btn default" type="button"><i class="fa fa-calendar"></i></button></span>';
				$string .= '</div>';
			}
			else if ($element['type'] == 'dropdowns')
			{
				$string .= '<input name="' . $element['field'] . '"  type="text" class="form-control" value="' . $value . '">';
			}
			# normal
			else
			{
				$string	.=		'<input name="'	.	$element['field']	.	'"  type="text" class="form-control" value="'. $value . '">';
			}

			# help block00
			if	(!empty($element['help_block']))
			{
				$string	.=	'<span class="help-block">'. $element['help_block'] .'</span>';
			}

			$string	.=	'</div></div>';
		}
		# end of the form with submit button
		$string	.=	'<div class="form-actions"><div class="row"><div class="col-md-offset-3 col-md-9"><button type="submit" class="btn btn-success">'. $element_list['submit'] .'</button></div></div></div></div>';

		return	$string;
	}



	# build a string element
	private static function doBuildCheckboxElements($data_array,$element_array)
	{
		$string	=	'';

		foreach ($element_array as $element)
		{
			# get the value of the data
			$value		=	$data_array[$element['field']];
			# default is not checked
			$checked	=	'';

			if ($value)
			{
				$checked	=	" checked";
			}

			# basic checkbox will just be on/off
			$string .= '<label><input type="checkbox" name="' .   $element['name'] . '  class="form-control" '. $checked . '>' . $element['name'] . '</label>';
		}

		return $string;
	}





	# build queries with passing an id
	private static function doBuildQueriesToArray($id,$element_list)
	{
		global	$gMysql;

		$data_list	=	array();

		foreach ($element_list as $query_array)
		{
			# build query string
			$query		=	"select " . $query_array['fields'] . " from ". $query_array['table'] ." where ". $query_array['clause'];
			$query		=	str_replace('[id]',$id,$query);

			# substitute id
			$data_list	=	array_merge($data_list,$gMysql->queryRow($query,__FILE__,__LINE__));
		}
		return	$data_list;
	}




	# build queries with passing an id
	private static function doFormElementsToArray($element_list)
	{
		global	$gMysql;

		$data_list	=	array();

		foreach ($element_list as $query_array)
		{
			# grab all fields needed to update
			$field_array	=	explode(",",$query_array['update_fields']);

			foreach ($field_array as $field)
			{
				# make sure the field is requestable
				if	(isset($_REQUEST[$field]))
				{
					$data_list[$field]		=	$_REQUEST[$field];
				}
			}
		}
		return	$data_list;
	}




	# we need to update the database
	public static function doHandleUpdate($id,$element_list)
	{
		# get the seed data via form
		$data_array		=	self::doFormElementsToArray($element_list['queries']);

		# we will need a possible error-handler with the results here - but maybe that is taken care of in the jquery beforehand
		self::updateDatabase($id,$element_list,$data_array);
	}







	# update database
	private static function updateDatabase($id,$element_list,$data_array)
	{
		global	$gMysql;

		foreach ($element_list['queries'] as $query_array)
		{
			$queries	=	0;
			# build query string
			$query			=	"update " . $query_array['table'] . " set ";
			# grab all fields needed
			$field_array	=	explode(",",$query_array['update_fields']);

			foreach ($field_array as $field)
			{
				# make sure the field is request-able
				if	(isset($data_array[$field]))
				{
					$type	=	self::getFieldType($field,$element_list['elements']);
					# date check
					if	( $type == "datepicker")
					{
						$value					=	$data_array[$field];
						$value					=	str_replace('/', '-', $value);
						$data_array[$field]		=	date("Y-m-d",strtotime($value));
					}

					$query .=	" ". $field . "='" . $data_array[$field] . "',";

					$queries++;
				}
			}
			# only
			if	($queries)
			{
				# trim last comma
				$query	=	rtrim($query,",");
				# add were clause
				$query	.=	" where ". $query_array['clause'];

				# substitute id
				$query	=	str_replace('[id]',$id,$query);

				$gMysql->update($query,__FILE__,__LINE__);
			}
		}
	}



	# returns the type of variable
	private static function getFieldType($field,$element_list)
	{
		foreach ($element_list as $element)
		{
			if	($element['field'] == $field)
			{
				return	$element['type'];
			}
		}
	}



}