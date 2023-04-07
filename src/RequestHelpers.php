<?php
namespace cjrasmussen\Request;

class RequestHelpers
{
	/**
	 * Determines if this is running in the context of a web request, as opposed to command-line
	 *
	 * @return bool
	 */
	public static function isWebRequest(): bool
	{
		return ((PHP_SAPI !== 'cli') && (array_key_exists('REQUEST_URI', $_SERVER)) && ($_SERVER['REQUEST_URI'] !== ''));
	}

	/**
	 * Determine if a web request was made over HTTPS
	 *
	 * @return bool|null
	 */
	public static function isRequestHttps(): ?bool
	{
		if (!self::isWebRequest()) {
			return null;
		}

		return ((!empty($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] !== 'off'));
	}

	/**
	 * Get the requested URL of a web request
	 *
	 * @param bool $includeQueryString
	 * @return string|null
	 */
	public static function getRequestedUrl(bool $includeQueryString = true): ?string
	{
		if (!self::isWebRequest()) {
			return null;
		}

		$url = 'http';
		if (self::isRequestHttps()) {
			$url .= 's';
		}

		$url .= '://' . $_SERVER['HTTP_HOST'];

		if ($includeQueryString) {
			$url .= $_SERVER['REQUEST_URI'];
		} else {
			$url .= strtok($_SERVER['REQUEST_URI'], '?');
		}

		return $url;
	}
}
