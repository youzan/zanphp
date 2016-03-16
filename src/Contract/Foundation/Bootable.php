<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/16
 * Time: 21:23
 */

namespace Zan\Framework\Contract\Foundation;

use Zan\Framework\Foundation\Application;

interface Bootable {
    public function bootstrap(Application $app);
}