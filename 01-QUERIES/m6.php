<?php
require_once('dbconn.php');
$bulk = new MongoDB\Driver\BulkWrite();
$i = 1;

foreach(file('data.txt') as $line) {
	$line = explode("\t", $line);
	
	$bulk->update(['_id' => intval($line[0])], ['$set' => 
		[
			'keyword' => my_encrypt($line[1], '*************************')
		]
	], ['multi' => false, 'upsert' => true]);

	echo $i++ .' > ' .$line[0]. ' > ' . $line[1]."<br />\n";
	
	if ($i == 5) break; // End after # of lines
}

$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
$result = $manager->executeBulkWrite('onlinestore.products3', $bulk, $writeConcern);


printf("Inserted %d document(s)\n", $result->getInsertedCount());
printf("Matched  %d document(s)\n", $result->getMatchedCount());
printf("Updated  %d document(s)\n", $result->getModifiedCount());
printf("Upserted %d document(s)\n", $result->getUpsertedCount());
printf("Deleted  %d document(s)\n", $result->getDeletedCount());

foreach ($result->getUpsertedIds() as $index => $id) {
    printf('upsertedId[%d]: ', $index);
    var_dump($id);
}


function my_encrypt($data, $key) {
    $encryption_key = base64_decode($key);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}
 
function my_decrypt($data, $key) {
    $encryption_key = base64_decode($key);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}
?>
