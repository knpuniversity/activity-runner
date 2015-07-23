<?php

namespace KnpU\ActivityRunner;

use KnpU\ActivityRunner\Assert\Helper\FileSource;
use KnpU\ActivityRunner\Assert\Helper\FileSourceCollection;
use KnpU\ActivityRunner\Assert\Helper\Output;
use KnpU\ActivityRunner\Worker\WorkerBag;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Actually executes an Activity and then passes it to the suite for validation
 *
 * This relies on "workers" behind the scenes - e.g. there is a different worker for
 * "php" activities, versus Twig activities, etc.
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class ActivityRunner
{
    /**
     * @var WorkerBag
     */
    protected $workerBag;

    /**
     * @param WorkerBag $workerBag
     */
    public function __construct(WorkerBag $workerBag) {
        $this->workerBag = $workerBag;
    }

    /**
     * @param Activity $activity
     *
     * @return \KnpU\ActivityRunner\Result
     */
    public function run(Activity $activity)
    {
        $worker = $this->getWorker($activity->getWorkerName());

        $result = $worker->execute($activity);

        $this->executeAsserts($activity, $result);

        return $result;
    }

    private function executeAsserts(Activity $activity, Result $result)
    {
        $expressionLanguage = new ExpressionLanguage();
        $sourceFunction = new ExpressionFunction(
            'source',
            function() {
                throw new \Exception('no supported - at least not now');
            },
            function(array $variables, $value) {
                // ...
                return $variables['files']->getFile($value);
            }
        );
        $expressionLanguage->addFunction($sourceFunction);

        $fileSourceCollection = new FileSourceCollection();
        foreach ($activity->getInputFiles() as $filename => $source) {
            $fileSource = new FileSource($source);
            $fileSourceCollection->addFile($filename, $fileSource);
        }

        $variables = array(
            'files' => $fileSourceCollection,
            'output' => new Output($result->getOutput()),
        );

        foreach ($activity->getAssertExpressions() as $assertExpression)
        {
            if (!$expressionLanguage->evaluate($assertExpression, $variables)) {
                // we don't really have a reason right now
                $result->setValidationError('Incorrect');
            }
        }
    }

    /**
     * @param string $workerName
     *
     * @return \KnpU\ActivityRunner\Worker\WorkerInterface
     */
    private function getWorker($workerName)
    {
        return $this->workerBag->get($workerName);
    }
}
