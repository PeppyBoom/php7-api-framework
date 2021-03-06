<?php
declare (strict_types = 1);

namespace Asd\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Asd\Collections\MapInterface;
use Asd\Http\ResponseBody;
use Asd\Http\Status;
use Asd\Http\Headers;

/**
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * Responses are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @var Status
     */
    protected $status;
    
    /**
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @var string[] http status codes(key) and standard phrases(value)
     */

    /**
     * Standard HTTP Status Reason Phrases
     * @var standardPhrases
     */
    private $standardPhrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * Default HTTP status code
     */
    const DEFAULT_STATUS_CODE = 200;

    public function __construct(
        Status $status = null,
        StreamInterface $body = null,
        MapInterface $headers = null
    ) {
        $this->status = $status ??  new Status(
            self::DEFAULT_STATUS_CODE,
            $this->standardPhrases[self::DEFAULT_STATUS_CODE]
        );
        $this->body = $body ?? new ResponseBody();
        $this->headers = $headers ?? new Headers();
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode() : int
    {
        return $this->status->getCode();
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '') : self
    {
        if ($reasonPhrase === '' && array_key_exists($code, $this->standardPhrases)) {
            $this->standardPhrases[$code];
        }
        $clone = clone $this;
        $clone->status = $clone->status->withCode($code)->withPhrase($reasonPhrase);
        return $clone;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase() : string
    {
        return $this->status->getPhrase();
    }

    /**
     * Takes mixed inputs and writes it json encoded to the body of a response
     * object and returns a clone.
     *
     * Warning: This method overwrites any previous data in the response body
     *
     * @param  array $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function withJson(array $data) : ResponseInterface
    {
        $jsonBody = $this->getBody();
        $jsonBody->rewind();
        $jsonBody->write(json_encode($data));
        $jsonResponse = $this->withHeader('Content-Type', 'application/json;charset=utf-8');
        return $jsonResponse->withBody($jsonBody);
    }

    /**
     * Takes string inputs and writes it to the body of a response object and
     * returns a clone.
     *
     * Warning: This method overwrites any previous data in the response body
     *
     * @param  string $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function withText(string $data) : ResponseInterface
    {
        $textBody = $this->getBody();
        $textBody->rewind();
        $textBody->write($data);
        $textResponse = $this->withHeader('Content-Type', 'text/html;charset=utf-8');
        return $textResponse->withBody($textBody);
    }
}
