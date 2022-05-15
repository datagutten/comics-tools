<?php


namespace datagutten\comics_tools\comics_api_client\exceptions;


use WpOrg\Requests;
use Throwable;

class NoResultsException extends ComicsException
{
    /**
     * @var Requests\Response
     */
    public $response;

    function __construct(string $uri, Requests\Response $response, $code = 0, Throwable $previous = null)
    {
        $this->response = $response;
        $message = sprintf('No results for query %s', $uri);
        parent::__construct($message, $code, $previous);
    }
}