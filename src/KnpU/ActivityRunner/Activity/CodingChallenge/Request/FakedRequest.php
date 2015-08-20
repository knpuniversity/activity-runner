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

    public function __construct($url, $method)
    {
        $this->uri = $url;
        $this->method = $method;
    }

    public function setPostData($postData)
    {
        $this->postData = $postData;
    }

    /**
     * Turns this informtion into a Symfony Request object
     *
     * @return Request
     */
    public function createRequest()
    {
        return Request::create(
            $this->uri,
            $this->method,
            $this->postData,
            array(), // cookies
            array(), // files
            array(), // server
            null // content
        );
    }
}
