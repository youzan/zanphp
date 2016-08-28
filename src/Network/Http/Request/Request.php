<?php

namespace Zan\Framework\Network\Http\Request;

use swoole_http_request as SwooleHttpRequest;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Utilities\Types\Str;
use Zan\Framework\Contract\Foundation\Arrayable;
use Zan\Framework\Network\Http\Bag\ParameterBag;
use Zan\Framework\Contract\Network\Request as RequestContract;

class Request extends BaseRequest implements Arrayable, RequestContract
{
    /**
     * The decoded JSON content for the request.
     *
     * @var string
     */
    protected $json;

    /**
     * @var string
     */
    protected $route;

    /**
     * Create a new HTTP request from swoole http request.
     *
     * @return static
     */
    public static function createFromSwooleHttpRequest(SwooleHttpRequest $swooleRequest)
    {
        $get = isset($swooleRequest->get) ? $swooleRequest->get : [];
        $post = isset($swooleRequest->post) ? $swooleRequest->post : [];
        $attributes = [];
        $cookie = isset($swooleRequest->cookie) ? $swooleRequest->cookie : [];
        $files = isset($swooleRequest->files) ? $swooleRequest->files : [];
        $server = isset($swooleRequest->server) ? array_change_key_case($swooleRequest->server, CASE_UPPER) : [];
        if (isset($swooleRequest->header)) {
            foreach ($swooleRequest->header as $key => $value) {
                $newKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
                $server[$newKey] = $value;
            }
        }
        $request = new static($get, $post, $attributes, $cookie, $files, $server);

        // parse http body
        $contentType = $request->headers->get('CONTENT_TYPE');
        $requestMethod = strtoupper($request->server->get('REQUEST_METHOD', 'GET'));
        if (in_array($requestMethod, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $request->content = $swooleRequest->rawContent();

            $data = [];
            if ($request->isJson()) {
                $data = json_decode($request->content, true, 512, JSON_BIGINT_AS_STRING);
                $request->json = new ParameterBag($data);
            } elseif (0 === strpos($contentType, 'application/x-www-form-urlencoded')) {
                parse_str($request->content, $data);
            }
            if ($data) {
                $request->request = new ParameterBag($data);
            }
        }

        return $request;
    }

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string A normalized URI (URL) for the Request
     *
     * @see getQueryString()
     */
    public function getUri()
    {
        if (null !== $qs = $this->getQueryString()) {
            $qs = '?'.$qs;
        }

        // 若使用了反向代理,通过此处理拿到完整的URL
        if ($this->headers->has('X_ZAN_PROXY_DIR')) {
            $baseUrl = '/' . trim($this->headers->get('X_ZAN_PROXY_DIR'), '/');
        } else {
            $baseUrl = $this->getBaseUrl();
        }

        return $this->getSchemeAndHttpHost().$baseUrl.$this->getPathInfo().$qs;
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function getUrlRoot()
    {
        return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function getUrl()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function getFullUrl()
    {
        $query = $this->getQueryString();

        $question = $this->getBaseUrl().$this->getPathInfo() == '/' ? '/?' : '?';

        return $query ? $this->getUrl().$question.$query : $this->getUrl();
    }

    /**
     * Get the full URL for the request with the added query string parameters.
     *
     * @param  array  $query
     * @return string
     */
    public function getFullUrlWithAddedQuery(array $query)
    {
        return count($this->get()) > 0
            ? $this->getUrl().'/?'.http_build_query(array_merge($this->get(), $query))
            : $this->getFullUrl().'?'.http_build_query($query);
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function getPath()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * Get the current encoded path info for the request.
     *
     * @return string
     */
    public function getDecodedPath()
    {
        return rawurldecode($this->getPath());
    }

    /**
     * Get a segment from the URI (1 based index).
     *
     * @param  int  $index
     * @param  string|null  $default
     * @return string|null
     */
    public function getSegment($index, $default = null)
    {
        return Arr::get($this->getSegments(), $index - 1, $default);
    }

    /**
     * Get all of the segments for the request path.
     *
     * @return array
     */
    public function getSegments()
    {
        $segments = explode('/', $this->getPath());

        return array_values(array_filter($segments, function ($v) {
            return $v != '';
        }));
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param  mixed  string
     * @return bool
     */
    public function is()
    {
        foreach (func_get_args() as $pattern) {
            if (Str::is($pattern, urldecode($this->getPath()))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current request URL and query string matches a pattern.
     *
     * @param  mixed  string
     * @return bool
     */
    public function isFullUrlMatch()
    {
        $url = $this->getFullUrl();

        foreach (func_get_args() as $pattern) {
            if (Str::is($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function isPjax()
    {
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Determine if the request contains a given input item key.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function exists($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $input = $this->all();

        foreach ($keys as $value) {
            if (! array_key_exists($value, $input)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $value = $this->input($key);

        $boolOrArray = is_bool($value) || is_array($value);

        return ! $boolOrArray && trim((string) $value) === '';
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        return $this->input();
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function input($key = null, $default = null)
    {
        $input = $this->getInputSource()->all() + $this->query->all();

        return Arr::get($input, $key, $default);
    }

    /**
     * Get a subset of the items from the input data.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = [];

        $input = $this->all();

        foreach ($keys as $key) {
            Arr::set($results, $key, Arr::get($input, $key));
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        Arr::forget($results, $keys);

        return $results;
    }

    /**
     * Retrieve a query string item from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function get($key = null, $default = null)
    {
        return $this->retrieveItem('query', $key, $default);
    }

    /**
     * Retrieve a post string item from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function post($key = null, $default = null)
    {
        return $this->retrieveItem('request', $key, $default);
    }

    /**
     * Determine if a cookie is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCookie($key)
    {
        return ! is_null($this->cookie($key));
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function cookie($key = null, $default = null)
    {
        return $this->retrieveItem('cookies', $key, $default);
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function header($key = null, $default = null)
    {
        return $this->retrieveItem('headers', $key, $default);
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function server($key = null, $default = null)
    {
        return $this->retrieveItem('server', $key, $default);
    }

    /**
     * Retrieve a parameter item from a given source.
     *
     * @param  string  $source
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    protected function retrieveItem($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $default);
    }

    /**
     * Merge new input into the current request's input array.
     *
     * @param  array  $input
     * @return void
     */
    public function merge(array $input)
    {
        $this->getInputSource()->add($input);
    }

    /**
     * Replace the input for the current request.
     *
     * @param  array  $input
     * @return void
     */
    public function replace(array $input)
    {
        $this->getInputSource()->replace($input);
    }

    /**
     * Get the JSON payload for the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (! isset($this->json)) {
            $this->json = new ParameterBag((array) json_decode($this->getContent(), true));
        }

        if (is_null($key)) {
            return $this->json;
        }

        return Arr::get($this->json->all(), $key, $default);
    }

    /**
     * Get the input source for the request.
     *
     * @return \Zan\Framework\Network\Http\Bag\ParameterBag
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return $this->getMethod() == 'GET' ? $this->query : $this->request;
    }

    /**
     * Determine if the given content types match.
     *
     * @param  string  $actual
     * @param  string  $type
     * @return bool
     */
    public static function matchesType($actual, $type)
    {
        if ($actual === $type) {
            return true;
        }

        $split = explode('/', $actual);

        if (isset($split[1]) && preg_match('/'.$split[0].'\/.+\+'.$split[1].'/', $type)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        return Str::contains($this->header('CONTENT_TYPE'), ['/json', '+json']);
    }

    /**
     * Determine if the current request is asking for JSON in return.
     *
     * @return bool
     */
    public function wantsJson()
    {
        $acceptable = $this->getAcceptableContentTypes();
        $isJson = isset($acceptable[0]) && Str::contains($acceptable[0], ['/json', '+json']);
        if (!$isJson) {
            $isJson = stripos($this->getRequestUri(), '.json') !== false;
        }
        return $isJson;
    }

    /**
     * Determines whether the current requests accepts a given content type.
     *
     * @param  string|array  $contentTypes
     * @return bool
     */
    public function accepts($contentTypes)
    {
        $accepts = $this->getAcceptableContentTypes();

        if (count($accepts) === 0) {
            return true;
        }

        $types = (array) $contentTypes;

        foreach ($accepts as $accept) {
            if ($accept === '*/*' || $accept === '*') {
                return true;
            }

            foreach ($types as $type) {
                if ($this->matchesType($accept, $type) || $accept === strtok($type, '/').'/*') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return the most suitable content type from the given array based on content negotiation.
     *
     * @param  string|array  $contentTypes
     * @return string|null
     */
    public function prefers($contentTypes)
    {
        $accepts = $this->getAcceptableContentTypes();

        $contentTypes = (array) $contentTypes;

        foreach ($accepts as $accept) {
            if (in_array($accept, ['*/*', '*'])) {
                return $contentTypes[0];
            }

            foreach ($contentTypes as $contentType) {
                $type = $contentType;

                if (! is_null($mimeType = $this->getMimeType($contentType))) {
                    $type = $mimeType;
                }

                if ($this->matchesType($type, $accept) || $accept === strtok($type, '/').'/*') {
                    return $contentType;
                }
            }
        }
    }

    /**
     * Determines whether a request accepts JSON.
     *
     * @return bool
     */
    public function acceptsJson()
    {
        return $this->accepts('application/json');
    }

    /**
     * Determines whether a request accepts HTML.
     *
     * @return bool
     */
    public function acceptsHtml()
    {
        return $this->accepts('text/html');
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    public function bearerToken()
    {
        $header = $this->header('Authorization', '');

        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource
     * @return string The request body content.
     *
     */
    public function getContent($asResource = false)
    {
        return $this->content;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }
}