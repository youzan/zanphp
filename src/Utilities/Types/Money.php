<?php
namespace Zan\Framework\Utilities\Types;


class Money
{
    private $num = 0;
    
    public function __construct($num)
    {
        $this->num = $num;
    }

    public function toRmb()
    {

    }

    public function toDollar()
    {

    }

    public function toYuan()
    {
        return number_format(round($this->num / 100, 2, PHP_ROUND_HALF_EVEN), 2, '.', '');
    }

    public function rmDot()
    {
        return intval(number_format(round($this->num * 100, 0, PHP_ROUND_HALF_EVEN), 0, '.', ''));
    }

    public function addZero()
    {
        return number_format($this->num, 2, '.', '');
    }

    public function rmDecimalAndAddZero()
    {
        return number_format(intval(round($this->num / 100, 2, PHP_ROUND_HALF_EVEN)), 2, '.', '');
    }

}