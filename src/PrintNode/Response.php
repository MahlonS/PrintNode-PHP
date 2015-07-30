<?php

namespace PrintNode;

/**
 * Response
 *
 * HTTP response object.
 */
class Response
{
    /**
     * Original Request URL
     * @var string
     */
    private $url;

    /**
     * Response headers
     * @var mixed[]
     */
    private $headers;

    /**
     * Response body
     * @var string
     */
    private $content;

    /**
     * Constructor
     * @param mixed $url
     * @param mixed $content
     * @param mixed $headers
     * @return Response
     */
    public function __construct($url, $content, array $headers)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->content = $content;
    }

    /**
     * Get Response body
     * @param void
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get Response headers
     * @param void
     * @return mixed[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get Response body decoded into an array
     * @param void
     * @return mixed
     */
    public function getDecodedContent()
    {
        $decoded = json_decode($this->content, true);
        // have error?
        if (null === $decoded and JSON_ERROR_NONE !== $lastError = json_last_error()) {
            $message = sprintf(<<<TEXT
PrintNode API did not return valid JSON for request %s.

--- BEGIN SERVER RESPONSE ---
%s
--- END SERVER RESPONSE ---
TEXT
                , $this->url,
                $this->content
            );
            throw new \RuntimeException($message);
        }
        return $decoded;
    }

    /**
     * Get HTTP status code
     * @param void
     * @return string
     */
    public function getStatusCode()
    {
        $status = $this->getStatus();
        return $status['code'];
    }

    /**
     * Get HTTP status code
     * @param void
     * @return string
     */
    public function getStatusMessage()
    {
        $status = $this->getStatus();
        return $status['message'];
    }

    /**
     * Extract the HTTP status code and message
     * from the Response headers
     * @param void
     * @return mixed[]
     */
    private function getStatus()
    {
        if (!($statusArray = preg_grep('/^HTTP\/(1.0|1.1)\s+(\d+)\s+(.+)/', $this->headers))) {
            throw new \RuntimeException('Could not determine HTTP status from API response');
        }

        if (!preg_match('/^HTTP\/(1.0|1.1)\s+(\d+)\s+(.+)/', $statusArray[0], $matchesArray)) {
            throw new \RuntimeException('Could not determine HTTP status from API response');
        }

        return array(
            'code' => $matchesArray[2],
            'message' => $matchesArray[3],
        );
    }

}
