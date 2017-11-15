<?php
/**
 * Created by PhpStorm.
 * User: McMillan
 * Date: 27-Jan-17
 * Time: 9:23 AM
 */

# flight query type used to get data from database
define	('FLIGHT_QUERY_TYPE_FLIGHTNUMBER',		1);
define	('FLIGHT_QUERY_TYPE_AIRPORTS',			2);

# defines if we should use these
define	('USE_FLIGHTAWARE_API',					true);
define	('USE_AUSTRIA_API',						true);



class Flight_Library
{
	# we want to know which queries are getting hit the most. we probably want to know if we are successful in returning data?
	private	$queries_austria;
	private	$queries_austria_api;
	private	$queries_flightstats;
	private	$queries_flightaware;
	private	$queries_newdb;

	private	$flight_number;
	private	$flight_airline;
	private	$flight_split_airline_code;
	private	$flight_split_number;
	private	$flight_arr_code;
	private	$flight_dep_code;
	private	$flight_date;
	private	$flight_date_mysql;

	# these are for flightaware to get a day woth of flights
	private	$flight_date_epoch;
	private	$flight_date_epoch_eod;
	private	$flight_dep_code_icao;
	private	$flight_arr_code_icao;
	private	$flight_airline_icao;



	private	$query_type;
	private	$query_austria;
	private	$query_masterdb;
	private	$query_flightaware;

	private	$flightaware;
	private	$data_array;






	public function __construct($flight_number,$flight_airline,$flight_date,$flight_split_airline_code,$flight_split_number,$flight_arr_code,$flight_dep_code)
	{
		global 	$gMysql;


		$options = array(
			'trace' => true,
			'exceptions' => 0,
			'login' => 'boxlegal',
			'password' => '9ee8d6649c428d60eb1832b94f43ed510d8597f5',
		);
		$this->flightaware	= new SoapClient('http://flightxml.flightaware.com/soap/FlightXML2/wsdl', $options);



		# dates used
		$this->flight_date					=	$flight_date;
		$this->flight_date_mysql			=	date("Y-m-d G:i:s",strtotime(str_replace('/', '-', $flight_date)));
		$this->flight_date_epoch			=	strtotime(str_replace('/', '-', $flight_date));
		$this->flight_date_epoch_eod		=	strtotime("+1day", $this->flight_date_epoch);

		$this->flight_airline				=	$flight_airline;
		$this->flight_number				=	$flight_number;
		$this->flight_split_airline_code	=	$flight_split_airline_code;
		$this->flight_split_number			=	$flight_split_number;
		$this->flight_arr_code				=	$flight_arr_code;
		$this->flight_dep_code				=	$flight_dep_code;


	}

	# find flight data via this method
	# master will keep a neater version of all queries. austrian data will eventually be added daily.
	#
	public function findFlight()
	{
		$this->build_query();

		if	(($data =  $this->getFlightInMasterDB()) == NULL)
		{
#			if	(($data =  $this->getFlightInFlightAwareDB()) == NULL)
#			{
				if	(($data =  $this->getFlightsInAustriaAPI($this->flight_split_airline_code,$this->flight_split_number,$this->flight_date_mysql)) == NULL)
				{
					if	(($data =  $this->getFlightsInAustriaDB()) == NULL)
					{
					}
				}
#			}
		}

		return $data;
	}




	# build query.  so, based on initial criteria we will go for one of
	private function build_query()
	{
		$this->query_austria	=	"";
		$this->query_newdb		=	"";
		# make sure we have a flight date
		if	($this->flight_date)
		{
			# flight number? lets get route for this flight
			if	(($this->flight_split_airline_code) && ($this->flight_split_number))
			{
				$this->query_type			=	FLIGHT_QUERY_TYPE_FLIGHTNUMBER;

				# we need to set route variables based on flight num
	#			$this->setRouteViaFlightNum();

				# get the icao codes
	#			$this->flight_dep_code_icao			=	$gMysql->queryItem("select icao from fp_airports where code='$flight_dep_code'",__FILE__,__LINE__);
	#			$this->flight_arr_code_icao			=	$gMysql->queryItem("select icao from fp_airports where code='$flight_arr_code'",__FILE__,__LINE__);
	#			$this->flight_airline_icao			=	$gMysql->queryItem("select icao from fp_airlines where code='$flight_airline'",__FILE__,__LINE__);



				#		$ic 						=	new IataCodes(IATACODES_KEY, '6');
		#		$result 					=	$ic->api('flight_number=J2854');



				$this->query_austria		=	" DATE(ScheduledDeparture)=DATE('$this->flight_date_mysql') and Carrier='$this->flight_split_airline_code' and FlightNumber='$this->flight_split_number' ";
				$this->query_masterdb		=	" DATE(scheduled_departure)=DATE('$this->flight_date_mysql') and airline_code='$this->flight_split_airline_code' and flight_number='$this->flight_split_number' ";
				$this->query_flightaware	=	" {= dest $this->flight_arr_code_icao}  {= orig $this->flight_dep_code_icao} {= ogtd  $this->flight_date_epoch}  {ident {$this->flight_number}}";

			}
			# departure or arrival airport set.
			else if (($this->flight_dep_code) || ($this->flight_arr_code))
			{
				$this->query_type			=	FLIGHT_QUERY_TYPE_AIRPORTS;

				# must be careful to build exact query, so start with date
				$this->query_austria		=	" DATE(ScheduledDeparture)=DATE('$this->flight_date_mysql') ";
				$this->query_masterdb		=	" DATE(scheduled_departure)=DATE('$this->flight_date_mysql') ";
				$this->query_flightaware	=	" {= ogtd  $this->flight_date_epoch}  ";

				# both sewt
				if (($this->flight_dep_code) &&  ($this->flight_arr_code))
				{
					$this->query_austria		.=	" and ( DepartureAirport='$this->flight_dep_code' or ArrivalAirport='$this->flight_arr_code' ) ";
					$this->query_masterdb		.=	" and ( dep_airport_code='$this->flight_dep_code' or arr_airport_code='$this->flight_arr_code' ) ";
					$this->query_flightaware	.=	" {= orig $this->flight_dep_code_icao} {= ogtd  $this->flight_date_epoch}  ";

				}
				else if (($this->flight_dep_code) &&  (empty($this->flight_arr_code)))
				{
					$this->query_austria		.=	" and  DepartureAirport='$this->flight_dep_code'  ";
					$this->query_masterdb		.=	" and  dep_airport_code='$this->flight_dep_code'  ";
					$this->query_flightaware	.=	" {= orig $this->flight_dep_code_icao}  ";
				}
				else if ((empty($this->flight_dep_code)) && ($this->flight_arr_code))
				{
					$this->query_austria		.=	" and  ArrivalAirport='$this->flight_arr_code'  ";
					$this->query_masterdb		.=	" and  arr_airport_code='$this->flight_arr_code'  ";
					$this->query_flightaware	.=	" {= dest $this->flight_arr_code_icao}   ";
				}


				# do we have a specific airline also to check
				if	($this->flight_airline)
				{
					# check for airline
					$this->query_austria		.=	" and Carrier='$this->flight_airline' ";
					$this->query_masterdb		.=	" and airline_code='$this->flight_airline' ";
					$this->query_flightaware	.=	" {ident { $this->flight_airline* }}";

				}
			}
			# this means there is an error
			else
			{
				return false;
			}


			#


		}

	}

	# checks the flight for existence in master DB treat result as a possible array of two
	private function getFlightInMasterDB()
	{
		global $gMysql;

		$this->data_array	=	$gMysql->selectToArray("select * from fp_flight_master_db where ".$this->query_masterdb."",__FILE__,__LINE__);

		return $this->data_array;
	}






	# checks the flight
	private function getFlightsInAustriaDB()
	{
		global $gMysql;
		#
		$gMysql->setDB("newfairp_flightdata");
		$this->data_array	=	$gMysql->selectToArray("select 
		
		Id  				as	flight_id,
		Carrier				as	airline_code,
		FlightNumber		as 	flight_number,
		DepartureAirport	as	dep_airport_code,
		ArrivalAirport		as	arr_airport_code,
		ScheduledDeparture	as	scheduled_departure,
		ActualDeparture		as	actual_departure,
		ScheduledArrival	as	scheduled_arrival,
		ActualArrival		as	actual_arrival,
		ActualArrivalValue	as	aus_arrival_value,
		Delay				as	aus_delay,
		DistanceKm			as	distance,
		LastUpdate			as	last_update,
		
		'' as dep_status_code,
		0 as dep_delay,
		'' as arr_status_code,
		0 as arr_delay,
		'' as status_code,
		0 as delay,
		0 as num_flights
 
		   
	     from flights where ".$this->query_austria."",__FILE__,__LINE__);

		$gMysql->setDB(MYSQL_DBASE);

		if ($this->data_array)
		{
			# now push data into the new database so that it is formatted
			$this->insertDataArray($this->data_array);
			return $this->data_array;
		}

	}





	# checks the flightaware API and returns an array
	private function getFlightInFlightAwareDB()
	{
		if	(USE_FLIGHTAWARE_API == true)
		{

			$this->data_array	=	array();

			$num_flights	=	15;
			$offset			=	0;
			$data			=	array();


			$params 		= array(
		//	"query" 					=> 	$this->query_flightaware,

			"query"	=>  " {= dest $this->flight_arr_code_icao}  {= orig $this->flight_dep_code_icao} {> ogtd  $this->flight_date_epoch}  {ident {$this->flight_number}}",

			"howMany"					=> 	$num_flights,
			"offset"					=> 	$offset,

			);

			# get a bunch of flights
			while (($aircraft =	$this->getAircraft($params,$num_flights)) != NULL)
			{
				foreach ($aircraft as $airplane)
				{

					$a=1;

				}
			}
		}

		return $this->data_array;
	}





	private function getAircraft(&$params,$num_flights=1)
	{
		# increment the counter
		$this->flight_aware_queries++;

		$aircraft 	=	$this->flightaware->SearchBirdseyeInFlight($params)->SearchBirdseyeInFlightResult->aircraft;

		if	(is_array($aircraft))
		{
			# inc counter
			$params['offset']	+=	$num_flights;

			return $aircraft;
		}

	}




	# checks flight using the database YYYY-MM-DD and returns a structure
	# possibly add these to the database manually
	# there is the possibility of returning several flights, but v1.0 will just grab the first
	# v2.0 should return an array and we can then decide what to do by traversing it
	public function getFlightsInAustriaAPI($airline_code,$flight_number,$date)
	{
		if	(USE_AUSTRIA_API == true)
		{
			$converted_date		=	date( 'Y-m-d', strtotime($date) );
			$this->data_array	=	array();

			$curl = curl_init("http://affiliate.fairplane.net/Services/GetFlights.ashx?carrier=$airline_code&flightnr=$flight_number&date=$converted_date");
			curl_setopt($curl, CURLOPT_FAILONERROR, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			if	($flight_data 	=	curl_exec($curl))
			{
				if	($flight_data	 =	json_decode($flight_data,true))
				{
					# this can help to deduce if there was a leg of he journey responsible for delay
					# not all legs seem to have delay information stored, so we would have to find out some how.
					$num_segments	=	count($flight_data['segments']);

					foreach ($flight_data['segments'] as $flight)
					{
						# this needs to be converted and stored
						$flight_id 				=	$flight['key'];
						$dep_airport_code 		=	substr($flight['departure_airport'], 0, 3);
						$arr_airport_code 		=	substr($flight['arrival_airport'], 0, 3);
						$scheduled_departure	=	date( 'Y-m-d H:i:s', strtotime($flight['scheduled_time']) );
						$scheduled_arrival		=	date( 'Y-m-d H:i:s', strtotime($flight['scheduled_arrival_time']) );

						$distance 				=	$flight['distance'];
						# these may not be passed back
						if	(isset($flight['actual_arrival_time']))
						{
							$actual_arrival 		=	date( 'Y-m-d H:i:s', strtotime($flight['actual_arrival_time']) );
						}
						else
						{
							$actual_arrival 		=	"0000-00-00 00:00:00";
						}

						if	(isset($flight['actual_time']))
						{
							$actual_departure		=	date( 'Y-m-d H:i:s', strtotime($flight['actual_time']) );
						}
						else
						{
							$actual_departure 		=	"0000-00-00 00:00:00";
						}


						# reset these for each flight
						$departure_status_code	=	"";
						$delay_departure		=	0;
						$arrival_status_code	=	"";
						$delay_arrival			=	0;
						$flight_status_code		=	"";
						$flight_delay			=	0;

						# compute delay and status
						if	(isset($flight['departure_status_code']))
						{
							$departure_status_code	=	$flight['departure_status_code'];
							if	($departure_status_code == "DY")
							{
								$delay_departure	=	(strtotime($actual_departure) - strtotime($scheduled_departure)) /60;
							}
							else if ($departure_status_code == "CX")
							{
								$delay_departure	=	9999;
							}
						}
						if	(isset($flight['arrival_status_code']))
						{
							$arrival_status_code	=	$flight['arrival_status_code'];
							if	($arrival_status_code == "DY")
							{
								$delay_arrival	=	(strtotime($actual_arrival) - strtotime($scheduled_arrival)) /60;
							}
							else if ($arrival_status_code == "CX")
							{
								$delay_arrival	=	9999;
							}
						}

						# status of flight - cancellation overrides a delay
						if	($delay_departure > $delay_arrival)
						{
							$flight_status_code		=	$departure_status_code;
							$flight_delay			=	$delay_departure;
						}
						else if ($delay_arrival > $delay_departure)
						{
							$flight_status_code		=	$arrival_status_code;
							$flight_delay			=	$delay_arrival;
						}



						$data['flight_id']				=	$flight_id;
						$data['airline_code']			=	$airline_code;
						$data['flight_number']			=	$flight_number;
						$data['dep_airport_code']		=	$dep_airport_code;
						$data['arr_airport_code']		=	$arr_airport_code;
						$data['scheduled_departure']	=	$scheduled_departure;
						$data['actual_departure']		=	$actual_departure;
						$data['scheduled_arrival']		=	$scheduled_arrival;
						$data['actual_arrival']			=	$actual_arrival;
						$data['distance']				=	$distance;

						$data['dep_status_code']		=	$departure_status_code;
						$data['dep_delay']				=	$delay_departure; # pick greatest delay value
						$data['arr_status_code']		=	$arrival_status_code;
						$data['arr_delay']				=	$delay_arrival; # pick greatest delay value
						$data['status_code']			=	$flight_status_code;
						$data['delay']					=	$flight_delay;
						$data['num_flights']			=	$num_segments;

						$data['aus_arrival_value']		=	"";
						$data['aus_delay']				=	"";



						# add to array
						$this->data_array[]				=	$data;
					}

					$this->insertDataArray();
				}

				return	$this->data_array;
			}
		}
	}







	# inserts data into database
	private function insertDataArray()
	{
		global $gMysql;

		foreach ($this->data_array as $data)
		{
			$flight_id 				=	$data['flight_id'];
			$airline_code 			=	$data['airline_code'];
			$flight_number 			=	$data['flight_number'];
			$dep_airport_code 		=	$data['dep_airport_code'];
			$arr_airport_code 		=	$data['arr_airport_code'];
			$scheduled_departure	=	$data['scheduled_departure'];
			$actual_departure		=	$data['actual_departure'];
			$scheduled_arrival 		=	$data['scheduled_arrival'];
			$actual_arrival 		=	$data['actual_arrival'];
			$aus_arrival_value 		=	$data['aus_arrival_value'];
			$aus_delay 				=	$data['aus_delay'];
			$distance 				=	$data['distance'];



			$dep_status_code		=	$data['dep_status_code'];
			$dep_delay				=	$data['dep_delay'];
			$arr_status_code		=	$data['arr_status_code'];
			$arr_delay				=	$data['arr_delay'];
			$status_code			=	$data['status_code'];
			$delay					=	$data['delay'];
			$num_flights			=	$data['num_flights'];



			# place into master db
			$gMysql->insert("replace into fp_flight_master_db 
						(flight_id,airline_code,flight_number,dep_airport_code,arr_airport_code,scheduled_departure,actual_departure,
						scheduled_arrival,actual_arrival,aus_arrival_value,aus_delay,distance,dep_status_code,dep_delay,arr_status_code,arr_delay,status_code,delay,num_flights,last_update)

						values('$flight_id','$airline_code','$flight_number','$dep_airport_code','$arr_airport_code','$scheduled_departure','$actual_departure','$scheduled_arrival',
						'$actual_arrival','$aus_arrival_value','$aus_delay','$distance','$dep_status_code','$dep_delay','$arr_status_code','$arr_delay','$status_code','$delay','$num_flights',NOW())
						",__FILE__,__LINE__);
		}
	}










	# return data as a string
	public function getFormattedDataString()
	{
		$string	=	"";

		foreach ($this->data_array as $data)
		{
			$flight_id 				=	$data['flight_id'];
			$airline_code 			=	$data['airline_code'];
			$flight_number 			=	$data['flight_number'];
			$dep_airport_code 		=	$data['dep_airport_code'];
			$arr_airport_code 		=	$data['arr_airport_code'];
			$scheduled_departure	=	$data['scheduled_departure'];
			$actual_departure		=	$data['actual_departure'];
			$scheduled_arrival 		=	$data['scheduled_arrival'];
			$actual_arrival 		=	$data['actual_arrival'];
			$aus_arrival_value 		=	$data['aus_arrival_value'];
			$aus_delay 				=	$data['aus_delay'];
			$distance 				=	$data['distance'];

			$dep_status_code		=	$data['dep_status_code'];
			$dep_delay				=	$data['dep_delay'];
			$arr_status_code		=	$data['arr_status_code'];
			$arr_delay				=	$data['arr_delay'];
			$status_code			=	$data['status_code'];
			$delay					=	$data['delay'];
			$num_flights			=	$data['num_flights'];

			$string	.=	"
			
			flight_id:$flight_id<br>
			airline_code:$airline_code<br>
			flight_number:$flight_number<br>
			dep_airport_code:$dep_airport_code<br>
			arr_airport_code:$arr_airport_code<br>
			scheduled_departure:$scheduled_departure<br>
			actual_departure:$actual_departure<br>
			scheduled_arrival:$scheduled_arrival<br>
			actual_arrival:$actual_arrival<br>
			dep_status_code: $dep_status_code<br>
			dep_delay:$dep_delay mins<br>
			arr_status_code:$arr_status_code<br>
			arr_delay:$arr_delay<br>
			status_code:$status_code<br>
			delay:$delay<br>
			distance:$distance<br>
			num_flights:$num_flights<br>
			<br>
			--------------------------------------<br>
				
			";
		}

		return $string;
	}


}