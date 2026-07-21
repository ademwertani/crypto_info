<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by MoneyPageGeneratorService on any unrecoverable failure to
 * produce a valid page (API error after retries, truncated/invalid JSON,
 * missing required fields). Callers should catch this, log it, and move
 * on to the next page rather than let one bad response kill a whole run.
 */
class MoneyPageGenerationException extends Exception
{
}
