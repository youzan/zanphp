<?php
namespace Zan\Framework\Utilities\Pagination;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\View\View;

class Paginator {

    const PREV = 'PREV';
    // 下一页
    const NEXT = 'NEXT';
    // 跳页器
    const JUMP = 'JUMP';
    // 省略 ...
    const OMIT = 'OMIT';
    // 更多
    const MORE = 'MORE';


    public $currentPage;
    public $totalPage;
    public $pageSize;
    public $totalItems;

    private $list = [];

    /**
     * Pager constructor.
     * @param $currentPage
     * @param $totalItems
     * @param $pageSize
     */
    public function __construct($currentPage, $totalItems, $pageSize)
    {
        $this->currentPage = (int) $currentPage;
        $this->pageSize = (int) $pageSize;
        $this->totalItems = (int) $totalItems;
        $this->totalPage = (int) ceil($totalItems / $pageSize);
    }

    /**
     * @return array
     */
    public function output()
    {
        $this->list = [];

        $this->outputPreviousPageButton();
        $this->outputFirstPage();
        $this->outputFirstOmitSymbol();
        $this->outputRangePages();
        $this->outputSecondOmitSymbol();
        $this->outputLastPage();
        $this->outputJumpPage();
        $this->outputNextPageButton();

        return $this->list;
    }

    public function render($tplPath)
    {
        if (empty($tplPath)) {
            throw new InvalidArgumentException('Invalid tplPath for Pagination');
        }
        $tpl = View::display($tplPath, ['paginator' => $this, 'Paginator' => self::class]);
        return $tpl;
    }

    private function push($item)
    {
        array_push($this->list, $item);
    }

    private function outputPreviousPageButton()
    {
        if ($this->currentPage != 1) {
            $this->push(Paginator::PREV);
        }
    }

    private function outputFirstPage()
    {
        $num = $this->currentPage - 2;
        if ($num >= 1) {
            $this->push(1);
        }
    }

    private function outputFirstOmitSymbol()
    {
        $num = $this->currentPage - 2;
        if ($num >= 1 && $num != 1) {
            $this->push(Paginator::OMIT);
        }
    }

    private function outputRangePages()
    {
        $num = $this->currentPage - 1;
        if ($num >= 1) {
            $this->push($num);
        }

        if ($this->totalPage > 1) {
            $this->push($this->currentPage);
        }

        $num = $this->currentPage + 1;
        if ($num <= $this->totalPage) {
            $this->push($num);
        }
    }

    private function outputSecondOmitSymbol()
    {
        $num = $this->currentPage + 2;
        if ($num <= $this->totalPage && $num != $this->totalPage) {
            $this->push(Paginator::OMIT);
        }
    }

    private function outputLastPage()
    {
        $num = $this->currentPage + 2;
        if ($num <= $this->totalPage) {
            $this->push($this->totalPage);
        }
    }

    private function outputJumpPage()
    {
        if ($this->totalPage > 1) {
            $this->push(Paginator::JUMP);
        }
    }

    private function outputNextPageButton()
    {
        if ($this->currentPage < $this->totalPage) {
            $this->push(Paginator::NEXT);
        }
    }
}
