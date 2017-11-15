<?
// NAME: Timer()
// VERSION: 1.0
// AUTHOR: J de Silva
// WEBSITE: [url]http://www.desilva.biz/[/url]
// DESCRIPTION: A simple PHP SCRIPT TIMER / BENCHMARK class.
//              See notes below.
// TYPE: class
// --------------------

// Some constants
if( !defined( 'NL' ) ):
  // newlines
  define( 'NL', "\r\n" );
endif;

class Timer_Library
{
	var $st; // START time

	public function __construct()
	{
		// set the START time
		$this->st = $this->pGetTime();
	}

  /**
    * Displays the timer on a web page; you may specify the HTML
    * by including it as a parameter.
    *
    * e.g.
    * $t->DisplayTimer( '</p>Page created in [DEC=5]</p>' );
    *
    * You may place this repeatedly at many points in your script,
    * (in the event you want to time some parts off your script incrementally).
    *
    * The [DEC=5] bit is where the results are displayed in the
    * given HTML string, meaning 5 decimal places! If you just
    * want to show 4 decimal places in the result, change the
    * parameter string to include [DEC=4], etc.
  */
  function DisplayTimer( $html='' )
  {
    $format = '<p style="text-align:center">Script runtime: %.5f %s</p>';
    if( !empty($html) ):
      $format = preg_replace( '/\[dec=([0-9]+)\]/i', '%.$1f %s', $html, 1 );
    endif;
    $time = $this->pCalcTime();
    $sec = ( ($time > 1) ? 'secs.' : 'sec.' );
    printf( $format.NL, $time, $sec );
  }

  /**
    * This is the same as above, except only useful if you need
    * the results / variable to add into a template (i.e. minus
    * any html).
    *
    * e.g.
    * $variable = $t->ReturnTimer( 5 );
    *
    * The parameter is the number of decimal places in
    * the returned value
    *
    * See sample codes in next post.
    */
  function ReturnTimer( $decimal=5 )
  {
    if( !is_int($decimal) ):
      $decimal = 5;
    endif;
    $format = '%.'.$decimal.'f %s';
    $time = $this->pCalcTime();
    $sec = ( ($time > 1) ? 'secs.' : 'sec.' );
    return sprintf( $format, $time, $sec );
  }

  function pCalcTime()
  {
    return ( $this->pGetTime() - $this->st );
  }

  function pGetTime()
  {
    $mt = explode( ' ', microtime() );
    return $mt[1] + $mt[0];
  }
}
