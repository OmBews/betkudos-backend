<?php

namespace App\Exceptions\BetsAPI;

use Exception;
use Throwable;

class APICallException extends Exception
{
    public const SERVER_ERROR = 'INTERNAL_SERVER_ERROR';

    public const NOT_FOUND = 'NOT_FOUND';

    public const METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';

    public const AUTHORIZE_FAILED = 'AUTHORIZE_FAILED';

    public const TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS';

    public const PERMISSION_DENIED = 'PERMISSION_DENIED';

    public const PARAM_REQUIRED = 'PARAM_REQUIRED';

    public const PARAM_INVALID = 'PARAM_INVALID';

    public const UNAVAILABLE_MATCH_RESULT = 'UNAVAILABLE_MATCH_RESULT';

    private $error;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->error = $message;
        $this->buildMessage();

        parent::__construct($this->message, $code, $previous);
    }

    private function buildMessage()
    {
        $this->message = "BetsAPI request failed: {$this->error}";
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
