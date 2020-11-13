
<?php
//  $db->addIndex($namespace, $table, 'address', 'address_index', Aerospike::INDEX_TYPE_DEFAULT, Aerospike::INDEX_STRING);

$data = [];
$count = 0;
 $stats = $db->query($namespace,
  $table,
  Aerospike::predicateEquals('address', 'Jakarta'),
  function ($record) use (&$data, &$count) {
      var_dump($record);
      ++$count;
      $data = $record;
  }
);

var_dump($data, $count);
