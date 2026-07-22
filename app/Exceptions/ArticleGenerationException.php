<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown by ArticleGeneratorService on any unrecoverable failure to produce
 * a valid draft (API error after retries, truncated/invalid JSON, missing
 * required fields). Callers should catch this, log it, and move on to the
 * next article rather than let one bad response kill a whole run.
 */
class ArticleGenerationException extends Exception
{
}
