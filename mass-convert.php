<?php

// db.people.updateMany({data2: {$exists: true}},   {$unset: {data2: ""} })
// https://10.10.10.13/stock/mass-convert.php

$time1 = microtime(true);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

require_once('dbconn.php');
$bulk = new MongoDB\Driver\BulkWrite();

// this must be called after the $mapping variable is declared
$log = get_the_job_done();


// Mapping for ...
function elements($line){
    $map = [
        "data2" => ['$each' => $line]
    ];
    return $map;
}

/* To run this query in MongoDB Shell, use this command:

// works with testing mechanisms
function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
db.people.find( { "data" : { $type : 3 } } ).forEach( function (x) {
  var out =[];
  for( var i in x.data ) {
    if (x.data.hasOwnProperty(i)){
        if (isNumber(i)){
            out[i] = x.data[i];
        }else{
          out.push(x.data[i]);
        }
    }
  }
  x.data = out
    db.people.save(x);
});

*/

function get_the_job_done(){
    global $manager, $bulk, $mapping;
    $query = new MongoDB\Driver\Query(
        [   
            'data' => ['$type' => 3]
		], 
		[   // options
            'projection' => ['data' => 1]
            //'limit' => 10
            //'sort' => [ 'onhand.BR01' => -1], 'skip' => 0, 'limit' => 1
		] 
	);

	// Execute query and obtain cursor:
	$cursor = $manager->executeQuery('test.people', $query );

	foreach ($cursor as $doc) {
        $arr = toArray($doc);
        //$arr = $arr['data'];
        $bulk->update(['_id' => intval($doc->_id)], ['$addToSet' => elements($arr['data'])], ['multi' => false, 'upsert' => false]);
        //echo 'out: ' . $doc->_id ."<br>\n";
    }

    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    $result = $manager->executeBulkWrite('test.people', $bulk, $writeConcern);

    // display results on one line
    printf("\nmatched %d, ", $result->getMatchedCount());
    printf("inserted %d, ", $result->getInsertedCount());
    printf("updated %d, ", $result->getModifiedCount());
    printf("upserted %d.\n", $result->getUpsertedCount());
    
    $output = (object) [
        'matched' => $result->getMatchedCount(),
        'inserted' => $result->getInsertedCount(),
        'modified' => $result->getModifiedCount(),
        'upserted' => $result->getUpsertedCount()
    ];

    return $output;
}

function toArray($obj){
    if (is_object($obj)) $obj = (array)$obj;
    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = toArray($val);
        }
    } else {
        $new = $obj;
    }
    return $new;
}

// show how much time it took to process
echo 'PHP time: ' .round( ( (microtime(true) - $time1)),3 ). ' seconds';
//ob_end_flush();
?>