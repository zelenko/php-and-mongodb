
<?php
$sweet = array('a' => 'apple', 'b' => 'banana');
$fruits = array(
    'sweet' => $sweet,
    'a2' => 'apple2',
    'a3' => 'apple3',
    'a4' => 'apple4',
    'sour' => 'lemon'
);

function test_print($item, $key)
{
    echo "$key holds $item <br>\n";
}

array_walk_recursive($fruits, 'test_print');


echo '<pre>';
print_r($fruits);
echo '<pre>';

echo '<hr>';


echo '<pre>';
echo json_encode($fruits, JSON_PRETTY_PRINT);
echo '</pre>';


$myObj = new stdClass();
$myObj->name = "John";
$myObj->age = 30;
$myObj->city = "New York";

$myJSON = json_encode($myObj, JSON_PRETTY_PRINT);

echo '<pre>';
echo $myJSON;
echo '</pre>';
?>
