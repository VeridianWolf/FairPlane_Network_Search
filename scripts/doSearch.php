<?php
# order_id, email and surname are all empty variables
$order_id = "";
$email = "";
$surname = "";

# searches SQL table fp_flight_master_db_flight_info WHERE
$sqlString	=	" select * from fp_flight_master_db_flight_info WHERE ";



#if $order_id is not empty then add $order_id to $sqlString
if (!empty($order_id))
{
    $sqlString		.=	" order_id like '%" . $order_id ."%' ";
}
#if $email is not empty then add $email to $sqlString
if (!empty($email))
{
    $sqlString		.=	" AND email like '%" . $email ."%' ";
}
#if $surname is not empty then add $surname to $sqlString
if (!empty($surname))
{
    $sqlString		.=	" AND surname like '%" . $surname ."%' ";
}

$data = array();

$num_items = count($returnArray);

#start table
$html = '<table>';

#1
$html .= '<tr>';
$html .= '<th><th/>';
$html .= '<th><th/>';
$html .= '<th><th/>';
$html .= '</tr>';

foreach ($returnArray as $data)
{
    $id = $data['id'];
    $email = $data['email'];
    $surname = $data['surname'];

    # build html string


    #2
    $html .= "<tr>";
    $html .= "<td>$id<td/>";
    $html .= "<td>$email<td/>";
    $html .= "<td>$surname<td/>";
    $html .= "</tr>";
}

    #finish table
    $html .= '</table>';



# returns the data in this array to the javascript ajax
$returnArray    =   array	(

    "{{table}}" => $html

);
# return array is encoded so that jquery can understand it
echo json_encode($returnArray);



