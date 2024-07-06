<?php

namespace cjrasmussen\Request;

class RequestData
{
	public const METHOD_NONE = '';
	public const METHOD_GET = 'GET';
	public const METHOD_POST = 'POST';
	public const METHOD_PUT = 'PUT';
	public const METHOD_PATCH = 'PATCH';
	public const METHOD_DELETE = 'DELETE';

	public RequestCollection $cookies;
	public RequestCollection $delete;
	public RequestCollection $query;
	public RequestCollection $patch;
	public RequestCollection $post;
	public RequestCollection $put;
	public RequestCollection $files;
	public RequestCollection $server;
	public RequestCollection $env;

	public string $method;
	public int $timestamp;
	protected string $requestIdPrefix = '';
	protected string $requestId;

	public function __construct(array $get = [], array $post = [], array $cookies = [], array $files = [], array $server = [], array $env = []) {
		$this->server = new RequestCollection($server);
		$this->requestId = uniqid($this->requestIdPrefix, true);
		$this->method = ($this->server->has('REQUEST_METHOD')) ? $this->server->get('REQUEST_METHOD') : self::METHOD_NONE;
		$this->timestamp = time();

		// spoof the form method to allow for PUT, PATCH, DELETE
		if ($this->method === self::METHOD_POST) {
			$incomingData = $post;
			$post = [];
		} else {
			$incomingData = [];
		}

		$this->query = new RequestCollection($get);
		$this->post = new RequestCollection($post);
		$this->cookies = new RequestCollection($cookies);
		$this->files = new RequestCollection($files);
		$this->env = new RequestCollection($env);

		// Retrieve data from input stream for non-supported request methods
		if ((empty($incomingData)) && (in_array($this->method, [self::METHOD_DELETE, self::METHOD_PATCH, self::METHOD_PUT], true))) {
			parse_str(file_get_contents('php://input'), $incomingData);
		}

		$this->delete = ($this->method === self::METHOD_DELETE) ? new RequestCollection($incomingData) : new RequestCollection();
		$this->patch = ($this->method === self::METHOD_PATCH) ? new RequestCollection($incomingData) : new RequestCollection();
		$this->put = ($this->method === self::METHOD_PUT) ? new RequestCollection($incomingData) : new RequestCollection();
	}

	/**
	 * Determines if this is running in the context of a web request, as opposed to command-line
	 *
	 * @return bool
	 */
	public function isWebRequest(): bool
	{
		return ($this->method !== self::METHOD_NONE);
	}

	/**
	 * Determine if a web request was made over HTTPS
	 *
	 * @return bool|null
	 */
	public function isRequestHttps(): ?bool
	{
		if (!$this->isWebRequest()) {
			return null;
		}

		return (($this->server->has('HTTPS')) && ($this->server->get('HTTPS') !== 'off'));
	}

	/**
	 * Get the requested URL of a web request
	 *
	 * @param bool $includeQueryString
	 * @return string|null
	 */
	public function getRequestedUrl(bool $includeQueryString = true): ?string
	{
		if (!$this->isWebRequest()) {
			return null;
		}

		$url = 'http';
		if ($this->isRequestHttps()) {
			$url .= 's';
		}

		$url .= '://' . $this->server->get('HTTP_HOST');

		if ($includeQueryString) {
			$url .= $this->server->get('REQUEST_URI');
		} else {
			$url .= strtok($this->server->get('REQUEST_URI'), '?');
		}

		return $url;
	}
}