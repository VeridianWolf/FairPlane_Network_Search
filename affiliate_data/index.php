<?php
# layout - firstly we add the required/include files
# include these files from codelibrary
require_once($_SERVER["DOCUMENT_ROOT"]	.	"../codelibrary/includes/php/autoload.php");
require_once($_SERVER["DOCUMENT_ROOT"]	.	"../codelibrary/includes/php/common.php");
require_once("../config.inc.php");


include ("../app/includes/php/template.php");

# next we must open a connection to the sql database - Mysql_Library
# mysql = new connection to Mysql_Library()
$gMysql				=	new Mysql_Library();

# here we use the function GetVariableString and this line of code GETS the proclaim id from the variable proclaim_id
$proclaim_id			= GetVariableString('proclaim_id',$_GET,"");

# now you can begin the query the database for what it is you want.
# data is qual to the proclaim_id in the table fp_flight_master_db_flight_info
$data	=	$gMysql->queryRow("SELECT * FROM fp_flight_master_db_flight_info WHERE proclaim_id='$proclaim_id'  ",__FILE__,__LINE__);


# now if the data we are trying to GET is not there we must tell the code to do something about this.
# for this we use the if else statement
if(empty($data)){
    # this line says that if variable $data is empty then you it should refresh the url to say /?pUpdate=IDNotFound and refresh the page
    gotoURL("/?pUpdate=IDNotFound");
    exit;
}
# else the variable the variable $case_input_data is equal to the data from the database under case_input_data etc ...
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


# variable options_names is equal to an array list with the drop down menus names held inside.
$options_names  =   array(
    '-- Select --',               #
    'Approved',                   #01
    'Pending',                    #02
    'Declined',                   #03
);
# variable options_values is equal to the array list of drop down menu values.
$options_values  =   array(
    '',                                                 # -- Select --
    'A',                                               # Approved
    'P',                                               # Pending
    'D',                                               # Declined
);

# this variable is used to build the drop down
$select_string  =   '<select class="form-control" id="case_status" name="case_status" >';

# here we use count to go through the dropdown options stored in the array list we made earlier
$num    =   count($options_names);

# and now we can begin our loop
# for ($i=0; when i is less than num; add 1 ($i++) )
for ($i=0;  $i < $num;  $i++)
{
    # variable $name is equal to $option_names[$i]
    $name = $options_names[$i];
    # variable $value is equal to $options_values[$i]
    $value = $options_values[$i];

    # if variable case_status is equal to the variable value
    # then append to variable $select_string <option value='".$value."' selected=''>$name</option>
    if ($case_status == $value) {
        $select_string .= "<option value='".$value."' selected=''>$name</option>";
    }

    # else
    else
    {
        # append <option value='".$value."'>$name</option> to variable select_string
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
$header = getTemplate("../app/templates/header.html");
$footer = getTemplate("../app/templates/footer.html");

# $template variable is using a string replacement function to find the {{string}} and replace it with the content of the variable.
# variable template = string replace {{header}} with the variable $header
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
