#!/usr/bin/env php
<?php

$dir = __DIR__ . "/src";
$regex = '/.*\.php$/';
$iter = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
$iter = new \RecursiveIteratorIterator($iter, \RecursiveIteratorIterator::LEAVES_ONLY);
$iter = new \RegexIterator($iter, $regex, \RegexIterator::GET_MATCH);

foreach ($iter as $file) {
    $realPath = realpath($file[0]);
    echo `php -l $realPath`;
}