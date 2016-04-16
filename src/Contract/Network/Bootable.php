<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/4/15
 * Time: 下午2:11
 */

namespace Zan\Framework\Contract\Network;

use Zan\Framework\Network\Http\Server;

interface Bootable {
    public function bootstrap(Server $server);
}
