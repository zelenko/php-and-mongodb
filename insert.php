<?php
$time1 = microtime(true);

require_once('dbconn.php');
$bulk = new MongoDB\Driver\BulkWrite();

// Depending on file, pick the right mapping
switch ($_GET['file']) {
    case "product_onhand.txt":
        $mapping = 'mapping_onhand';
        break;

    case "product_general.txt":
        $mapping = 'mapping_general';
        break;

    case "product_sales.txt":
        $mapping = 'mapping_sales';
        break;
}

// this must be called after the right mapping is selected
insert_into($_GET['file']);


// Mapping for ...
function mapping_general($line){
    $map =
    [
        'pline' => $line[1],
        'bline' => $line[2],
        'category' => $line[3],
        'sales_all' => floatval($line[4]),
        'price' =>  floatval($line[5]),
        'product_name' => my_encrypt($line[6], '#############'),
        'keyword' => my_encrypt($line[7], '#############'),
        'avgcost' =>  floatval($line[8])
    ];
return $map;
}

// Mapping for ...
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
        'onhand.BRPT' => floatval($line[16])
    ];
return $map;
}


// Inserting file into databae
function insert_into($file) {
	global $manager, $bulk, $mapping;
    
    $file = "../../../home/ftpscript/inbox/".$file;
	if (file_exists($file)) {

        // Go through each line of the file, one by one.
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

        // display results on one line
        printf("PHP matched %d, ", $result->getMatchedCount());
        printf("inserted %d, ", $result->getInsertedCount());
        printf("updated %d, ", $result->getModifiedCount());
        printf("upserted %d.\n", $result->getUpsertedCount());
        
        // delete the file
		if (!unlink($file)) echo ("Error deleting $file <br>\n");

    // if file does not exist, say so
	} else { 
		echo "The file \"".$file."\" does not exist<br>\n";
	}
}

// show how much time it took to process
echo 'PHP script execution time: ' .round( ( (microtime(true) - $time1)),3 ). ' milliseconds';
?>
