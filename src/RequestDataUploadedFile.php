<?php

namespace cjrasmussen\Request;

class RequestDataUploadedFile
{
	public string $name;
	public string $type;
	public string $tmpName;
	public int $error;
	public int $size;

	public function __construct(string $name, string $type, string $tmpName, int $error, int $size)
	{
		$this->name = $name;
		$this->type = $type;
		$this->tmpName = $tmpName;
		$this->error = $error;
		$this->size = $size;
	}
}