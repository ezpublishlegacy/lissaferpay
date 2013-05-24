<?php
/**
 *  lis Saferpay getAuth ...
 *  
 *  recieves a POST request from the ACS and redierects if OK to /shop/checkout
 */

$ini = eZINI::instance( 'lissaferpay.ini' );
$siteaccess = $ini->variable('lissaferpay','siteaccess');


$saferpay = new lissaferpayHandler();

$request = simplexml_load_string(urldecode($_REQUEST['DATA']));

$saferpay->logger->writeTimedString($request,"REQUEST");

$_SESSION['lissaferpay']['MPI_SESSIONID'] = (string)$request['MPI_SESSIONID'];
$_SESSION['lissaferpay']['MPI_LIABILITYSHIFT'] = (string)$request['MPI_LIABILITYSHIFT'];

header('Location: /'.$siteaccess.'/shop/checkout');
eZExecution::cleanExit();
?>