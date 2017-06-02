<?php

namespace Zan\Framework\Network\Http\Response;

use Zan\Framework\Contract\Network\Response as ResponseContract;

class InternalErrorResponse extends BaseResponse implements ResponseContract
{
    use ResponseTrait;
}