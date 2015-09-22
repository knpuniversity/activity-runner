<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge\Request;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows you to configure a "fake" request environment, if you need to
 */
class FakedRequest
{
    private $uri;

    private $method;

    private $postData = array();

    private $server = array();

    private $headers = array();

    public function __construct($url, $method)
    {
        $this->uri = $url;
        $this->method = $method;
    }

    public function setPostData(array $postData)
    {
        $this->postData = $postData;
    }

    public function addServerVariable($key, $val)
    {
        $this->server[strtoupper($key)] = $val;
    }

    public function addHeader($name, $val)
    {
        $this->headers[$name] = $val;
    }

    /**
     * Turns this informtion into a Symfony Request object
     *
     * @return Request
     */
    public function createRequest()
    {
        $request = Request::create(
            $this->uri,
            $this->method,
            $this->postData,
            array(), // cookies
            array(), // files
            $this->server, // server
            null // content
        );

        $request->headers->add($this->headers);

        return $request;
    }
}
