<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/1
 * Time: 20:27
 */

namespace Zan\Framework\Test\Utilities\Types;

require __DIR__ . '/../../../' . 'src/Zan.php';

use Zan\Framework\Utilities\Types\Dir;

class DirTest extends \UnitTest {
    public function testScanWorkFine()
    {
        $path = __DIR__ . '/dir/';
        $files = Dir::scan($path);
        var_dump($files);exit;
    }

    public function testPathFormatWorkFine()
    {
        $path = '/tmp';
        $formatedPath = Dir::formatPath($path);

        $this->assertEquals('/tmp/', $formatedPath, 'Dir::formatPath faild');

        $path = '/tmp/';
        $formatedPath = Dir::formatPath($path);

        $this->assertEquals('/tmp/', $formatedPath, 'Dir::formatPath faild');
    }
}