<?php
$time1 = microtime(true);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

require_once('dbconn.php');
$bulk = new MongoDB\Driver\BulkWrite();

$mapping = 'mass_upload';

// this must be called after the $mapping variable is declared
get_the_job_done();


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
        'onhand_value.BRPT' => (double)(number_format($doc->avg_cost->BRPT * $doc->onhand->BRPT,2,'.',''))
    ];
return $map;
}
/*
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
            "onhand_value.BRPT": parseFloat((doc.onhand.BRPT * doc.avg_cost.BRPT).toFixed(2))
    
    } } )
    })
*/

function get_the_job_done(){
    global $manager, $bulk, $mapping;
    $query = new MongoDB\Driver\Query(
		[
            //'pline' => ['$ne' => 'NONSTOCK'],
            'onhand' => ['$exists' => true],
            'avg_cost' => ['$exists' => true]
		], // query (empty: select all)
		[
            'projection' => ['avg_cost' => 1, 'onhand' => 1]
            //'limit' => 10
            //'sort' => [ 'onhand.BR01' => -1], 'skip' => 0, 'limit' => 1
		] // options
	);

	// Execute query and obtain cursor:
	$cursor = $manager->executeQuery('onlinestore.products3', $query );
	
	// Display info in a table
	//echo '<table class="fixed_headers seven_columns">';
	//echo "<thead><tr><th>Product ID & Buyline</th><th>Product Description</th><th>$ / Qty: </th><th>onhand</th><th>avg cost</th></tr></thead>\n";
	foreach ($cursor as $doc) {

		//echo '<tr><td><a href="product/'.$doc->_id .'/">'.$doc->_id . '</a> ('.$doc->pline. ')</td>'.
		//'<td>--</td><td>$' . ''.
        //"</td><td>".$doc->onhand->BR01."</td><td>". $doc->avg_cost->BR01 . "</td></tr>\n";
        //echo $doc->_id . "<br />\n";
        
        $bulk->update(['_id' => intval($doc->_id)], ['$set' => $mapping($doc)], ['multi' => false, 'upsert' => true]);
	}
    //echo "</table>";

    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    $result = $manager->executeBulkWrite('onlinestore.products3', $bulk, $writeConcern);

    // display results on one line
    printf("PHP matched %d, ", $result->getMatchedCount());
    printf("inserted %d, ", $result->getInsertedCount());
    printf("updated %d, ", $result->getModifiedCount());
    printf("upserted %d.\n", $result->getUpsertedCount());
}


// show how much time it took to process
echo 'PHP script execution time: ' .round( ( (microtime(true) - $time1)),3 ). ' milliseconds';
?>