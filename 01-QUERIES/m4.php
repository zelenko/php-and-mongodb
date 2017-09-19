<!DOCTYPE html>
<html lang="en">
<head>
  <title>DB Stats</title>
  <meta charset="utf-8">
  <meta http-equiv="refresh" content="120">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="jumbotron text-center">
  <h1>DB Stats</h1>
  <p>DB stats: from MongoDB using PHP</p> 
  <p><?php
require_once('dbconn.php');
?>
</p>
</div>
  
<div class="container">
  <div class="row">
    <div class="col-sm-4">
      <h3>Statistics</h3>
        <p>
<?php

try {

    $listdatabases = new MongoDB\Driver\Command(["listDatabases" => 1]);
    $res = $manager->executeCommand("admin", $listdatabases);

    $databases = current($res->toArray());

    foreach ($databases->databases as $el) {
    
        echo $el->name . "\n";
    }

} catch (MongoDB\Driver\Exception\Exception $e) {

    $filename = basename(__FILE__);
    
    echo "The $filename script has experienced an error.\n"; 
    echo "It failed with the following exception:\n";
    
    echo "Exception:", $e->getMessage(), "\n";
    echo "In file:", $e->getFile(), "\n";
    echo "On line:", $e->getLine(), "\n";       
}

?>
  </p>
    </div>
    <div class="col-sm-4">
      <h3>Stats</h3>
      <p>
	  
	  <?php

try {

    $stats = new MongoDB\Driver\Command(["dbstats" => 1]);
    $res = $manager->executeCommand("onlinestore", $stats);
    
    $stats = current($res->toArray());

    //print_r($stats);
	foreach($stats as $key=>$value){
		print "<strong>$key:</strong> $value<br />\n";
	}

} catch (MongoDB\Driver\Exception\Exception $e) {

    $filename = basename(__FILE__);
    
    echo "The $filename script has experienced an error.\n"; 
    echo "It failed with the following exception:\n";
    
    echo "Exception:", $e->getMessage(), "\n";
    echo "In file:", $e->getFile(), "\n";
    echo "On line:", $e->getLine(), "\n";       
}
    
?>
	  
	  </p>
    </div>
    <div class="col-sm-4">
      <h3>Column 3</h3>        
      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
    </div>
  </div>
</div>

</body>
</html>
