<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/13
 * Time: 18:45
 */

namespace Zan\Framework\Test\Network\Server;

require __DIR__ . '/../../../' . 'src/Zan.php';

use Zan\Framework\Network\Server\Time;


class TimeTest extends \UnitTest {
    public function testTimeFormatWorkFine()
    {
        $time = new Time();
        $ts = time();

        $result = $time->format('U', $ts);
        $expect = date('U', $ts);

        $this->assertEquals($expect, $result, 'Time.format fail');
        $this->setExpectedException('Zan\\Framework\\Foundation\\Exception\\System\\InvalidArgument');
        $time->format('U');
    }
}