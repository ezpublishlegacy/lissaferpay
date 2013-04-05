<?php


$Module = array( 'name' => 'paymentpage' );



$ViewList = array();
$ViewList['getAuth'] = array (
                            'script' => 'getAuth.php',
                            'params' => array()
                                );



$ViewList = array();
$ViewList['returnok'] = array (
		'script' => 'returnok.php'

);

$ViewList['returnfail'] = array (
		'script' => 'returnfail.php'
		 
);
/*

$ViewList['overview'] = array(

    'script' => 'overview.php',

    'params' => array ( ) ); */



?>

