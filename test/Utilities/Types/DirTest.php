<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/1
 * Time: 20:27
 */

namespace Zan\Framework\Test\Utilities\Types;


use Zan\Framework\Utilities\Types\Dir;

class DirTest extends \TestCase {

    public function testScanWorkFine()
    {
        $path = __DIR__ . '/dir/';
        $files = Dir::scan($path,Dir::SCAN_BFS);
        $this->assertEquals(10, count($files), 'Dir::scan bfs without dirs fail');
        $this->assertTrue(in_array($path . 'dir2/dir4/file10', $files), 'Dir::scan bfs without dirs fail');

        $files = Dir::scan($path,Dir::SCAN_DFS);
        $this->assertEquals(10, count($files), 'Dir::scan dfs without dirs fail');
        $this->assertTrue(in_array($path . 'dir2/dir4/file10', $files), 'Dir::scan dfs without dirs fail');

        $files = Dir::scan($path,Dir::SCAN_CURRENT_DIR);
        $this->assertEquals(3, count($files), 'Dir::scan current without dirs fail');
        $this->assertTrue(in_array($path . 'file2', $files), 'Dir::scan current without dirs fail');
        $this->assertNotTrue(in_array($path . 'dir2/dir4/file10', $files), 'Dir::scan current without dirs fail');

        $files = Dir::scan($path, Dir::SCAN_CURRENT_DIR, false);
        $this->assertEquals(5, count($files), 'Dir::scan without dirs fail');
        $this->assertTrue(in_array($path . 'file2', $files), 'Dir::scan recusivly without dirs fail');
        $this->assertTrue(in_array($path . 'dir2/', $files), 'Dir::scan recusivly without dirs fail');
        $this->assertNotTrue(in_array($path . 'dir2/dir4/file10', $files), 'Dir::scan recusivly without dirs fail');

        $files = Dir::scan($path, Dir::SCAN_BFS, false);
        $this->assertEquals(14, count($files), 'Dir::scan bfs without dirs fail');
        $this->assertTrue(in_array($path . 'file2', $files), 'Dir::scan bfs without dirs fail');
        $this->assertTrue(in_array($path . 'dir2/', $files), 'Dir::scan bfs without dirs fail');
        $this->assertTrue(in_array($path . 'dir2/dir4/', $files), 'Dir::scan bfs without dirs fail');

        $files = Dir::scan($path, Dir::SCAN_DFS, false);
        $this->assertEquals(14, count($files), 'Dir::scan dfs without dirs fail');
        $this->assertTrue(in_array($path . 'file2', $files), 'Dir::scan dfs without dirs fail');
        $this->assertTrue(in_array($path . 'dir2/', $files), 'Dir::scan dfs without dirs fail');
        $this->assertTrue(in_array($path . 'dir2/dir4/', $files), 'Dir::scan dfs without dirs fail');
    }

    public function testGlobWorkFine()
    {
        $path = __DIR__ . '/dir/';
        $files = Dir::glob($path, '*.php');

        $this->assertTrue(in_array($path . 'file1.php', $files), 'Dir::glob recusivly without dirs fail');
        $this->assertTrue(in_array($path . 'file3.php', $files), 'Dir::glob recusivly without dirs fail');
        $this->assertNotTrue(in_array($path . 'file2', $files), 'Dir::glob recusivly without dirs fail');
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

    public function testBasenameWorkFine()
    {
        $path = __DIR__ . '/dir/';
        $files = Dir::glob($path, '*.php', Dir::SCAN_CURRENT_DIR);

        $results = Dir::basename($files, '.php');
        $this->assertContains('file1',$results, 'Dir::basename with suffix faild');
        $this->assertContains('file3',$results, 'Dir::basename with suffix faild');

        $results = Dir::basename($files);
        $this->assertContains('file1.php',$results, 'Dir::basename with suffix faild');
        $this->assertContains('file3.php',$results, 'Dir::basename with suffix faild');
    }
}