<?php
	set_time_limit(0);
	ini_set("memory_limit","1024M");
	ini_set("soap.wsdl_cache_enabled", "0");
	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', true);
	date_default_timezone_set("America/Sao_Paulo");
	
	
	define('DS', DIRECTORY_SEPARATOR);
	define('PS', PATH_SEPARATOR);
	//define('BASEPATH', getcwd() . DS);
	
	
	define('BASEPATH', dirname(dirname(__FILE__)) . DS);
	
	// directory setup and class loading
	set_include_path(
		 'd:' . DS . 'library' . DS
		 . PS . BASEPATH . get_include_path() . DS
		 . PS . '.');
		 
	include('zend/exception.php');
	include('zend/debug.php');
	include('zend/TimeSync.php');
	include('zend/TimeSync/exception.php');
	include('zend/TimeSync/Protocol.php');
	include('Zend/TimeSync/Sntp.php');
	include('Zend/TimeSync/ntp.php');
	include('Zend/Loader.php');
	include('Zend/Date/DateObject.php');
	include('Zend/Date.php');
	include('Zend/Locale/Data/Translation.php');
	include('Zend/Locale.php');
	include('Zend/Registry.php');
	

 // ******************************************************
 // * NTP Interface Class for PHP                        *
 // *  (Written by Brian Haase on June 22, 2004          *
 // *                                                    *
 // ******************************************************
$xportlist = stream_get_transports();
print_r($xportlist);
 class NTP_Core
 {
    var $Server;
    var $Port = 318;
    var $Timeout = 10;

    var $Time = "";
    var $Timestamp = 0;
    
    var $Error = "";
    
    function lookup( $Server )
    {
       $this->Error = "";
       $this->Server = $Server;
       
       $_Success = FALSE;
       $_Time = "";

       $_Timeout = time();
       
       $Fp = fsockopen( 'udp://' . $this->Server, $this->Port, $errno, $errstr, $this->Timeout );
       
       if ( !$Fp )
       {
          $this->Error = $errno . " : " . $errstr;
          return FALSE;
       }
       
       for ( ; time() <= ($_Timeout + $this->Timeout) ; )
       {
          $_Time .= fgets( $Fp, 2096 );
          if ( feof( $Fp ) ) break;
       }

       if ( $Fp ) fclose( $Fp );
         
       if ( $_Time <> "" )
       {
          $this->Time = trim($_Time);
          return TRUE;
       }

       $this->Error = "N/A : NTP PHP Class Socket Timeout";
       return FALSE;
    }
    
    function format( )
    {
       // Tue Jun 22 07:19:36 UTC 2004
       // Tue Jun 22 07:19:51 2004

       $_Fields = explode( ' ', $this->Time );
       $_Subfields = explode( ':', $_Fields[3] );
       
       // Discard the day of week - $_Fields[0];
       $Month = $_Fields[1];
       $Day = $_Fields[2];
       $Hour = $_Subfields[0];
       $Min = $_Subfields[1];
       $Sec = $_Subfields[2];
       
       if ( count( $_Fields ) == 6 ) {
         $Zone = $_Fields[4];
         $Year = $_Fields[5];
       } else {
         $Zone = "";
         $Year = $_Fields[4];
       }
         
       $MTable = array( "", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" );
       $Month = array_search( $Month, $MTable );

       $this->Timestamp = mktime( $Hour, $Min, $Sec, $Month, $Day, $Year );

       return TRUE;
    }
 }
 
$NTP = new NTP_Core;

 // Request the time from a NTP Server
 $Result = $NTP->lookup( "sct.trf1.gov.br" );
 
 if ( $Result)
 {
    // Output the Server Time
    echo "<B>Server Time: </B>" . $NTP->Time . "<BR>\n";

    // Try to reformat the time into a useable timestamp
    if ( $NTP->Format() )
    {
       echo "<B>Formatted: </B>" . date('l dS of F Y h:i:s A', $NTP->Timestamp);
    }
    else
    {
       echo "Bogus Time Format - Unable to Format.";
    }
 } else {
    echo "<B>NTP Time Lookup Failed - </B>" . $NTP->Error;
 }
	

  // ntp time servers to contact
  // we try them one at a time if the previous failed (failover)
  // if all fail then wait till tomorrow
  
  $time_servers = array("sct"  => "sct.trf1.gov.br:318",
                        "sct2" => "sct2.trf1.gov.br:318");
	

  $server = new Zend_TimeSync($time_servers /*array('br01' => '1.br.pool.ntp.org',
                                    'br02' => '1.south-america.pool.ntp.org',
  									'br03' => '3.south-america.pool.ntp.org')*/);
//$server->addServer('1.br.pool.ntp.org', 'additional');


try {
    $result = $server->getDate();
    echo $result->getIso();
    print_r(Zend_TimeSync::getOptions());
} catch (Zend_TimeSync_Exception $e) {

    $exceptions = $e->get();

    foreach ($exceptions as $key => $myException) {
        echo $myException->getMessage();
        echo '<br />';
    }
}