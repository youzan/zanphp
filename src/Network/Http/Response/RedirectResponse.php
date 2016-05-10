<?php

namespace Zan\Framework\Network\Http\Response;

use Zan\Framework\Contract\Network\Response as ResponseContract;

class RedirectResponse extends BaseRedirectResponse implements ResponseContract
{
    use ResponseTrait;
}
