<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/2/6
 * Time: 下午5:24
 */
namespace Zan\Framework\Test\Utilities\File;

use Zan\Framework\Testing\TaskTest;
use Zan\Framework\Utilities\File\File;
use Zan\Framework\Utilities\File\OnceFile;

class FileOpTest extends TaskTest {
    private $files = [];

    // !! linux 的bs不支持m作为单位 !!!
    private function randfile($file, $count, $bs = 1024 * 1024 /*m*/)
    {
        `dd if=/dev/urandom of=$file bs=$bs count=$count >/dev/null 2>&1`;
        return $count * $bs;
    }

    public function taskallTest()
    {
        yield $this->onceWrite();
        yield $this->onceRead();
        yield $this->readWrite();
        yield $this->destruct();
    }

    public function onceWrite()
    {
        $f = "tmp";
        $f_copy = "{$f}_copy";

        $size = $this->randfile($f, rand(2, 9), 1024 * 1023);

        $c = file_get_contents($f);

        $of = new OnceFile();
        $len = (yield $of->putContents($f_copy, $c));

        `diff $f $f_copy`;
        $this->files[] = $f;
        $this->files[] = $f_copy;
        $this->assertEquals($size, $len, "onceWrite failed");
    }

    public function onceRead()
    {
        $f = "tmp1";

        $size = $this->randfile($f, rand(2, 9), 1024 * 1023);

        $of = new OnceFile();
        $txt = (yield $of->getContents($f));

        $this->assertEquals($size, strlen($txt), "onceRead failed");
        $this->files[] = $f;
    }

    public function readWrite()
    {
        $f = "tmp2";
        $f1 = "{$f}_1";
        $f2 = "{$f}_2";
        $this->randfile($f1, rand(2, 9), 1024 * 1023);
        $this->randfile($f2, rand(2, 9), 1024 * 1023);

        $file = new File($f);

        $txt1 = file_get_contents($f1);
        $len = (yield $file->write($txt1));
        $this->assertEquals($len, strlen($txt1), "readWrite $f1 failed");

        $txt2 = file_get_contents($f2);
        $len = (yield $file->write($txt2));
        $this->assertEquals($len, strlen($txt2), "readWrite $f2 failed");

        $file->seek(0);

        $txt = (yield $file->read(-1));
        $this->assertEquals($txt, "$txt1$txt2", "read AllContents failed");

        $file->seek(0);
        $len = 1024 * 1024 + 1;
        $txt1 = (yield $file->read($len));
        $this->assertEquals(strlen($txt1), $len, "Length of $txt1 not equal to $len");
        $this->assertEquals($file->tell(), $len, "File tell failed");

        $txt2 = (yield $file->read($len));
        $this->assertEquals(strlen($txt2), $len, "Length of $txt2 not equal to $len");
        $this->assertEquals($file->tell(), $len * 2, "File tell failed");

        $file->seek(0);
        $this->files[] = $f;
        $this->files[] = $f1;
        $this->files[] = $f2;
    }

    public function destruct()
    {
        foreach ($this->files as $file) {
            unlink($file);
        }

    }
}
