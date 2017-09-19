<?php
require_once('dbconn.php');

$filter = [
    'author' => 'bjori',
    'views' => [
        '$gte' => 100,
    ],
];

$filter = [
	'_id' => ['$lt' => 10000000]]; // query (empty: select all)

$options = [
    /* Only return the following fields in the matching documents */
    'projection' => [
        '_id' => 1,
        'model' => 1,
		'description' => 1
    ],
    /* Return the documents in descending order of views */
    'sort' => [
        'model' => -1],
		['limit' => 100000
    ],
];

$query = new MongoDB\Driver\Query($filter, $options);

$readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
$cursor = $manager->executeQuery('test.product', $query, $readPreference);

foreach($cursor as $document) {
    //var_dump($document);
	echo $document->_id .'-- '.$document->model . ' ' . $document->description.'<br />';
}

?>
