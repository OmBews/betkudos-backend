<?php

namespace App\Http\Clients\BetsAPI\Responses;

use App\Exceptions\BetsAPI\APICallException;
use Illuminate\Support\Arr;

abstract class BetsAPIResponse
{
    public $results;

    public $success;

    protected $pager;

    public $error;

    private $content;

    /**
     * BetsAPIResponse constructor.
     * @param string $content
     * @throws APICallException
     */
    public function __construct(string $content)
    {
        $this->content = $content;

        $decoded = json_decode($content);

        $this->success = $decoded->success;

        if ($this->success == 0) {
            $this->error = $decoded->error ?? APICallException::SERVER_ERROR;

            throw new APICallException($this->error);
        }

        $this->results = $decoded->results;
        $this->pager = $decoded->pager ?? null;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
