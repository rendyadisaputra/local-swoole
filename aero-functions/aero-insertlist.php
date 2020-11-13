<?php

$key = $db->initKey($namespace, $table, $pk = 1);
$bin = 'col-value';
$index = 12;
$value = ['m' => 'mixed value'];

$db->listInsert($key, $bin, $index, $value);

var_dump($db->listSize($key, $bin, $count));
var_dump($count);
