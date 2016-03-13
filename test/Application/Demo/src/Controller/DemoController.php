<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/3
 * Time: 23:43
 */

namespace Com\Youzan\Demo\Controller;

use Zan\Framework\Foundation\Coroutine\Event;
use Zan\Framework\Foundation\Domain\Controller;

class DemoController extends Controller
{
    public function getIndexHtml($request, $context)
    {
        return $this->display('aaa');
    }
}



class View {
    public function __construct(){
        $this->event = new Event();

        $this->layout = new Layout($this->event);
        $this->js    = new Js($this->event);
    }
}

class Layout {
    public function __construct($event){
        $this->event = $event;
    }

    public function block($blockName)
    {
        //do .....

        $this->event->fire('start_block' , $blockName);
    }
}





class Js {
    public function __construct($event){
        $this->event = $event;
        $this->bindEvents();
    }

    private function bindEvents()
    {
        $that = $this;
        $this->event->bind('start_block', function($args) use ($that){
            $that->setCurrentBlock($args);
        });

        $this->event->bind('override_block',function(){

        });
    }

    private function setCurrentBlock($blockName){
        $this->currentBlock = $blockName;
    }


    public function load($file){
        var_dump($this->currentBlock);
    }

    public function toJs()
    {

    }
}