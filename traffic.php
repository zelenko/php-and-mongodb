<?php
$time1 = microtime(true);

require_once('dbconn.php');
$bulk = new MongoDB\Driver\BulkWrite();
$mapping = 'mass_upload';

if(!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] ='';


// $orig_date2 = new MongoDB\BSON\UTCDateTime(new DateTime('2017-10-01 14:30:33'));
//$todays_date = new MongoDB\BSON\UTCDateTime;
//$orig_date2 = $orig_date2->toDateTime();
//var_dump($orig_date2->format(DATE_ISO8601));  // DATE_ATOM DATE_RSS DATE_ISO8601
// {"date": {"$gte": {"$date": "2017-10-01T00:00:00.000Z"}}}

$doc = (object) [
    'date' => new MongoDB\BSON\UTCDateTime,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'hostname' => 'hostname', //$_SERVER['REMOTE_HOST'],
    'browser_version' => $_SERVER['HTTP_USER_AGENT'],
    'other' => 'browser',//get_browser(null, true),
    'os' => 'Foo value',
    'loadtime' => $execution_time,
    'referrer' => $_SERVER['HTTP_REFERER'],
    'screen_size' => "Foo value 2",
    'page' => $_SERVER['REQUEST_URI'],
    'session' => $_COOKIE['PHPSESSID']
]; 

if(!isset($_GET['traffic'])) $_GET['traffic'] = 'insert';


switch ($_GET['traffic']) {
    case "insert":
        //$mapping = 'mapping_onhand';
        get_the_job_done();
        //system_variable2();
        break;

    case "delete":
        delete();
        break;

    case "none":
        system_variable2();
        break;
}

// this must be called after the $mapping variable is declared
//get_the_job_done();


// Mapping for ...
function mass_upload($values){
    $map =
    [
        'date' => $values->date,
        'page' => $values->page,
        'ip' => $values->ip,
        'browser_version' => $values->browser_version,
        'loadtime' => $values->loadtime,
        'session' => $values->session
/*
        'other' => $values->other,
        'os' => $values->os,
        
        'referrer' => $values->referrer,
        'screen_size' => $values->screen_size,
        'hostname' => $values->hostname  */
    ];

return (object) $map;
}


function get_the_job_done(){
    global $manager, $bulk, $mapping, $doc;

    // insert the data into collection
    $bulk->insert($mapping($doc));


    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    $result = $manager->executeBulkWrite('onlinestore.traffic', $bulk, $writeConcern);

    // display results on one line
    /*
    printf("PHP matched %d, ", $result->getMatchedCount());
    printf("inserted %d, ", $result->getInsertedCount());
    printf("updated %d, ", $result->getModifiedCount());
    printf("upserted %d.\n", $result->getUpsertedCount()); */
}

function delete(){
    global $manager;
    
    $delRec = new MongoDB\Driver\BulkWrite;
    $delRec->delete(['_id' => ['$exists' => true]], ['limit' => 1]);
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
    $result       = $manager->executeBulkWrite('onlinestore.traffic', $delRec, $writeConcern);
    if($result->getDeletedCount()){
        printf("deleted");
        printf("deleted %d.\n", $result->getDeletedCount());
    }else{
        printf("nothing to delete<br>\n");
    }


}


function system_variable2(){
    $_SERVER;
    echo '<pre>';
    print_r($GLOBALS);
    echo '</pre>';
}

?>