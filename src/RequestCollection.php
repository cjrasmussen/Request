<?php

namespace cjrasmussen\Request;

use ArrayAccess;
use Countable;
use Iterator;

class RequestCollection implements ArrayAccess, Countable, Iterator
{
	private array $array;

	public function __construct(array $array = array())
	{
		$this->array = $array;
	}

	/**
	 * Get the number of items in the collection
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->array);
	}

	/**
	 * Get all the items in the collection
	 *
	 * @return array
	 */
	public function all(): array
	{
		return $this->array;
	}

	/**
	 * Get the keys of all the items in the collection
	 *
	 * @return array
	 */
	public function keys(): array
	{
		return array_keys($this->array);
	}

	/**
	 * Check to see if the key exists in the collection
	 *
	 * @param $key
	 * @return bool
	 */
	public function has($key): bool
	{
		return array_key_exists($key, $this->array);
	}

	/**
	 * @param string|int $key
	 *
	 * @return mixed|null
	 */
	public function get($key)
	{
		return $this->array[$key] ?? null;
	}

	/**
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->count() === 0;
	}

	/**
	 * Run a filter over each of the items.
	 *
	 * @param  callable|null $callback
	 *
	 * @return self
	 */
	public function filter(callable $callback = null): self
	{
		if ($callback) {
			$return = array();

			foreach ($this->array as $key => $value) {
				if ($callback($value, $key)) {
					$return[$key] = $value;
				}
			}

			return new static($return);
		}

		return new static(array_filter($this->array));
	}

	public function current()
	{
		return current($this->array);
	}

	public function next(): void
	{
		next($this->array);
	}

	public function key()
	{
		return key($this->array);
	}

	public function valid(): bool
	{
		return key($this->array) !== null;
	}

	public function rewind(): void
	{
		reset($this->array);
	}

	public function offsetExists($offset): bool
	{
		return isset($this->array[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->array[$offset] ?? null;
	}

	public function offsetSet($offset, $value)
	{
		if ($offset === null) {
			$this->array[] = $value;
		} else {
			$this->array[$offset] = $value;
		}
	}

	public function offsetUnset($offset)
	{
		unset($this->array[$offset]);
	}
}