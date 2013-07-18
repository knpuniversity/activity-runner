<?php

namespace KnpU\ActivityRunner\Assert\Suite;

use KnpU\ActivityRunner\Result;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 *
 * @author Kristen Gilden <kristen.gilden@gmail.com>
 */
class Passed implements StateInterface
{
    /**
     * {@inheritDoc}
     */
    public function isAllowedToRun(Result $result)
    {
        return !$result->hasLanguageError();
    }
}
