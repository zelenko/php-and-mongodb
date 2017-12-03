<?php
// https://10.10.10.13/stock/insert-test.php?file=test_array.txt
$time1 = microtime(true);

require_once('dbconn.php');
$bulk = new MongoDB\Driver\BulkWrite();

switch ($_GET['file']) {
    
    case "product_sales.txt":
        $mapping = 'product_sales';
        break;

    case "test_array.txt":
        $mapping = 'test_array';
        break;

}

$log = insert_into($_GET['file']);

function test_array($line){
    $map =
    [
        'name' => $line[1],
        //'data' => [array({"02" => $line[2]}, {"03" => $line[3]}, {"04" => $line[4]}, {"05" => $line[5]}, {"06" => $line[6]}, {"07" => $line[7]})]
        "data.0.test1" => $line[2]
    ];
return $map;
}

function test_set($line){
    $map =
    [
        "data" => ['$each' => [
            ["id" => "BR01", "sales" => floatval($line[1])],
            ["id" => "BR02", "sales" => floatval($line[2])],
            ["id" => "BR03", "sales" => floatval($line[3])],
            ["id" => "BR04", "sales" => floatval($line[4])],
            ["id" => "BR05", "sales" => floatval($line[5])],
            ["id" => "BR06", "sales" => floatval($line[6])],
            ["id" => "BR07", "sales" => floatval($line[7])],
            ["id" => "BR08", "sales" => floatval($line[8])],
            ["id" => "BR09", "sales" => floatval($line[9])],
            ["id" => "BR10", "sales" => floatval($line[10])],
            ["id" => "BR11", "sales" => floatval($line[11])],
            ["id" => "BR12", "sales" => floatval($line[12])],
            ["id" => "BRPT", "sales" => floatval($line[13])]
        ]]
        // { $addToSet: { data: { $each: [ "camera", "electronics", "accessories" ] } } }
    ];
return $map;
}

function set2($line){
    $map =
    [
        "data.0.id"  => "BR01",     "data.0.gp"  => floatval( $line[2] ),    "data.0.sales"  => floatval( $line[1] ),
        "data.1.id"  => "BR02",     "data.1.gp"  => floatval( $line[2] ),    "data.1.sales"  => floatval( $line[2] ),
        "data.2.id"  => "BR03",     "data.2.gp"  => floatval( $line[2] ),    "data.2.sales"  => floatval( $line[3] ),
        "data.3.id"  => "BR04",     "data.3.gp"  => floatval( $line[2] ),    "data.3.sales"  => floatval( $line[4] ),
        "data.4.id"  => "BR05",     "data.4.gp"  => floatval( $line[2] ),    "data.4.sales"  => floatval( $line[5] ),
        "data.5.id"  => "BR06",     "data.5.gp"  => floatval( $line[2] ),    "data.5.sales"  => floatval( $line[6] ),
        "data.6.id"  => "BR07",     "data.6.gp"  => floatval( $line[2] ),    "data.6.sales"  => floatval( $line[7] ),
        "data.7.id"  => "BR08",     "data.7.gp"  => floatval( $line[2] ),    "data.7.sales"  => floatval( $line[8] )
    ];
return $map;
}
/*
db.spaces.update(
    { "attributes.name": "x" }, // <-- the array field must appear as part of the query document.
    { "$set": { "attributes.$.weight": 2 } },
    { "multi": true }
 )
*/

function insert_into($file = "key_user") {
    global $manager, $bulk, $mapping;
    
    $file = "./".$file;
	if (file_exists($file)) {

        // go through each line of the file
        $handle = @fopen($file, "r");
        if ($handle) {
            // go ahead
            while (($line = fgets($handle, 4096)) !== false) {
                $line = explode("\t", $line);
                //$bulk->update(['_id' => intval($line[0])], ['$addToSet' => test_set($line)], ['multi' => false, 'upsert' => true]); // works
                //$bulk->update(['_id' => intval($line[0])], ['$set' => $mapping($line)], ['multi' => false, 'upsert' => true]); // original
                //$bulk->update(['_id' => intval($line[0])], ['$set' => (array)set2($line)], ['multi' => false, 'upsert' => true]); // works with arrays
                //$pull: { itemNames: 3 }
                $bulk->update(
                    ['_id' => intval($line[0])], 
                    ['$set' => set2($line)], 
                    ['multi' => false, 'upsert' => true]
                ); // works with arrays

                //$bulk->update(['_id' => intval($line[0])], ['$unset' => ["data.33" => 1]], ['multi' => false, 'upsert' => true]); // removes array element
            }

            fclose($handle);
        }

        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        $result = $manager->executeBulkWrite('test.people', $bulk, $writeConcern);

        // display results
        printf("PHP matched %d, ", $result->getMatchedCount());
        printf("inserted %d, ", $result->getInsertedCount());
        printf("updated %d, ", $result->getModifiedCount());
        printf("upserted %d.\n", $result->getUpsertedCount());

        // this is "output" from this function
        $output = (object) [
            'matched' => $result->getMatchedCount(),
            'inserted' => $result->getInsertedCount(),
            'modified' => $result->getModifiedCount(),
            'upserted' => $result->getUpsertedCount()
        ];

        //printf("Deleted  %d document(s)\n", $result->getDeletedCount());
        //foreach ($result->getUpsertedIds() as $index => $id) {
        //    printf('upsertedId[%d]: ', $index);
        //    var_dump($id);
        //}
        
        // delete the file
		//if (!unlink($file)) echo ("Error deleting $file <br>\n");

    // if file does not exist, say so
	} else { 
		echo "The file \"".$file."\" does not exist<br>\n";
    }
    return $output;
}


// creating document to insert into log collection
$doc = [
    'date' => new MongoDB\BSON\UTCDateTime,
    'php_seconds' => round( ( (microtime(true) - $time1)),3 ),
    'file' => $_GET['file'],
    'matched' => $log->matched,
    'inserted' => $log->inserted,
    'updated' => $log->modified,
    'upserted' => $log->upserted,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'browser' => $_SERVER['HTTP_USER_AGENT'],
    'page' => $_SERVER['REQUEST_URI']
];


// show how much time it took to process
echo 'PHP script execution time: ' .round( ( (microtime(true) - $time1)),3 ). ' milliseconds';

?>
