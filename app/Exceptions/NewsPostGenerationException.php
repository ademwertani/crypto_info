<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by NewsPostGeneratorService on any unrecoverable failure to
 * produce a valid draft (API error after retries, truncated/invalid JSON,
 * missing required fields). Callers should catch this, log it, and move
 * on to the next RSS item rather than let one bad response kill a whole run.
 */
class NewsPostGenerationException extends Exception
{
}
