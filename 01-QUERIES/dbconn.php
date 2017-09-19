<?php
ini_set('display_errors','true');
error_reporting(E_ALL);

echo '<p><a href="index.php">Home</a> | <a href="m1.php">m1</a> | <a href="m2.php">m2</a> | <a href="m.php">m</a>-<a href="m5.php">m5</a> | <a href="m3.php">m3</a> | <a href="m4.php">m4</a> | <br /></p>';

$mongoDbname  =  'onlinestore';
$mongoTblName =  'products';

$manager = new MongoDB\Driver\Manager("mongodb://user:password@127.0.0.1:27017/onlinestore?ssl=false&authSource=admin",
	array('connectTimeoutMS' => '30000',
	'socketTimeoutMS' => '30000'));