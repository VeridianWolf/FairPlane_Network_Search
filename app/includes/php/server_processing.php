<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'fp_flight_master_db_flight_info';

// Table's primary key
$primaryKey = 'id';


# this excludes all claims where there is NO order_id or NO affiliate
# no order_id really means there is no affiliate also, so it's a double safety measure

$where = " order_id !='' and affiliate !='' ";
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes


# $d = contains the variable (db) that has been extracted from the database
# dt is the index for each item, starting at 0
# $row[]  use this to pick an item from the row of variables
# return - always return something if you are using 'formatter'


$columns = array(
	array(
		'db'        => 'case_input_date',
		'dt'        => 0,
		'formatter' => function( $d, $row ) {

			if	($d == "0000-00-00 00:00:00")
			{
				$string	=	'--';

				return	$string;
			}
			return date( 'd/m/Y', strtotime($d));
		},
	),
    array( 'db' => 'case_key', 'dt' => 1 ),
	array( 'db' => 'email',  'dt' => 2 ),
	array( 'db' => 'forename',  'dt' => 3 ),
	array(
		'db'        => 'surname',
		'dt'        => 4,
		'formatter' => function( $d, $row ) {

			$forename		=	$row[3];

			if	(strcmp(strtolower($forename),"john") == 0)
			{
				return "****" . $d . "****";

			}
			return $d;

		},
	),


	array(
		'db'        => 'status',
		'dt'        => 5,
		'formatter' => function( $d, $row ) {

			$status	=	strtolower($d);

			return $status;

		},
	),

	array(
		'db'        => 'case_status',
		'dt'        => 6,
		'formatter' => function( $d, $row )
		{

			# some claims can have no order_id. These claims are ones that are not made via the website
			$order_id		=	$row[8];
			# if there is no order_id, then we should bypass this bit
			if (!empty($order_id))
			{
				if	($d == "D")
				{
					return "<button class='btn btn-danger'>Declined</button>";
				}
				else if	($d == "A")
				{
					return "<button class='btn btn-success'>Approved</button>";
				}
				else if	($d == "P")
				{
					return "<button class='btn btn-warning'>Pending</button>";
				}
			}

			return "No Order ID";

		},
	),


	array( 'db' => 'affiliate',  'dt' => 7 ),

    array( 'db' => 'order_id',  'dt' => 8 ),

    array( 'db' => 'proclaim_id',  'dt' => 9 ),




);

// SQL server connection information
$sql_details = array(
    'user' => '7apO3nBbB2a',
    'pass' => 'PIKkip91sD312',
    'db'   => 'newfairp_website',
    'host' => 'localhost'
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "../codelibrary/includes/php/ssp.class.php");

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns , $where)
);
