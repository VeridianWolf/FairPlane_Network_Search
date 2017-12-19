<?php
#include these files from codelibrary
require_once($_SERVER["DOCUMENT_ROOT"]	.	"/codelibrary/includes/php/autoload.php");
require_once($_SERVER["DOCUMENT_ROOT"]	.	"/codelibrary/includes/php/common.php");
require_once("/config.inc.php");


include ("app/includes/php/template.php");


# mysql = new connection to Mysql_Library()
$gMysql				=	new Mysql_Library();

$proclaim_id			= GetVariableString('proclaim_id',$_GET,"");


$data	=	$gMysql->queryRow("SELECT * FROM fp_flight_master_db_flight_info WHERE proclaim_id='$proclaim_id'  ",__FILE__,__LINE__);

if(empty($data)){
    gotoURL("/?pUpdate=IDNotFound");
    exit;
}
else
{
    $case_input_data    =   $data['case_input_date'];
    $case_key           =   $data['case_key'];
    $email              =   $data['email'];
    $forename           =   $data['forename'];
    $surname            =   $data['surname'];
    $status             =   $data['status'];
    $case_status        =   $data['case_status'];
    $affiliate          =   $data['affiliate'];
}



#variable options_names is equal to an array list with the drop down menus names held inside.
$options_names  =   array(
    '-- Select --',               #
    'Approved',                   #01
    'Pending',                    #02
    'Declined',                   #03
);


$options_values  =   array(
    '',                                                 # -- Select --
    'A',                                               # Approved
    'P',                                               # Pending
    'D',                                               # Declined
);

$select_string  =   '<select class="form-control" id="case_status" name="case_status" >';

$num    =   count($options_names);


for ($i=0;  $i < $num;  $i++)
{

    $name = $options_names[$i];

    $value = $options_values[$i];


    if ($case_status == $value) {
        $select_string .= "<option value='".$value."' selected=''>$name</option>";
    }


    else
    {
        $select_string .= "<option value='".$value."'>$name</option>";
    }

}

# variable select_string append </select>
# (closes the select input)
$select_string .= "</select>";


# variable template = function getTemplate points to file index.html
$template = getTemplate("affiliate_data.html");


# variable template = function getTemplate points to file header.html
# variable template = function getTemplate points to file footer.html
$header = getTemplate("app/templates/header.html");
$footer = getTemplate("app/templates/footer.html");


# variable template = string replace {{header}} with the variable $header
# variable template = string replace {{footer}} with the variable $footer
$template = str_replace("{{header}}", $header, $template);
$template = str_replace("{{footer}}", $footer, $template);
$template = str_replace("{{case_input_data}}", $case_input_data, $template);
$template = str_replace("{{proclaim_id}}", $proclaim_id, $template);
$template = str_replace("{{case_key}}", $case_key, $template);
$template = str_replace("{{email}}", $email, $template);
$template = str_replace("{{forename}}", $forename, $template);
$template = str_replace("{{surname}}", $surname, $template);
$template = str_replace("{{status}}", $status, $template);
$template = str_replace("{{case_status}}", $case_status, $template);
$template = str_replace("{{affiliate}}", $affiliate, $template);
$template = str_replace("{{select_string}}", $select_string, $template);



echo $template;
