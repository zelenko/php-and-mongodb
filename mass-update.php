<?php
ob_implicit_flush(true);
ob_end_flush();

echo ('PHP: ');

$time1 = '';
$time2 = microtime(true);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

require_once('dbconn.php');


$mapping = 'mass_upload';

// this must be called after the $mapping variable is declared
//$output = get_the_job_done();
//insert_log($output);

$output = populate_array();
insert_log($output);

// Mapping for ...
function mass_upload($doc){
    $map =
    [
        'onhand_value.BR01' => (double)(number_format($doc->avg_cost->BR01 * $doc->onhand->BR01,2,'.','')),
        'onhand_value.BR02' => (double)(number_format($doc->avg_cost->BR02 * $doc->onhand->BR02,2,'.','')),
        'onhand_value.BR03' => (double)(number_format($doc->avg_cost->BR03 * $doc->onhand->BR03,2,'.','')),
        'onhand_value.BR04' => (double)(number_format($doc->avg_cost->BR04 * $doc->onhand->BR04,2,'.','')),
        'onhand_value.BR05' => (double)(number_format($doc->avg_cost->BR05 * $doc->onhand->BR05,2,'.','')),
        'onhand_value.BR07' => (double)(number_format($doc->avg_cost->BR07 * $doc->onhand->BR07,2,'.','')),
        'onhand_value.BR08' => (double)(number_format($doc->avg_cost->BR08 * $doc->onhand->BR08,2,'.','')),
        'onhand_value.BR12' => (double)(number_format($doc->avg_cost->BR12 * $doc->onhand->BR12,2,'.','')),
        'onhand_value.BR13' => (double)(number_format($doc->avg_cost->BR13 * $doc->onhand->BR13,2,'.','')),
        'onhand_value.BR14' => (double)(number_format($doc->avg_cost->BR14 * $doc->onhand->BR14,2,'.','')),
        'onhand_value.BR16' => (double)(number_format($doc->avg_cost->BR16 * $doc->onhand->BR16,2,'.','')),
        'onhand_value.BR17' => (double)(number_format($doc->avg_cost->BR17 * $doc->onhand->BR17,2,'.','')),
        'onhand_value.BR19' => (double)(number_format($doc->avg_cost->BR19 * $doc->onhand->BR19,2,'.','')),
        'onhand_value.BR21' => (double)(number_format($doc->avg_cost->BR21 * $doc->onhand->BR21,2,'.','')),
        'onhand_value.BR22' => (double)(number_format($doc->avg_cost->BR22 * $doc->onhand->BR22,2,'.','')),
        'onhand_value.BRPT' => (double)(number_format($doc->avg_cost->BRPT * $doc->onhand->BRPT,2,'.','')),
        'onhand_value.BR60' => (double)(number_format($doc->avg_cost->BR60 * $doc->onhand->BR60,2,'.',''))
    ];
return $map;
}

/*
To run this query in MongoDB Shell, use this command:

db.products3.find({pline: {$ne:"NONSTOCK-"}, pline: {$exists:1}, onhand: {$exists:1}, avg_cost: {$exists:1} } ).forEach(function(doc) {
    db.products3.update({ _id: doc._id }, { $set: {
            "onhand_value.BR01": parseFloat((doc.onhand.BR01 * doc.avg_cost.BR01).toFixed(2)),
            "onhand_value.BR02": parseFloat((doc.onhand.BR02 * doc.avg_cost.BR02).toFixed(2)),
            "onhand_value.BR03": parseFloat((doc.onhand.BR03 * doc.avg_cost.BR03).toFixed(2)),
            "onhand_value.BR04": parseFloat((doc.onhand.BR04 * doc.avg_cost.BR04).toFixed(2)),
            "onhand_value.BR05": parseFloat((doc.onhand.BR05 * doc.avg_cost.BR05).toFixed(2)),
            "onhand_value.BR07": parseFloat((doc.onhand.BR07 * doc.avg_cost.BR07).toFixed(2)),
            "onhand_value.BR08": parseFloat((doc.onhand.BR08 * doc.avg_cost.BR08).toFixed(2)),
            "onhand_value.BR12": parseFloat((doc.onhand.BR12 * doc.avg_cost.BR12).toFixed(2)),
            "onhand_value.BR13": parseFloat((doc.onhand.BR13 * doc.avg_cost.BR13).toFixed(2)),
            "onhand_value.BR14": parseFloat((doc.onhand.BR14 * doc.avg_cost.BR14).toFixed(2)),
            "onhand_value.BR16": parseFloat((doc.onhand.BR16 * doc.avg_cost.BR16).toFixed(2)),
            "onhand_value.BR17": parseFloat((doc.onhand.BR17 * doc.avg_cost.BR17).toFixed(2)),
            "onhand_value.BR19": parseFloat((doc.onhand.BR19 * doc.avg_cost.BR19).toFixed(2)),
            "onhand_value.BR21": parseFloat((doc.onhand.BR21 * doc.avg_cost.BR21).toFixed(2)),
            "onhand_value.BR22": parseFloat((doc.onhand.BR22 * doc.avg_cost.BR22).toFixed(2)),
            "onhand_value.BRPT": parseFloat((doc.onhand.BRPT * doc.avg_cost.BRPT).toFixed(2)),
            "onhand_value.BR60": parseFloat((doc.onhand.BR60 * doc.avg_cost.BR60).toFixed(2))
    } } )
    })
*/

function get_the_job_done(){
    global $manager, $mapping, $time1;
    $time1 = microtime(true);
    $bulk = new MongoDB\Driver\BulkWrite();
    $query = new MongoDB\Driver\Query(
        [   // query (empty: select all)
            //'sales_all' => ['$gt' => 100], // ADDED FOR TESTINTG
            'onhand' => ['$exists' => true],
            'avg_cost' => ['$exists' => true]
		], 
		[   // options
            'projection' => ['avg_cost' => 1, 'onhand' => 1]
            //'limit' => 10
            //'sort' => [ 'onhand.BR01' => -1], 'skip' => 0, 'limit' => 1
		] 
	);

	// Execute query and obtain cursor:
	$cursor = $manager->executeQuery('onlinestore.products3', $query );
	$j = 0; $k = 0;
	foreach ($cursor as $doc) {
        $bulk->update(['_id' => intval($doc->_id)], ['$set' => $mapping($doc)], ['multi' => false, 'upsert' => true]);
        $j++;
        if($j>10000){
            $k++;
            echo ','.$k;
            $j=0;
        }
	}

    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    $result = $manager->executeBulkWrite('onlinestore.products3', $bulk, $writeConcern);

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

// insert_log updates log collection
function insert_log($log){
    global $manager, $time1;
    $bulk = new MongoDB\Driver\BulkWrite();

    // creating document to insert into log collection
    $_GET['file'] = 'Mass Load';
    $doc = [
        'date' => new MongoDB\BSON\UTCDateTime,
        'execution_time' => round( ( (microtime(true) - $time1)),3 ),
        'file_name' => $_GET['file'],
        'matched' => $log->matched,
        'inserted' => $log->inserted,
        'updated' => $log->modified,
        'upserted' => $log->upserted,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'browser' => $_SERVER['HTTP_USER_AGENT'],
        'page' => $_SERVER['REQUEST_URI']
    ];

    // inserting document into log collection
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->insert($doc);
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    $result = $manager->executeBulkWrite('onlinestore.insert_log', $bulk, $writeConcern);
}



// Mapping for populate_array()
function elements($line){
    $map = [
        "branch" => ['$each' => $line]
    ];
    return $map;
}
/* To run this query in MongoDB Shell, use this command:
   It works with testing mechanisms.
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

// This function get data from each product as object and saves as an array.
function populate_array(){
    global $manager, $mapping, $time1;
    $time1 = microtime(true);
    $bulk = new MongoDB\Driver\BulkWrite();
    $query = new MongoDB\Driver\Query(
        [   
            //'_id' => 1,
            'sales' => ['$exists' => 1]
		], 
		[   // options
            'projection' => ['sales' => 1]
            //'limit' => 10
            //'sort' => [ 'onhand.BR01' => -1], 'skip' => 0, 'limit' => 1
		] 
	);

	// Execute query and obtain cursor:
	$cursor = $manager->executeQuery('onlinestore.products3', $query );
    $j = 0; $k = 0;
    echo ' Array: ';
	foreach ($cursor as $doc) {
        $arr = toArray($doc->sales);
        //$arr = $arr['sales'];
        //print_r($arr);
        $bulk->update(['_id' => intval($doc->_id)], ['$addToSet' => elements($arr)], ['multi' => false, 'upsert' => false]);
        //echo 'out: ' . $doc->_id ."<br>\n";
        $j++;
        if($j>10000){
            $k++;
            echo ','.$k;
            $j=0;
        }
    }

    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    $result = $manager->executeBulkWrite('onlinestore.products3', $bulk, $writeConcern);

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

// toArray is special helper function to convert object to an array.
function toArray($obj){
    if (is_object($obj)) $obj = (array)$obj;
    if (is_array($obj)) {
        $new = array();
        $i = 0;
        foreach ($obj as $key => $val) {
            $subArray['id'] = $key;
            $subArray['sales'] = toArray($val);
            $new[$i] = $subArray;
            $i++;
        }
    } else {
        $new = $obj;
    }
    return $new;
}


// show how much time it took to process
echo 'PHP time: ' .round( ( (microtime(true) - $time2)),3 ). ' seconds';
?>