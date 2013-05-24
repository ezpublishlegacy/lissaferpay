<?php
/*
 * Created on 28.11.2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


$FunctionList = array();


$FunctionList['paymentInfo'] = array(
      'name' => 'paymentInfo',
      'call_method' => array( 
      'include_file' => 'extension/lissaferpay/classes/lissaferpaygateway.php',
      'class' => 'lisSaferpayGateway',
      'method' => 'paymentInfo' ),
      'parameter_type' => 'standard',
      'parameters' => array(array( 'name' => 'order_id',
                                      'required' => true,
                                      'default' => false ) ));
?>