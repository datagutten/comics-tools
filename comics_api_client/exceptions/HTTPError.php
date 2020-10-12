<?php


namespace datagutten\comics_tools\comics_api_client\exceptions;


use Requests_Response;
use Throwable;

class HTTPError extends ComicsException
{
    /**
     * @var Requests_Response
     */
    public $response;

    function __construct(string $message, Requests_Response $response, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }
}