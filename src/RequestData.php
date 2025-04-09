<?php

namespace cjrasmussen\Request;

use InvalidArgumentException;

class RequestData
{
	public const METHOD_NONE = '';
	public const METHOD_GET = 'GET';
	public const METHOD_POST = 'POST';
	public const METHOD_PUT = 'PUT';
	public const METHOD_PATCH = 'PATCH';
	public const METHOD_DELETE = 'DELETE';

	private RequestCollection $rawDelete;
	private RequestCollection $rawQuery;
	private RequestCollection $rawPatch;
	private RequestCollection $rawPost;
	private RequestCollection $rawPut;

	public RequestCollection $cookies;
	public RequestCollection $delete;
	public RequestCollection $env;
	public RequestCollection $files;
	public RequestCollection $query;
	public RequestCollection $patch;
	public RequestCollection $post;
	public RequestCollection $put;
	public RequestCollection $server;

	public string $method;
	public int $timestamp;
	public string $requestId;

	protected string $requestIdPrefix = '';

	public function __construct(array $get = [], array $post = [], array $cookies = [], array $files = [], array $server = [], array $env = []) {
		$this->server = new RequestCollection($server);
		$this->requestId = uniqid($this->requestIdPrefix, true);
		$this->method = ($this->server->has('REQUEST_METHOD')) ? $this->server->get('REQUEST_METHOD') : self::METHOD_NONE;
		$this->timestamp = time();

		// pull incoming data out of POST for PUT, PATCH, DELETE
		if (in_array($this->method, [self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_DELETE], true)) {
			$incomingData = $post;
			$post = [];
		} else {
			$incomingData = [];
		}

		$this->rawQuery = new RequestCollection($get);
		$this->rawPost = new RequestCollection($post);
		$this->cookies = new RequestCollection($cookies);
		$this->files = new RequestCollection($files);
		$this->env = new RequestCollection($env);

		// Retrieve data from input stream for non-supported request methods
		if ((empty($incomingData)) && (in_array($this->method, [self::METHOD_DELETE, self::METHOD_PATCH, self::METHOD_PUT], true))) {
			parse_str(file_get_contents('php://input'), $incomingData);
		}

		$this->rawDelete = ($this->method === self::METHOD_DELETE) ? new RequestCollection($incomingData) : new RequestCollection();
		$this->rawPatch = ($this->method === self::METHOD_PATCH) ? new RequestCollection($incomingData) : new RequestCollection();
		$this->rawPut = ($this->method === self::METHOD_PUT) ? new RequestCollection($incomingData) : new RequestCollection();

		$this->sanitizeInput();
	}

	/**
	 * Populate the forward-facing collections with sanitized data
	 */
	private function sanitizeInput(): void
	{
		$this->delete = $this->sanitizeCollection(clone $this->rawDelete);
		$this->query = $this->sanitizeCollection(clone $this->rawQuery);
		$this->patch = $this->sanitizeCollection(clone $this->rawPatch);
		$this->post = $this->sanitizeCollection(clone $this->rawPost);
		$this->put = $this->sanitizeCollection(clone $this->rawPut);
		$this->files = $this->sanitizeUploadedFileCollection($this->files);
	}

	/**
	 * Sanitize a collection's data
	 *
	 * @param RequestCollection|array $input
	 * @return RequestCollection|array
	 */
	private function sanitizeCollection($input)
	{
		foreach ($input AS $key => $value) {
			if ($value !== null) {
				if (is_iterable($value)) {
					$input[$key] = $this->sanitizeCollection($value);
					continue;
				}

				if (is_string($value)) {
					$value = stripslashes(trim($value));
				}

				if (is_numeric($value)) {
					$potential_decimals = substr_count($value, '.');
					if ($potential_decimals === 1) {
						// potential float
						if ($check = filter_var($value, FILTER_VALIDATE_FLOAT)) {
							// passes float check
							$input[$key] = $check;
							continue;
						}
					} elseif ($potential_decimals === 0) {
						// potential int
						if (($value <= PHP_INT_MAX) && ($value >= PHP_INT_MIN)) {
							// in the int range
							$multiplier = 1;

							if (strpos($value, '-') === 0) {
								// negative number
								$multiplier = -1;
								$value = substr($value, 1);
							}

							if ((strpos($value, '0') !== 0) || (strlen($value) === 1)) {
								// does not have leading zeros
								$input[$key] = (int)$value * $multiplier;
								continue;
							}
						}
					}
				}

				if ($value === '') {
					$value = null;
				}

				$input[$key] = $value;
			}
		}

		return $input;
	}

	/**
	 * Sanitize a collection of uploaded file data
	 *
	 * @param RequestCollection $input
	 * @return RequestCollection
	 */
	private function sanitizeUploadedFileCollection(RequestCollection $input): RequestCollection
	{
		$output = [];

		foreach ($input AS $key => $data) {
			if ($data['error'] === UPLOAD_ERR_NO_FILE) {
				// empty input
				continue;
			}

			if (is_array($data['name'])) {
				foreach ($data['name'] AS $index => $value) {
					$output[$key][$index] = new RequestDataUploadedFile($data['name'][$index], $data['type'][$index], $data['tmp_name'][$index], $data['error'][$index], $data['size'][$index]);
				}
			} else {
				$output[$key] = new RequestDataUploadedFile($data['name'], $data['type'], $data['tmp_name'], $data['error'], $data['size']);
			}
		}

		return new RequestCollection($output);
	}

	/**
	 * Get the raw data for a given request method
	 *
	 * @param string $method
	 * @return RequestCollection
	 */
	public function getRawData(string $method): RequestCollection
	{
		if (!in_array($method, [self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_DELETE])) {
			$msg = 'Invalid method "' . $method . '" provided to getRawData.';
			throw new InvalidArgumentException($msg);
		}

		if ($method === self::METHOD_GET) {
			$method = 'query';
		}

		$key = 'raw' . ucfirst(strtolower($method));
		return $this->{$key};
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