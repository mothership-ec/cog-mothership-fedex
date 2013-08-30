<?php

namespace Message\Mothership\Fedex\Api\Exception;

use Message\Mothership\Fedex\Api\Response\ResponseInterface;

class ResponseException extends Exception
{
	protected $_response;

	public function setResponse(ResponseInterface $response)
	{
		$this->_response = $response;
	}

	public function getResponse()
	{
		return $this->_response;
	}
}