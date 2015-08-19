<?php

namespace KnpU\ActivityRunner\Activity\CodingChallenge;

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

    public function getVariables()
    {
        return $this->variables;
    }
}
