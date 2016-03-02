<?php
namespace Zan\Framework\Foundation\View;

class View
{
    public function render()
    {
        extract($this->getViewVars());
    }

    private function getViewVars()
    {
        return [
            'view' => $this,
            'layout' => new Layout(),
            'form' => new Form(),
            'js' => new Js(),
            'css' => new Css(),
        ];
    }
}