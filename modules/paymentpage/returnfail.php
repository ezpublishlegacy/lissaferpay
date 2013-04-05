<?php
/**
 *  lis Paymentpage returnfail ...
 *  
 *  recieves a POST request from the ACS and redierects if NOT OK to ...??
 */




$_SESSION['paymentpage']['return'] = 'FAIL';


header('Location: /shop/checkout');
eZExecution::cleanExit();
?>