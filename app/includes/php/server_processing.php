<?php

/* * * * * * * * * * * * * * * * * * * * * * *
 *  DATATABLES SERVERSIDE PROCESSING SCRIPT  *
 * * * * * * * * * * * * * * * * * * * * * * */

# database table to use
$table = 'fp_flight_master_db_flight_info';

# table's primary key
$primaryKey = 'id';


# this excludes all claims where there is NO order_id or NO affiliate
# no order_id really means there is no affiliate also, so it's a double safety measure
$where = " order_id !='' and affiliate !='' ";

/*
 * BELOW IS AN EXPLANATION OF HOW THE FOLLOWING $columns ARRAY LIST WORKS
 *
 */

# Array of database columns which should be read and sent back to DataTables.
# The `db` parameter represents the column name in the database, while the `dt`
# parameter represents the DataTables column identifier. In this case simple indexes
# $d = contains the variable (db) that has been extracted from the database
# dt = the index for each item, starting at 0
# $row[]  use this to pick an item from the row of variables
# return - always return something if you are using 'formatter'

#
$columns = array(
	array(
		'db'        => 'case_input_date',
		'dt'        => 0,
		'formatter' => function( $d, $row ) {
            #
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

			# if you search for name john the it will return the surname in the table with **** either side ****
			if	(strcmp(strtolower($forename),"john") == 0)
			{
				return "****" . $d . "****";

			}
			return $d;

		},
	),

    # array ( database column status, row index 5 formatter function $d, $row)
    # variable $status is equal to function strtolower($d) (converts the strings shown in this column as lowercase)
    # always return is using formatter
	array(
		'db'        => 'status',
		'dt'        => 5,
		'formatter' => function( $d, $row ) {

			$status	=	strtolower($d);

			return $status;

		},
	),
    # array ( database column case_status, row index 6
	array(
		'db'        => 'case_status',
		'dt'        => 6,
		'formatter' => function( $d, $row )
		{

			# some claims can have no order_id. These claims are ones that are not made via the website
			$order_id		=	$row[8];
			# if there is no order_id, then we should bypass this bit
            # if order_id is not empty
			if (!empty($order_id))
			{
			    # D = declined
                # A = Approved
                # P = Pending

			    # if $d is == to D
                # return button with btn-danger class
				if	($d == "D")
				{
					return "<button class='btn btn-danger'>Declined</button>";
				}
                # else if $d is == to A
                # return button with btn-success class
                else if	($d == "A")
				{
					return "<button class='btn btn-success'>Approved</button>";
				}
                # else if $d is == to P
                # return button with btn-warning class
                else if	($d == "P")
				{
					return "<button class='btn btn-warning'>Pending</button>";
				}
			}

			return "No Order ID";

		},
	),

    # array ( database column name affiliate,  row index 7 )
	array( 'db' => 'affiliate',  'dt' => 7 ),

    # array ( database column name order_id,  row index 8 )
    array( 'db' => 'order_id',  'dt' => 8 ),

    # array ( database column name proclaim_id,  row index 9 )
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
