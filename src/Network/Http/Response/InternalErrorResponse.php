<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/13
 * Time: 下午5:43
 */

namespace Zan\Framework\Network\Http\Response;

use Zan\Framework\Contract\Network\Response as ResponseContract;

class InternalErrorResponse extends BaseResponse implements ResponseContract
{
    use ResponseTrait;
}