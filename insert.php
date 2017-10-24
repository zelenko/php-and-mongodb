<?php

$time1 = microtime(true);

require_once('dbconn.php');
$bulk = new MongoDB\Driver\BulkWrite();

switch ($_GET['file']) {
    case "product_onhand.txt":
        $mapping = 'mapping_onhand';
        break;

    case "product_general.txt":
        $mapping = 'mapping_general';
        break;

    case "product_avgcost.txt":
        $mapping = 'product_avgcost';
        break;

    case "product_sales.txt":
        $mapping = 'product_sales';
        break;

}

$log = insert_into($_GET['file']);



function mapping_general($line){
    $map =
    [
        'pline' => $line[1],
        'bline' => $line[2],
        'category' => $line[3],
        'sales_all' => floatval($line[4]),
        'price' =>  floatval($line[5]),
        'product_name' => my_encrypt($line[6], '############'),
        'keyword' => my_encrypt($line[7], '############'),
        'avgcost' =>  floatval($line[8])
    ];
return $map;
}

function mapping_onhand($line){
    $map = 
    [
        'onhand.BR01' => floatval($line[1]),
        'onhand.BR02' => floatval($line[2]),
        'onhand.BR03' => floatval($line[3]),
        'onhand.BR04' => floatval($line[4]),
        'onhand.BR05' => floatval($line[5]),
        'onhand.BR07' => floatval($line[6]),
        'onhand.BR08' => floatval($line[7]),
        'onhand.BR12' => floatval($line[8]),
        'onhand.BR13' => floatval($line[9]),
        'onhand.BR14' => floatval($line[10]),
        'onhand.BR16' => floatval($line[11]),
        'onhand.BR17' => floatval($line[12]),
        'onhand.BR19' => floatval($line[13]),
        'onhand.BR21' => floatval($line[14]),
        'onhand.BR22' => floatval($line[15]),
        'onhand.BRPT' => floatval($line[16]),
        'onhand.BR60' => floatval($line[17])
    ];
return $map;
}

function product_avgcost($line){
    $map = 
    [
        'avg_cost.BR01' => floatval($line[1]),
        'avg_cost.BR02' => floatval($line[2]),
        'avg_cost.BR03' => floatval($line[3]),
        'avg_cost.BR04' => floatval($line[4]),
        'avg_cost.BR05' => floatval($line[5]),
        'avg_cost.BR07' => floatval($line[6]),
        'avg_cost.BR08' => floatval($line[7]),
        'avg_cost.BR12' => floatval($line[8]),
        'avg_cost.BR13' => floatval($line[9]),
        'avg_cost.BR14' => floatval($line[10]),
        'avg_cost.BR16' => floatval($line[11]),
        'avg_cost.BR17' => floatval($line[12]),
        'avg_cost.BR19' => floatval($line[13]),
        'avg_cost.BR21' => floatval($line[14]),
        'avg_cost.BR22' => floatval($line[15]),
        'avg_cost.BRPT' => floatval($line[16]),
        'avg_cost.BR60' => floatval($line[17])
    ];
return $map;
}

function product_sales($line){
    $map = 
    [
        'sales.BR01' => floatval($line[1]),
        'sales.BR02' => floatval($line[2]),
        'sales.BR03' => floatval($line[3]),
        'sales.BR04' => floatval($line[4]),
        'sales.BR05' => floatval($line[5]),
        'sales.BR07' => floatval($line[6]),
        'sales.BR08' => floatval($line[7]),
        'sales.BR12' => floatval($line[8]),
        'sales.BR13' => floatval($line[9]),
        'sales.BR14' => floatval($line[10]),
        'sales.BR16' => floatval($line[11]),
        'sales.BR17' => floatval($line[12]),
        'sales.BR19' => floatval($line[13]),
        'sales.BR21' => floatval($line[14]),
        'sales.BR22' => floatval($line[15]),
        'sales.BRPT' => floatval($line[16]),
        'sales.BR60' => floatval($line[17])
    ];
return $map;
}



function insert_into($file = "key_user") {
	global $manager, $bulk, $mapping;
    
    $file = "../../../home/ftpscript/inbox/".$file;
	if (file_exists($file)) {

        // go through each line of the file
        $handle = @fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle, 4096)) !== false) {
                $line = explode("\t", $line);
                $bulk->update(['_id' => intval($line[0])], ['$set' => $mapping($line)], ['multi' => false, 'upsert' => true]);
            }

            fclose($handle);
        }

        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        $result = $manager->executeBulkWrite('onlinestore.products3', $bulk, $writeConcern);

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
		if (!unlink($file)) echo ("Error deleting $file <br>\n");

    // if file does not exist, say so
	} else { 
		echo "The file \"".$file."\" does not exist<br>\n";
    }
    return $output;
}


// creating document to insert into log collection
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


// show how much time it took to process
echo 'PHP script execution time: ' .round( ( (microtime(true) - $time1)),3 ). ' milliseconds';

?>
