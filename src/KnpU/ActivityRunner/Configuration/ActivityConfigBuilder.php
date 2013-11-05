<?php

namespace KnpU\ActivityRunner\Configuration;

use KnpU\ActivityRunner\Exception\FileNotFoundException;
use KnpU\ActivityRunner\Exception\UnexpectedTypeException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityConfigBuilder
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ConfigurationInterface
     */
    protected $definition;

    /**
     * @var Yaml
     */
    protected $yaml;

    /**
     * @var PathExpander|null
     */
    protected $pathExpander;

    protected $tree;

    /**
     * @param Processor $processor
     * @param ConfigurationInterface $definition
     * @param Yaml $yaml
     * @param PathExpander|null $pathExpander
     */
    public function __construct(
        Processor $processor,
        ConfigurationInterface $definition,
        Yaml $yaml,
        PathExpander $pathExpander = null
    ) {
        $this->processor  = $processor;
        $this->definition = $definition;
        $this->yaml       = $yaml;
        $this->setExpander($pathExpander);
    }

    /**
     * @param PathExpander|null $expander
     */
    public function setExpander(PathExpander $expander = null)
    {
        $this->pathExpander = $expander;
    }

    /**
     * Builds the activity configuration from the specified configuration files.
     *
     * @param string|array $paths
     *
     * @return array
     */
    public function build($paths)
    {
        if ($expander = $this->pathExpander) {
            $paths = $expander->expand($paths, 'activities.yml');
        }

        if (is_string($paths)) {
            $paths = array($paths);
        }

        if (!is_array($paths)) {
            throw new UnexpectedTypeException($paths, 'string" or "array');
        }

        $configs = array();

        foreach ($paths as $configPath) {
            if (!is_file($configPath)) {
                throw new FileNotFoundException($configPath);
            }

            $configBaseDir = dirname($configPath);

            $rawConfig = $this->yaml->parse(file_get_contents($configPath));
            $rawConfig = $this->resolveRelativePaths($configBaseDir, $rawConfig);

            // send back the base_dir in case some more paths need to be made absolute
            // todo - we should really return some configuration objects - the arrays and paths are tough to keep track of!
            foreach ($rawConfig as $key => $activityData) {
                // store the base dir, which may be useful by whatever is using this
                $rawConfig[$key]['base_dir'] = $configBaseDir;
            }

            $configs[] = $rawConfig;
        }

        $config = $this->processor->processConfiguration($this->definition, $configs);

        return $config;
    }

    /**
     * Certain metadata keys, like "context" and "asserts" represent PHP files that
     * are *not* sent to this application (as opposed to "skeletons" because when we
     * run an activity, the user-submitted content for each skeleton is passed to us).
     *
     * We need to resolve these keys to absolute paths to these files so that they
     * can be loaded and used.
     *
     * Tries to resolve the relative paths given the initial configuration file
     * location. It tries to match keys from the specified list and then go
     * through its elements and if a path is relative, it will be turned into
     * an absolute one.
     *
     * The whole process is recursive. Some examples:
     *
     * <code>
     *
     *     // 1.
     *     array(
     *         'skeletons' => 'foo.txt', 'context' => array('baz.txt', 'bar.txt'),
     *     )
     *
     *     // gets turned into:
     *     array(
     *         'skeletons' => 'foo.txt', 'context' => array('/base/dir/baz.txt', '/base/dir/bar.txt'),
     *     )
     *
     *     // 2.
     *     array(
     *         'random' => array('skeletons' => 'foo.txt'),
     *         'context' => array('baz.txt', 'bar.txt'),
     *     )
     *
     *     // gets turned into:
     *     array(
     *         'random' => array('skeletons' => 'foo.txt'),
     *         'context' => array('/base/dir/baz.txt', '/base/dir/bar.txt'),
     *     )
     *
     * </code>
     *
     * @param string $baseDir       The base directory added to matched relative paths
     * @param array $configuration  Unresolved configuration tree
     *
     * @return array  The resolved configuration
     */
    protected function resolveRelativePaths($baseDir, array $configuration)
    {
        // Try to resolve the values of the following keys only. The values may
        // also be arrays in which case they will be iterated over.
        $keys = array(
            'context',
            'asserts',
        );

        foreach ($configuration as $key => $paths) {
            if (in_array($key, $keys)) {

                // Copy the original value to a temporary one so that we would not
                // accidentally change the type to array.
                $tmpPaths = $paths;

                if (!is_array($tmpPaths)) {
                    $tmpPaths = array($paths);
                }

                foreach ($tmpPaths as $pathKey => $tmpPath) {

                    if (0 !== strpos($tmpPath, '/', 0)) {
                        // The path is relative, add the base.
                        $tmpPaths[$pathKey] = $baseDir.'/'.$tmpPath;
                    }
                }

                // Store the resolved paths again back to the original value
                // which is used by reference in this function.
                $configuration[$key] = is_array($paths) ? $tmpPaths : $tmpPaths[0];

            } elseif (is_array($paths)) {

                $configuration[$key] = $this->resolveRelativePaths($baseDir, $paths);

            }
        }

        return $configuration;
    }
}
