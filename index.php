<?php
$time1 = microtime(true);

require_once('dbconn.php');

if(!isset($_GET['mode'])){
	$_GET['mode'] = 'onhand';
}

if(!isset($_GET['branch'])){
	$_GET['branch'] = 'BR01';
}
if(!isset($_GET['id'])){
	$_GET['id'] = '';
}
if(!isset($_GET['priceline'])){
	$_GET['priceline'] = '';
}
?>
<!DOCTYPE html>
<html>
<head><title><?=$_GET['mode']=='onhand'?' Dead Stock':''?>
		<?=$_GET['mode']=='short'?' Short Stock':''?>
		<?=$_GET['branch']!=''?' in: '.$_GET['branch']:''?>
		<?=$_GET['id']!=''?' Product: '.$_GET['id']:''?>
		<?=$_GET['priceline']!=''?' Buyline: '.$_GET['priceline']:''?></title>
	<base href="https://192.168.100.85/stock/" />
	<meta http-equiv="refresh" content="60">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	

	<link rel="stylesheet" type="text/css" href="style.css">
<style>
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dashed black;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 440px;
    background-color: #555;
    color: #fff;
    text-align: left;
    border-radius: 6px;
    padding: 5px;
    position: absolute;
    z-index: 1;
   /* bottom: 125%;
  /*  left: 50%; */
    
    top: -3px; /* vertical tip location */
    left: 105%;
    
    margin-left: 10px; /* how far right */
    opacity: 0;
    transition: opacity 1s;
}

    /*
    bottom: 100%;  /* At the top of the tooltip 
    left: 50%;
    margin-left: -5px;
    
    top: 50%;
    right: 100%; /* To the left of the tooltip 
    margin-top: -5px;
    */


.tooltip .tooltiptext::after {
    content: " ";
    position: absolute;
	top: 8px;  /* arrow position */
    left: -7px;
    margin-left: -5px;
    
    border-width: 5px;
    border-style: solid;
    border-color: transparent black transparent transparent;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>
</head>
<body>

<div id="pagewrap">

	<div id="bar"><div style="float: left;">
		<?=$_GET['mode']=='onhand'?' Dead Stock':''?>
		<?=$_GET['mode']=='short'?' Short Stock':''?>
		<?=$_GET['branch']!=''?' in: '.$_GET['branch']:''?>
		<?=$_GET['id']!=''?' Product: '.$_GET['id']:''?>
		<?=$_GET['priceline']!=''?' Buyline: '.$_GET['priceline']:''?>
		</div><a href="/stock/branch/BR01/">Dead Stock</a>
	| <a href="/stock/short/BR01/">Stock Shortages</a>
	</div>

	<div id="content">

<?php

// Dead Stock onhand by branch
if($_GET['mode']=='onhand'){
	
	// Display branch links
	echo '<div> Branch '."\n";
	echo '<a href="/stock/branch/BR01/">1</a> '."\n";
	echo '<a href="/stock/branch/BR02/">2</a> '."\n";
	echo '<a href="/stock/branch/BR03/">3</a> '."\n";
	echo '<a href="/stock/branch/BR04/">4</a> '."\n";
	echo '<a href="/stock/branch/BR05/">5</a> '."\n";
	echo '<a href="/stock/branch/BR07/">7</a> '."\n";
	echo '<a href="/stock/branch/BR08/">8</a> '."\n";
	echo '<a href="/stock/branch/BR12/">12</a> '."\n";
	echo '<a href="/stock/branch/BR13/">13</a> '."\n";
	echo '<a href="/stock/branch/BR14/">14</a> '."\n";
	echo '<a href="/stock/branch/BR16/">16</a> '."\n";
	echo '<a href="/stock/branch/BR17/">17</a> '."\n";
	echo '<a href="/stock/branch/BR19/">19</a> '."\n";
	echo '<a href="/stock/branch/BR21/">21</a> '."\n";
	echo '<a href="/stock/branch/BR22/">22</a> '."\n";
	echo '<a href="/stock/branch/BRPT/">PT</a> '."\n";
	echo '</div>';

	$query = new MongoDB\Driver\Query(
		[
			'onhand.'.$_GET['branch'] => ['$gt' => 0],
			'sales.'.$_GET['branch'] => ['$lt' => 1],
			'sales_all' => ['$gt' => 0],
			'pline' => ['$exists' => true]
		], // query (empty: select all)
		[
			'sort' => [ 'onhand.'.$_GET['branch'] => -1], 'skip' => 0, 'limit' => 500
			//'onhand.'.$_GET['branch'] => ['$multiply'=> ['$onhand.'.$_GET['branch'] , '$avgcost']]
		] // options
	);

	// Execute query and obtain cursor:
	$cursor = $manager->executeQuery('onlinestore.products3', $query );
	$i=1;
	
	// Display info in a table
	echo '<table class="fixed_headers seven_columns">';
	echo "<thead><tr><th>Product ID & Buyline</th><th>Product Description</th><th>Onhand: ".$_GET['branch']."</th><th>12 Mo Sales</th><th>Category</th></tr></thead>\n";
	foreach ($cursor as $doc) {
		$new_value = my_decrypt($doc->product_name, '################');
		$onhand = (array)$doc->onhand;
		$sales = (array)$doc->sales;
		$top_branch = array_search(max($sales),$sales);

		// Display rows of data
		echo '<tr><td><a href="product/'.$doc->_id .'/">'.$doc->_id . '</a> (<a href="branch/'.$_GET['branch'].'/'.$doc->pline.'/">'.$doc->pline. '</a>)</td>'.
		'<td>'.$new_value.'</a></td><td>' . $doc->onhand->$_GET['branch'] .
		"</td><td>". $top_branch . ' ($' . max($sales) . ")</td><td>". $doc->category . "</td></tr>\n";
		$i++;
	}
	echo "</table>";
}

// Product info page
if($_GET['mode']=='product'){
	if(!isset($_GET['id'])){
		$_GET['id'] = '1';
	}
	
	$query = new MongoDB\Driver\Query(
		[
			'_id' => (int)$_GET['id']
		]
	);

	$cursor = $manager->executeQuery('onlinestore.products3', $query );
	
	// Display branch links
	echo '<div> Branch '."\n";
	echo '<a href="/stock/branch/BR01/">1</a> '."\n";
	echo '<a href="/stock/branch/BR02/">2</a> '."\n";
	echo '<a href="/stock/branch/BR03/">3</a> '."\n";
	echo '<a href="/stock/branch/BR04/">4</a> '."\n";
	echo '<a href="/stock/branch/BR05/">5</a> '."\n";
	echo '<a href="/stock/branch/BR07/">7</a> '."\n";
	echo '<a href="/stock/branch/BR08/">8</a> '."\n";
	echo '<a href="/stock/branch/BR12/">12</a> '."\n";
	echo '<a href="/stock/branch/BR13/">13</a> '."\n";
	echo '<a href="/stock/branch/BR14/">14</a> '."\n";
	echo '<a href="/stock/branch/BR16/">16</a> '."\n";
	echo '<a href="/stock/branch/BR17/">17</a> '."\n";
	echo '<a href="/stock/branch/BR19/">19</a> '."\n";
	echo '<a href="/stock/branch/BR21/">21</a> '."\n";
	echo '<a href="/stock/branch/BR22/">22</a> '."\n";
	echo '<a href="/stock/branch/BRPT/">PT</a> '."\n";
	echo '</div>';
	
	// Display product info
	echo '<table class="fixed_headers seven_columns">';
	echo "<thead><tr><th>Product ID & Buyline</th><th>Product Description</th><th></th><th>12 Mo Sales</th><th>Category</th></tr></thead>\n";
	foreach ($cursor as $doc) {
		$new_value = my_decrypt($doc->product_name, '################');
		$onhand = (array)$doc->onhand;
		$sales = (array)$doc->sales;
		$top_branch = array_search(max($sales),$sales);

		echo '<tr><td>'.$doc->_id . ' (<a href="branch/'.$_GET['branch'].'/'.$doc->pline.'/">'.$doc->pline. '</a>)</td>'.
		'<td>'.$new_value.'</a></td><td>' . '' .
		"</td><td>". $top_branch . ' ($' . max($sales) . ")</td><td>". $doc->category . "</td></tr>\n";
	}

	// Display info qty & sale by branch
	echo "<thead><tr><th>Branch ID</th><th>Onhand QTY</th><th>12 Mo Sales</th><th>-</th><th>-</th></tr></thead>\n";
	foreach ($onhand as $key => $value){
		//echo $key.': '.$value."<br>\n";
		echo '<tr><td><a href="branch/'.$key.'/">'.$key . '</a></td>'.
		'<td>'.$value.'</a></td><td>' . $sales[$key].
		"</td><td>". '' . '' . "</td><td>". '' . "</td></tr>\n";
	}

	echo "</table>";
}


// Products in Branch by buyline
if($_GET['mode']=='buyline'){
	$_GET['priceline'] = (string)$_GET['priceline'];
	// Display branch links
	echo '<div> Branch '."\n";
	echo '<a href="/stock/branch/BR01/'.$_GET['priceline'].'/">1</a> '."\n";
	echo '<a href="/stock/branch/BR02/'.$_GET['priceline'].'/">2</a> '."\n";
	echo '<a href="/stock/branch/BR03/'.$_GET['priceline'].'/">3</a> '."\n";
	echo '<a href="/stock/branch/BR04/'.$_GET['priceline'].'/">4</a> '."\n";
	echo '<a href="/stock/branch/BR05/'.$_GET['priceline'].'/">5</a> '."\n";
	echo '<a href="/stock/branch/BR07/'.$_GET['priceline'].'/">7</a> '."\n";
	echo '<a href="/stock/branch/BR08/'.$_GET['priceline'].'/">8</a> '."\n";
	echo '<a href="/stock/branch/BR12/'.$_GET['priceline'].'/">12</a> '."\n";
	echo '<a href="/stock/branch/BR13/'.$_GET['priceline'].'/">13</a> '."\n";
	echo '<a href="/stock/branch/BR14/'.$_GET['priceline'].'/">14</a> '."\n";
	echo '<a href="/stock/branch/BR16/'.$_GET['priceline'].'/">16</a> '."\n";
	echo '<a href="/stock/branch/BR17/'.$_GET['priceline'].'/">17</a> '."\n";
	echo '<a href="/stock/branch/BR19/'.$_GET['priceline'].'/">19</a> '."\n";
	echo '<a href="/stock/branch/BR21/'.$_GET['priceline'].'/">21</a> '."\n";
	echo '<a href="/stock/branch/BR22/'.$_GET['priceline'].'/">22</a> '."\n";
	echo '<a href="/stock/branch/BRPT/'.$_GET['priceline'].'/">PT</a> '."\n";
	echo ' | <a href="branch/'.$_GET['branch'].'/">[All buylines]</a>'."\n";
	echo '</div>';


	$query = new MongoDB\Driver\Query(
		[
			'onhand.'.$_GET['branch'] => ['$gt' => 0],
			'sales.'.$_GET['branch'] => ['$lt' => 1],
			'pline' => $_GET['priceline']
		], // query (empty: select all)
		[
			'sort' => [ 'onhand.'.$_GET['branch'] => -1], 'skip' => 0, 'limit' => 5000
		] // options
	);

	// Execute query and obtain cursor:
	$cursor = $manager->executeQuery('onlinestore.products3', $query );
	$i=1;
	
	// Display info in a table
	echo '<table class="fixed_headers seven_columns">';
	echo "<thead><tr><th>Product ID & Buyline</th><th>Product Description</th><th>Onhand: ".$_GET['branch']."</th><th>12 Mo Sales</th><th>Category</th></tr></thead>\n";
	foreach ($cursor as $doc) {
		$new_value = my_decrypt($doc->product_name, '################');
		$onhand = (array)$doc->onhand;
		$sales = (array)$doc->sales;
		$top_branch = array_search(max($sales),$sales);

		// Display rows of data
		echo '<tr><td><a href="product/'.$doc->_id . '/">'.$doc->_id . '</a> ('.$doc->pline. ')</td>'.
		'<td>'.$new_value.'</a></td><td>' . $doc->onhand->$_GET['branch'] .
		"</td><td>". $top_branch . ' ($' . max($sales) . ")</td><td>". $doc->category . "</td></tr>\n";
		$i++;
	}
	echo "</table>";
}




// Dead Stock onhand by branch
if($_GET['mode']=='short'){
	
	// Display branch links
	echo '<div> Branch '."\n";
	echo '<a href="/stock/short/BR01/">1</a> '."\n";
	echo '<a href="/stock/short/BR02/">2</a> '."\n";
	echo '<a href="/stock/short/BR03/">3</a> '."\n";
	echo '<a href="/stock/short/BR04/">4</a> '."\n";
	echo '<a href="/stock/short/BR05/">5</a> '."\n";
	echo '<a href="/stock/short/BR07/">7</a> '."\n";
	echo '<a href="/stock/short/BR08/">8</a> '."\n";
	echo '<a href="/stock/short/BR12/">12</a> '."\n";
	echo '<a href="/stock/short/BR13/">13</a> '."\n";
	echo '<a href="/stock/short/BR14/">14</a> '."\n";
	echo '<a href="/stock/short/BR16/">16</a> '."\n";
	echo '<a href="/stock/short/BR17/">17</a> '."\n";
	echo '<a href="/stock/short/BR19/">19</a> '."\n";
	echo '<a href="/stock/short/BR21/">21</a> '."\n";
	echo '<a href="/stock/short/BR22/">22</a> '."\n";
	echo '<a href="/stock/short/BRPT/">PT</a> '."\n";
	echo '</div>';

	$command = new MongoDB\Driver\Command([
    "aggregate" => "products3",
    "pipeline" => [
        [ '$project' => [
            "isFirstGreater" => [ '$cmp' => [ ['$multiply'=> ['$demand.'.$_GET['branch'] , 2]], '$onhand.'.$_GET['branch'] ] ],
			'demand.'.$_GET['branch'] => 1,
			"product_name" => 1,
			"pline" => 1,
			"ranks.".$_GET['branch'] => 1,
            //$_GET['branch'].".daily_demand2" => ['$multiply'=> ['$'.$_GET['branch'].'.daily_demand' , 2]],
			'difference' => ['$subtract'=> ['$onhand.'.$_GET['branch'], '$demand.'.$_GET['branch'] ]],
			'days' => ['$divide'=> ['$onhand.'.$_GET['branch'], '$demand.'.$_GET['branch'] ]],
            'onhand.'.$_GET['branch'] => 1
        ]],
        [ '$match' => [
			'isFirstGreater' => 1,
			'demand.'.$_GET['branch'] => ['$gt' => 0.01], 
			// { "BR01.ranks": { $in: ["A", "B"] }}
			'onhand.'.$_GET['branch'] => ['$gte' => 0],
			'product_name' => ['$exists' => 1],
			'pline' => ['$exists' => 1],
			'pline' => ['$ne' => "NONSTOCK"]
        ]],
        ['$sort' => [
			'onhand.'.$_GET['branch'] => 1, 
			'demand.'.$_GET['branch'] => -1
		]],
        ['$limit' => 500]
    ],
   'cursor' => new stdClass
]);

	try {
		$cursor = $manager->executeCommand("onlinestore", $command);
	} catch(MongoDB\Driver\Exception $e) {
		echo 'DB error: '.$e->getMessage(), "\n";
		exit;
	}

	// Execute query and obtain cursor:
	//$cursor = $manager->executeQuery('onlinestore.products3', $query );
	$i=1;
	
	// Display info in a table
	echo '<table class="fixed_headers seven_columns">';
	echo "<thead><tr><th>Product, Priceline</th><th>Product Description</th><th>Rank</th><th>demand / onhand</th><th>Difference / Days</th></tr></thead>\n";
	foreach ($cursor as $doc) {
		$new_value = my_decrypt($doc->product_name, '################');
		// Display rows of data
		echo '<tr><td><a href="product/'.$doc->_id .'/">'.$doc->_id . '</a> ('.$doc->pline. ')</td>'.
		'<td>'.$new_value.'</a></td><td>' . $doc->ranks->$_GET['branch'] .
		"</td><td>". $doc->demand->$_GET['branch'] . ' / '.$doc->onhand->$_GET['branch']. "</td><td>". $doc->difference . ' / ' .round($doc->days, 1) . "</td></tr>\n";
		$i++;
	}
	echo "</table>";
}



?>


</div>

<div id="footer">
		<div style="float: center;"><p class="rek"><font color="#999999">https://192.168.100.85/stock/<br>
&copy; 2017 Items in Stock<br>

<?php echo 'Script execution time: ' .round( ( (microtime(true) - $time1)),3 ). ' milliseconds';
 ?>
</font></p>
		</div>
		
	</div>

</div>
</body></html>