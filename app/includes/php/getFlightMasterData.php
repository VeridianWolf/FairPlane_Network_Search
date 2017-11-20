<?php
function getFlightMasterData($order_id)
{
    # attempts to make a connection to the database
    global $gMysql;


    # gMysql attempting to SELECT the entire table called policy_document_details from proclaim_code, the info is stored in $data
    $data	=	$gMysql->queryRow("SELECT * from fp_flight_master_db_flight_info WHERE order_id='$order_id'",__FILE__,__LINE__);

    # if the data variable is empty return false;
    if(empty($data)) {
        return false;
    }


    # if not return the data
    return $data;

}