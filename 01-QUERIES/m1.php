<?php

require_once('dbconn.php');

$ns = 'test.product';

// Create query object with all options:
$query = new \MongoDB\Driver\Query(
        ['_id' => ['$lt' => 10000000]], // query (empty: select all)
        [ 'sort' => [ '_id' => 1 ], 'limit' => 100000 ] // options
);

// Execute query and obtain cursor:
$cursor = $manager->executeQuery( $ns, $query );
foreach ($cursor as $doc) {
    //var_dump($doc);
	
	//$result = json_decode($doc, true);
	echo '-- '.$doc->model . ' ' . $doc->description.'<br />';
}

//db.product.find().limit(5).pretty()
/*
$query = array("_id" => "81540");

$filter = ['_id' => ['$lt' => 11000]];
$options = [
    'projection' => ['_id' => 0],
    'sort' => ['_id' => -1],
];

$query = new MongoDB\Driver\Query($filter, $options);
$cursor = $manager->executeQuery('db.collection', $query);

foreach ($cursor as $document) {
    var_dump($document);
}


$cursor = $collection->find($query);
foreach ($cursor as $doc) {
   var_dump($doc);
}
*/

?>