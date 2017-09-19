<?php

require_once('dbconn.php');
//*************************

// Query
$query = new MongoDB\Driver\Query(
	[
		'_id' => ['$gte' => 465900], 
		//'_id' => ['$regex' => '/240/'], 
		'product_name' => ['$exists' => 1], 
		'en' => true
	], // query (empty: select all)
	[
		'sort' => [ '_id' => 1 ], 'skip' => 3000, 'limit' => 500
	] // options
);


// Execute query and obtain cursor:
$cursor = $manager->executeQuery('onlinestore.products3', $query );

// Display on page
foreach ($cursor as $doc) {
	$new_value = my_decrypt($doc->product_name, '*************************');
	echo $doc->_id .' -- ' .$new_value."<br />\n";
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
