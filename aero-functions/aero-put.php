<?php

$pk = 1; // Primary Key
$listOfKey = [];
for ($i = $pk; $i < $pk + 10; ++$i) {
    $listOfKey[] = $db->initKey($namespace, $table, $i); // $i is Primary key
    $data = ['pk' => $i, 'name' => 'rendy'.$i, 'address' => 'Jakarta', 'phone number' => '08170656622'.rand(1, 15)];
    $status = $db->put($listOfKey[count($listOfKey) - 1], $data);
    // var_dump($status);
}

// // store the key data with the record, rather than just its digest
// $option = [Aerospike::OPT_POLICY_KEY => Aerospike::POLICY_KEY_SEND];
// // $statusDelete = $db->remove($key);
// // $status = $db->put($key, $bins);
// // var_dump($status);
// $key = $db->initKey($namespace, $table, $pk);
$status = $db->getMany($listOfKey, $record);
foreach ($record as $rec) {
    var_dump($rec['bins']);
}

// $total = 0;
// $in_thirties = 0;

// if ($status == Aerospike::ERR_QUERY) {
//     echo "An error occured while querying[{$client->errorno()}] ".$client->error();
// } elseif ($status == Aerospike::ERR_QUERY_ABORTED) {
//     echo "Stopped the result stream after {$in_thirties} results\n";
// } else {
//     echo 'The average age of employees in their thirties is '.($total).($in_thirties)."\n";
// }

// var_dump($record, $status);
