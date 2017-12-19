<?php
# include these files from codelibrary
require_once($_SERVER["DOCUMENT_ROOT"]	.	"../codelibrary/includes/php/autoload.php");
require_once($_SERVER["DOCUMENT_ROOT"]	.	"../codelibrary/includes/php/common.php");
require_once ("../config.inc.php");

# Opens new connection to database
$gMysql				=	        new Mysql_Library();

# $proclaim_id variable is equal to the proclaim_id from $POST (uses the GetVariableString function)
# $case_status variable is equal to the case_status from $POST (uses the GetVariableString function)
$proclaim_id		=           GetVariableString('proclaim_id',$_POST,"");
$case_status		=           GetVariableString('case_status',$_POST,"");

# returns the data in this array to the javascript ajax
$returnArray    =   array	(

    # where result is replace with variable $html
    "message" => "Claim has been changed to: "

);


# data variable is equal to updating the database with the case status that has been selected from the dropdown
$gMysql->update("UPDATE fp_flight_master_db_flight_info SET  case_status='$case_status' WHERE proclaim_id='$proclaim_id'",__FILE__,__LINE__);


# return array is encoded so that jquery can understand it
echo json_encode($returnArray);