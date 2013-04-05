<?php
/*
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


$FunctionList = array();


$FunctionList['paymentInfo'] = array(
      'name' => 'paymentInfo',
      'call_method' => array( 
      'include_file' => 'extension/paymentpage/classes/paymentpagegateway.php',
      'class' => 'PaymentPageGateway',
      'method' => 'paymentInfo' ),
      'parameter_type' => 'standard',
      'parameters' => array(array( 'name' => 'order_id',
                                      'required' => true,
                                      'default' => false ) ));

?>