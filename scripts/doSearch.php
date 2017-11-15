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

#empty array called data
$data = array();

# variable $num_items = count the items within $returnArray
$num_items = count($returnArray);

#start table outside of foreach loop
$html = '<table>';


#start table headers outside of loop also as these won't change
$html .= "<tr>";
$html .= "<th>Order Id<th/>";
$html .= "<th>Email Address<th/>";
$html .= "<th>Surname<th/>";
$html .= "</tr>";


foreach ($returnArray as $data)
{
    # grabs the data from each column of the database id / email / surname
    $id = $data['id'];
    $email = $data['email'];
    $surname = $data['surname'];

    # this part of the table will be filled with the info that is grabbed from the database using the code above
    $html .= "<tr>";
    $html .= "<td>$id<td/>";
    $html .= "<td>$email<td/>";
    $html .= "<td>$surname<td/>";
    $html .= "</tr>";
}

    #finish table - close the table html tag
    $html .= '</table>';



# returns the data in this array to the javascript ajax
$returnArray    =   array	(

    "{{table}}" => $html

);


# return array is encoded so that jquery can understand it
echo json_encode($returnArray);



