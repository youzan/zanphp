<?php
namespace Zan\Framework\Foundation\View;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

use Zan\Framework\Foundation\View\Tpl;
use Zan\Framework\Foundation\Coroutine\Event;

class Layout
{
    private $tpl           = null;
    private $tplPath       = '';
    private $blocks        = [];

    private $event         = null;

    private $curLevel      = 0;
    private $blockQueue    = [];
    private $blockLevelMap = [];

    private $blockPre      = '%%BLOCK__';
    private $blockSuf      = '__BLOCK%%';

    public function __construct(Tpl $tpl, Event $event, $tplPath)
    {
        $this->tpl = $tpl;
        $this->event = $event;
        $this->tplPath = $tplPath;
    }

    public function render()
    {
        ob_start();
        $this->tpl->load($this->tplPath);
        $html = ob_get_clean();
        return $this->replaceBlocksLevelByLevel($html);
    }

    public function extend($tplPath)
    {
        $this->tpl->load($tplPath);
    }

    public function block($blockName)
    {
        $blockName  = strtoupper($blockName);
        $parentBlock = $this->getCurrentBlock();
        if($parentBlock == $blockName){
            throw new InvalidArgumentException('子block与父block不允许重名,block名称：'. $blockName);
        }
        $this->curLevel++;
        array_push($this->blockQueue,$blockName);
        ob_start();
        $this->event->fire('start_block', $blockName);
        return true;
    }

    public function endBlock($blockName = null)
    {
        $this->curLevel--;
        $curBlock = array_pop($this->blockQueue);
        if($blockName && $curBlock !== strtoupper($blockName)){
            $errorMsg = 'block数量与endBlock数量不匹配，错误block名为:' . strtolower($curBlock) . ' vs ' . $blockName;
            throw new InvalidArgumentException($errorMsg);
        }
        $content = ob_get_clean();
        $content = trim($content);

        if(isset($this->blocks[$curBlock])){
            $this->blocks[$curBlock] = $content;
            return true;
        }
        $this->blocks[$curBlock] = $content;
        $this->addBlockToLevelMap($curBlock,$this->curLevel);
        echo $this->blockPre . $curBlock . $this->blockSuf;
        $this->event->fire('end_block', $curBlock);
    }

    public function place($blockName, $content = '')
    {
        $blockName  = strtoupper($blockName);
        $parentBlock = $this->getCurrentBlock();
        if($parentBlock == $blockName){
            throw new InvalidArgumentException('子block与父block不允许重名,block名称：'. $blockName);
        }
        if(isset($this->blocks[$blockName])){
            $this->blocks[$blockName] = $content;
            return true;
        }
        $this->blocks[$blockName] = $content;
        $this->addBlockToLevelMap($blockName,$this->curLevel);
        echo $this->blockPre . $blockName . $this->blockSuf;
    }

    public function super($blockName = null)
    {
        $blockName = is_null($blockName) ? $this->getCurrentBlock() : strtoupper($blockName);
        if(is_null($blockName)) {
            throw new InvalidArgumentException("no super block exists");
        }
        if(!isset($this->blocks[$blockName])) {
            throw new InvalidArgumentException('no such super block:' . $blockName);
        }
        echo $this->blocks[$blockName];
    }

    public function getCurrentBlock()
    {
        if(!$this->blockQueue) return null;
        $lastIdx = count($this->blockQueue) - 1;
        return $this->blockQueue[$lastIdx];
    }

    private function addBlockToLevelMap($blockName, $level = 0)
    {
        if(!isset($this->blockLevelMap[$level])){
            $this->blockLevelMap[$level] = [];
        }
        array_push($this->blockLevelMap[$level],$blockName);
    }

    private function replaceBlocksLevelByLevel($tpl)
    {
        if(!$this->blockLevelMap)
            return $tpl;
        $len = count($this->blockLevelMap);
        for($i=0; $i<$len; $i++){
            if(!isset($this->blockLevelMap[$i])) continue;
            $tpl = $this->replaceBlocksOfOneLevel($tpl, $this->blockLevelMap[$i]);
        }
        return $tpl;
    }

    private function replaceBlocksOfOneLevel($tpl, $level)
    {
        if(!$level) return $tpl;
        $blocks = [];
        foreach($level as $blockName){
            if( !isset($this->blocks[$blockName]) ) continue;
            $key = $this->blockPre . $blockName . $this->blockSuf;
            $val = $this->blocks[$blockName];
            $blocks[$key] = $val;

            unset($this->blocks[$blockName]);
        }
        if(empty($blocks)) return $tpl;
        return str_replace(array_keys($blocks),array_values($blocks),$tpl);
    }
}