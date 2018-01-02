<?php
# include these files from codelibrary
require_once($_SERVER["DOCUMENT_ROOT"]	.	"../codelibrary/includes/php/autoload.php");
require_once($_SERVER["DOCUMENT_ROOT"]	.	"../codelibrary/includes/php/common.php");
require_once ("../config.inc.php");

# mysql = new connection to Mysql_Library()
$gMysql				=	new Mysql_Library();

# default return code
$returnCode			=	"error";

# this is the return array with all values
$returnArray		=	array();

# initialize this variable, as it may be added to later.
# message variable currently empty
$message			=	"";

# $order_id,
# $email
# $surname
# are all empty variables
$order_id = "";
$email = "";
$surname = "";


# get the variables from $_POST
# gets the order_id, email and surname that are entered into the input fields
$order_id			= GetVariableString('search_order_id',$_POST,"");
$email			    = GetVariableString('search_email',$_POST,"");
$surname			= GetVariableString('search_surname',$_POST,"");


# searches SQL table fp_flight_master_db_flight_info WHERE
$sqlString	=	" select * from fp_flight_master_db_flight_info WHERE ";

# if $order_id is not empty then add $order_id to $sqlString
if (!empty($order_id))
{
    $sqlString		.=	" order_id like '%" . $order_id ."%' ";
}

# if $email is not empty then add $email to $sqlString
if (!empty($email))
{
    # if order_id is not empty then append AND to $sqlString
    if (!empty($order_id))
    {
        $sqlString .=" AND ";
    }

    # sqlString concatenated email like $email (using wildcard charcaters '%"
    $sqlString		.=	"  email like '%" . $email ."%' ";
}

# if $surname is not empty then add $surname to $sqlString
if (!empty($surname))
{
    # if order_id or $email are are not empty then add AND to $sqlString
    if ((!empty($order_id)) || (!empty($email)))
    {
        $sqlString .=" AND ";
    }

    # sqlString concatenated surname like $surname (using wildcard charcaters '%"
    $sqlString		.=	"  surname like '%" . $surname ."%' ";
}

# if $order_id and $email and $surname are empty then add sql syntax 1 = 1 LIMIT 100 to $sqlString
if (empty($order_id) && empty($email) && empty($surname))
{
    $sqlString .= " 1 = 1 LIMIT 100 ";
}

# this variable is a two dimensional array
# data_2d_array is equal to gMysql and calls function selectToArray
# selectToArray goes through the array and finds either the order_id, email or surname (uses wildcard chars e.g. '%order%' they exist in the table.
$data_2d_array	=	$gMysql->selectToArray($sqlString,__FILE__,__LINE__);

# variable $num_items = counts the items within $data_2d_array (whole table)
$num_items = count($data_2d_array);

# start table (outside of foreach loop)
$html = '<table id="myTable" class="display table" cellspacing="0" width="100%">';


# start table headers
# start table headers (outside of loop also as these won't change)
$html .= "    <thead> 
                    <tr>
                        <th>Order Id</th>
                        <th>Email Address</th>
                        <th>Surname</th>
                    </tr>
                </thead>";

# open the tbody tag
$html .= "<tbody>";

# this loop will go through the data_2d_array (table)
# and store each arrays value as a variable ($data)
foreach ($data_2d_array as $data)
{
    # grabs the data from each column of the database id / email / surname
    $order_id = $data['order_id'];
    $email = $data['email'];
    $surname = $data['surname'];



    # this part of the table will be filled with the info that is grabbed from the database using the code above
    $html .= "   <tr>
                    <td>$order_id<td/>
                    <td>$email<td/>
                    <td>$surname<td/>
                 </tr>";
}

# close the tbody tag
$html .= "</tbody>";

# finish table - close the table html tag
$html .= '</table>';


# returns the data in this array to the javascript ajax
$returnArray    =   array	(

    # where result is replace with variable $html
    "result" => $html,

);


# return array is encoded so that jquery can understand it
echo json_encode($returnArray);



