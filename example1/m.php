<?php
require_once('dbconn.php');

// Query
$query = new MongoDB\Driver\Query(
	[
		//'_id' => ['$in' => [1]], 
		'product_name' => ['$exists' => 1], 
		'en' => [ '$ne' => true ] 
	], // query (empty: select all)
	[
		'sort' => [ '_id' => 1 ], 'limit' => 10000
	] // options
);

$bulk = new MongoDB\Driver\BulkWrite();

// Execute query and obtain cursor:
$cursor = $manager->executeQuery('onlinestore.products3', $query );

// Display on page
foreach ($cursor as $doc) {
	$new_value = my_encrypt($doc->product_name, '*************************');
	$bulk->update(['_id' => $doc->_id], ['$set' => ['en' => true, 'product_name' => $new_value]], ['multi' => false, 'upsert' => true]);
	echo $doc->_id .' -- ' . $doc->product_name . ' >> '.$new_value.'<br />';
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
