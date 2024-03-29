<?php


namespace datagutten\comics_tools\comics_api_client\exceptions;


use WpOrg\Requests;
use Throwable;

class HTTPError extends ComicsException
{
    /**
     * @var Requests\Response
     */
    public $response;

    function __construct(string $message, Requests\Response $response, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }
}