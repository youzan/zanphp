<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 16/7/6
 * Time: 16:42
 */

namespace Zan\Framework\Test\Foundation\Exception;

use Zan\Framework\Foundation\Exception\BusinessException;

class BusinessExceptionTest extends \TestCase
{
    public function testIsValidCode()
    {
        $result = BusinessException::isValidCode(0);
        $this->assertFalse($result);

        $result = BusinessException::isValidCode(9999);
        $this->assertFalse($result);

        $result = BusinessException::isValidCode(10000);
        $this->assertTrue($result);

        $result = BusinessException::isValidCode(60000);
        $this->assertTrue($result);
        
        $result = BusinessException::isValidCode(60001);
        $this->assertFalse($result);
        
        $result = BusinessException::isValidCode(100000);
        $this->assertFalse($result);

        $result = BusinessException::isValidCode(1000000);
        $this->assertFalse($result);

        $result = BusinessException::isValidCode(10000000);
        $this->assertFalse($result);
        
        $result = BusinessException::isValidCode(100000000);
        $this->assertTrue($result);

        $result = BusinessException::isValidCode(1100000000);
        $this->assertFalse($result);
    }
}