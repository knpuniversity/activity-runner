<?php

namespace KnpU\ActivityRunner\Assert;

use KnpU\ActivityRunner\Result;

interface AssertSuiteInterface
{
    public function runTest(Result $resultSummary);
}