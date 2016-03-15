<?php

namespace Zan\Framework\Network\Http\Response;

use Exception;
use ArrayObject;
use JsonSerializable;
use Zan\Framework\Contract\Foundation\Jsonable;

class Response extends BaseResponse
{
    use ResponseTrait;

    /**
     * The original content of the response.
     *
     * @var mixed
     */
    public $original;

    /**
     * The exception that triggered the error response (if applicable).
     *
     * @var \Exception
     */
    public $exception;

    /**
     * Set the content on the response.
     *
     * @param  mixed  $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->original = $content;

        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        if ($this->shouldBeJson($content)) {
            $this->headers->set('Content-Type', 'application/json');

            $content = $this->morphToJson($content);
        }

        // If this content implements the "Renderable" interface then we will call the
        // render method on the object so we will avoid any "__toString" exceptions
        // that might be thrown and have their errors obscured by PHP's handling.
        /*elseif ($content instanceof Renderable) {
            $content = $content->render();
        }*/

        return parent::setContent($content);
    }

    /**
     * Morph the given content into JSON.
     *
     * @param  mixed   $content
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        }

        return json_encode($content);
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof Jsonable ||
               $content instanceof ArrayObject ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }

    /**
     * Get the original response content.
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        return $this->original;
    }

    /**
     * Set the exception to attach to the response.
     *
     * @param  \Exception  $e
     * @return $this
     */
    public function withException(Exception $e)
    {
        $this->exception = $e;

        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return BaseResponse
     */
    public function sendBySwoole(\swoole_http_response $swooleHttpResponse)
    {
        $this->sendHeaders($swooleHttpResponse);
        $this->sendContent($swooleHttpResponse);
    }

    /**
     * Sends HTTP headers.
     *
     * @return BaseResponse
     */
    public function sendHeaders(\swoole_http_response $swooleHttpResponse)
    {
        if (!$this->headers->has('Date')) {
            $this->setDate(\DateTime::createFromFormat('U', time()));
        }

        // headers
        foreach ($this->headers->allPreserveCase() as $name => $values) {
            foreach ($values as $value) {
                $swooleHttpResponse->header($name, $value);
            }
        }

        // status
        $swooleHttpResponse->status($this->statusCode);

        // status
        //header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        // cookies
        foreach ($this->headers->getCookies() as $cookie) {
            $swooleHttpResponse->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @return BaseResponse
     */
    public function sendContent(\swoole_http_response $swooleHttpResponse)
    {
        $swooleHttpResponse->end($this->content);

        return $this;
    }
}
