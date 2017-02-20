<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/2/6
 * Time: 下午5:24
 */

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Utilities\File\File;
use Zan\Framework\Utilities\File\OnceFile;

require __DIR__ . "/../../../vendor/autoload.php";

// !! linux 的bs不支持m作为单位 !!!
function randfile($file, $count, $bs = 1024 * 1024 /*m*/)
{
    `dd if=/dev/urandom of=$file bs=$bs count=$count >/dev/null 2>&1`;
    return $count * $bs;
}

$onceWriteTest = function() {
    $f = "tmp1";
    $f_copy = "{$f}_copy";

    $size = randfile($f, rand(2, 9), 1024 * 1023);

    $c = file_get_contents($f);

    $of = new OnceFile();
    $len = (yield $of->putContents($f_copy, $c));

    `diff $f $f_copy`;
    @unlink($f); @unlink($f_copy);
    assert($size === $len);
};
Task::execute($onceWriteTest());




$onceReadTest = function() {
    $f = "tmp";

    $size = randfile($f, rand(2, 9), 1024 * 1023);

    register_shutdown_function(function() use($f) { @unlink($f); });

    $of = new OnceFile();
    $txt = (yield $of->getContents($f));

    assert($size === strlen($txt));
};
Task::execute($onceReadTest());




$readWriteTest = function() {
    $f = "tmp3";
    $f1 = "{$f}_1";
    $f2 = "{$f}_2";
    randfile($f1, rand(2, 9), 1024 * 1023);
    randfile($f2, rand(2, 9), 1024 * 1023);
    register_shutdown_function(function() use($f,$f1,$f2) { @unlink($f);@unlink($f1);@unlink($f2);});


    $file = new File($f);

    $txt1 = file_get_contents($f1);
    $len = (yield $file->write($txt1));
    assert($len === strlen($txt1));

    $txt2 = file_get_contents($f2);
    $len = (yield $file->write($txt2));
    assert($len === strlen($txt2));

    $file->seek(0);

    $txt = (yield $file->read(-1));
    assert($txt === "$txt1$txt2");
    assert($file->eof() === false);

    $txt = (yield $file->read());
    assert($txt === "");
    assert($file->eof() === true);

    $file->seek(0);
    $len = 1024 * 1024 + 1;
    $txt1 = (yield $file->read($len));
    assert(strlen($txt1) === $len);
    assert($file->tell() === $len);

    $txt2 = (yield $file->read($len));
    assert(strlen($txt2) === $len);
    assert($file->tell() === $len * 2);

    $file->seek(0);
};
Task::execute($readWriteTest());


swoole_event_wait();