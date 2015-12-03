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

        $this->assertEquals(10, count($files), 'Dir::scan recusivly without dirs fail');
        $this->assertTrue(in_array('file10', $files), 'Dir::scan recusivly without dirs fail');

        $files = Dir::scan($path,false);
        $this->assertEquals(3, count($files), 'Dir::scan without dirs fail');
        $this->assertTrue(in_array('file2', $files), 'Dir::scan recusivly without dirs fail');
        $this->assertNotTrue(in_array('file10', $files), 'Dir::scan recusivly without dirs fail');

        $files = Dir::scan($path, false, false);
        $this->assertEquals(5, count($files), 'Dir::scan without dirs fail');
        $this->assertTrue(in_array('file2', $files), 'Dir::scan recusivly without dirs fail');
        $this->assertTrue(in_array('dir2', $files), 'Dir::scan recusivly without dirs fail');
        $this->assertNotTrue(in_array('file10', $files), 'Dir::scan recusivly without dirs fail');

        $files = Dir::scan($path, true, false);
        $this->assertEquals(14, count($files), 'Dir::scan without dirs fail');
        $this->assertTrue(in_array('file2', $files), 'Dir::scan recusivly without dirs fail');
        $this->assertTrue(in_array('dir2', $files), 'Dir::scan recusivly without dirs fail');
        $this->assertTrue(in_array('dir4', $files), 'Dir::scan recusivly without dirs fail');
    }

    public function testGlobWorkFine()
    {
        $path = __DIR__ . '/dir/';
        $files = Dir::glob($path, '*.php');

        $this->assertTrue(in_array('file1.php', $files), 'Dir::glob recusivly without dirs fail');
        $this->assertTrue(in_array('file3.php', $files), 'Dir::glob recusivly without dirs fail');
        $this->assertNotTrue(in_array('file2', $files), 'Dir::glob recusivly without dirs fail');
    }

    public function testPatternMatchWork()
    {
        $pattern = '*.php';
        $file = 'file.php';

        $status = Dir::matchPattern($pattern,$file);
        $this->assertTrue($status,'Dir::matchPattern fail');
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