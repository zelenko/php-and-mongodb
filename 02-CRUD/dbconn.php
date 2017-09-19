<?php
ini_set('display_errors','true');
error_reporting(E_ALL);

$messages = array(
	1=>'Record deleted successfully',
	2=>'Error occured. Please try again.', 
	3=>'Record saved successfully.',
    4=>'Record updated successfully.', 
    5=>'All fields are required.' );


$mongoDbname  =  'onlinestore';
$mongoTblName =  'products';

$manager = new MongoDB\Driver\Manager("mongodb://user:PASSWORD@SERVER1.mongodb.net:27017,SERVER2.mongodb.net:SERVER3.mongodb.net:27017/onlinestore?ssl=true&replicaSet=Cluster0-shard-0&authSource=admin",
	array('connectTimeoutMS' => '30000',
	'socketTimeoutMS' => '30000'));