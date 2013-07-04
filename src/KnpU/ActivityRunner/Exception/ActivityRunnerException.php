<?php

namespace KnpU\ActivityRunner\Exception;

/**
 * All activity runner exceptions implement this interface to be able to
 * catch by library while still allowing exceptions to inherit from the
 * correct exceptions (e.g. LogicException or RuntimeException).
 *
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
interface ActivityRunnerException
{

}
