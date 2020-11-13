<?php

echo 'Contoh disini  '."\n";

//Connect DB
$config = ['hosts' => [
    ['addr' => '192.168.56.4',
    'port' => 3000, ],
    ]];
$database = new Aerospike($config, false);
$namespace = 'infosphere';
$table = 'Mahasiswa';
$primaryKey = 'pk1'; //PK
// PK

$addIndex = $database->addIndex($namespace, $table, 'nama', 'nama_index', Aerospike::INDEX_TYPE_DEFAULT, Aerospike::INDEX_STRING);
// $key = $database->initKey($namespace, $table, $primaryKey); // $i is Primary
// $data = ['nama' => 'Andika heru', 'pk' => $primaryKey, 'alamat' => 'Jakarta', 'no handphone' => '08170656622'];
// $insertData = $database->put($key, $data);

// $primaryKey = 3;
// $key2 = $database->initKey($namespace, $table, $primaryKey); // $i is Primary
// $data = ['nama' => 'Sofyan', 'pk' => $primaryKey, 'alamat' => 'Jakarta', 'no handphone' => '08170656622'];

// $insertData = $database->put($key2, $data);

// $primaryKey = 4;
// $key3 = $database->initKey($namespace, $table, $primaryKey); // $i is Primary
// $data = ['nama' => 'Nurdiana', 'pk' => $primaryKey, 'alamat' => 'Jakarta', 'no handphone' => '08170656622'];

// $insertData = $database->put($key3, $data);

// $primaryKey = 5;
// $key4 = $database->initKey($namespace, $table, $primaryKey); // $i is Primary
// $data = ['nama' => 'Daffa', 'pk' => $primaryKey, 'alamat' => 'Jakarta', 'no handphone' => '08170656622'];

// $insertData = $database->put($key4, $data);

// $get = $database->get($key, $data);

var_dump($addIndex);
