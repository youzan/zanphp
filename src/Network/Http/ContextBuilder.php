<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/26
 * Time: 19:59
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Domain\Context;
use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class ContextBuilder {
    
    private $context = null;

    public function __construct(Context $context) {
        if(!$context) {
            throw new InvalidArgument('invalid context for ContextBuilder');
        }

        $this->context = $context;
    }

    public function build() {
        $this->dobuilding();

        return $this->context;
    }


    private function doBuilding() {

    }
}