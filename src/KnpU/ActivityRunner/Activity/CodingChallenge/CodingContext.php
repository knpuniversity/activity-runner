<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;
use KnpU\ActivityRunner\Activity\CodingChallenge\Request\FakedRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Holds information about variables and other things that'll be setup before running code
 */
class CodingContext
{
    private $variables = array();

    /**
     * The root directory where the files will be executed
     *
     * @var string
     */
    private $rootFileDir;

    /** @var FakedRequest */
    private $fakedRequest;

    public function __construct($rootFileDir)
    {
        $this->rootFileDir = $rootFileDir;
    }

    /**
     * Add a variable that'll be made available to the scripts
     *
     * @param string $name
     * @param string $value
     */
    public function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Add a file that you want required before running the code
     *
     * The $localPath is the filename passed to the FileBuilder
     *
     *      $context->addRequire('Product.php');
     *
     * @param string $localPath
     */
    public function requireFile($localPath)
    {
        require $this->rootFileDir.'/'.$localPath;
    }

    /**
     * Call this to start configuring a faked request environment
     *
     * After calling this, just continue mutating the returned object
     *
     * @param string $url
     * @param string $method
     * @return FakedRequest
     */
    public function fakeHttpRequest($url, $method = 'GET')
    {
        if ($this->fakedRequest) {
            throw new \LogicException('There is already a request being faked!');
        }

        $request = new FakedRequest($url, $method);
        $request->addServerVariable('HTTP_USER_AGENT', 'FOO');

        $this->fakedRequest = $request;

        return $this->fakedRequest;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Called right before the code is executed and initializes the environment
     * (e.g. faking the request environment)
     *
     *
     */
    public function initialize()
    {
        if ($this->fakedRequest) {
            // create a Request and tell it to override the globals
            $this->fakedRequest->createRequest()->overrideGlobals();
        }
    }
}
