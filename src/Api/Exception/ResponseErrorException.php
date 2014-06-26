<?php

namespace Message\Mothership\Fedex\Api\Exception;

use Message\Mothership\Fedex\Api\Response\ResponseInterface;

/**
 * Exception class for response errors.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ResponseErrorException extends ResponseException
{
	/**
	 * Factory method to create an instance of this class from a FedEx response
	 * object.
	 *
	 * @param  ResponseInterface $response The response object to get errors from
	 *
	 * @return ResponseErrorException
	 */
	static public function createFromResponse(ResponseInterface $response)
	{
		$messages = array();

		foreach ($response->getNotifications()->getBySeverity(array('FAILURE', 'ERROR')) as $n) {
			$messages[] = sprintf('%s: (%s) %s', $n->severity, $n->code, $n->message);
		}

		$exception = new self(sprintf('FedEx API Request Failure: %s', implode(', ', $messages)));

		$exception->setResponse($response);

		return $exception;
	}
}