<?php

namespace Message\Mothership\Fedex\Api\Exception;

use Message\Mothership\Fedex\Api\Response\ResponseInterface;

/**
 * Exception relating to a response from the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ResponseException extends Exception
{
	protected $_response;

	/**
	 * Set the response for this exception.
	 *
	 * @param ResponseInterface $response
	 */
	public function setResponse(ResponseInterface $response)
	{
		$this->_response = $response;
	}

	/**
	 * Get the response for this exception
	 *
	 * @return ResponseInterface
	 */
	public function getResponse()
	{
		return $this->_response;
	}
}