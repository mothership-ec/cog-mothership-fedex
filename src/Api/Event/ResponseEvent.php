<?php

namespace Message\Mothership\Fedex\Api\Event;

use Message\Mothership\Fedex\Api\Dispatcher;
use Message\Mothership\Fedex\Api\Response\ResponseInterface;

/**
 * FedEx API event for responses.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ResponseEvent extends Event
{
	protected $_response;

	/**
	 * Constructor.
	 *
	 * @param Dispatcher        $dispatcher The dispatcher for this event
	 * @param ResponseInterface $response   The response for this event
	 */
	public function __construct(Dispatcher $dispatcher, ResponseInterface $response)
	{
		$this->_dispatcher = $dispatcher;
		$this->_response   = $response;
	}

	/**
	 * Get the response for this event.
	 *
	 * @return ResponseInterface
	 */
	public function getResponse()
	{
		return $this->_response;
	}
}