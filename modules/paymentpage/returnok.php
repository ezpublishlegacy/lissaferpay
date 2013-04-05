<?php
/**
 *  lis Paymentpage returnok ...
 *  
 *  recieves a POST request from the ACS and redierects if OK to /shop/checkout
 */


$_SESSION['paymentpage']['return'] = 'OK';
$_SESSION['paymentpage']['data']= urlencode($_REQUEST['DATA']);
$_SESSION['paymentpage']['signature']= $_REQUEST['SIGNATURE'];

header('Location: /shop/checkout');
eZExecution::cleanExit();
?>