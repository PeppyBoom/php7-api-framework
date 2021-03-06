<?php
declare (strict_types = 1);

namespace Asd\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Asd\Collections\MapInterface;
use Asd\Http\RequestBody;
use Asd\Http\Headers;

/**
 * Representation of an outgoing, client-side request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * During construction, implementations MUST attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Request extends Message implements RequestInterface
{

    /**
     * @var string
     */
    protected $target = null;

    /**
     * @var string http method
     */
    protected $method;
    
    /**
     * @var Psr\Http\Message\UriInterface
     */
    protected $uri;

    const VALID_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

    /**
     * @param string            $method
     * @param UriInterface|null $uri
     * @param RequestBody|null  $body
     */
    public function __construct(
        string $method = null,
        UriInterface $uri = null,
        StreamInterface $body = null,
        MapInterface $headers = null
    ) {
        $method = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->method = $this->validateMethod($method);
        $this->body = $body ?? new RequestBody();
        $this->uri = $uri ?? (new Uri())->withGlobals();
        $this->headers = $headers ?? (new Headers())->withGlobals();
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget() : string
    {
        if ($this->target !== null) {
            return $this->target;
        }

        return $this->uri->getPath() === '' ? '/' : $this->uri->getPath();
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget) : self
    {
        $clone = clone $this;
        $clone->target = $requestTarget;
        return $clone;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method) : self
    {
        $clone = clone $this;
        $clone->method = $this->validateMethod($method);
        return $clone;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri() : UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false) : self
    {
        $clone = clone $this;
        $clone->uri = $uri;
        if (!$preserveHost) {
            return $uri->getHost() !== '' ? $clone->withHeader('Host', $uri->getHost()) : $clone;
        }
            
        if ((!$this->hasHeader('Host') || empty($this->getHeader('Host'))) && $uri->getHost() !== '') {
            return $clone->withHeader('Host', $uri->getHost());
        }

        return $clone;
    }

    /**
     * Validate and upper case method.
     *
     * @param  string $method
     * @return string method as upper case
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    private function validateMethod(string $method) : string
    {
        if (!in_array(strtoupper($method), self::VALID_METHODS)) {
            throw new InvalidArgumentException('Invalid http method');
        }
        return $method;
    }
}
