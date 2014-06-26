<?php

namespace Message\Mothership\Fedex\Api\Event;

use Message\Mothership\Fedex\Api\Dispatcher;
use Message\Mothership\Fedex\Api\PreparedRequest;

/**
 * FedEx API event for requests.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RequestEvent extends Event
{
	protected $_preparedRequest;

	/**
	 * Constructor.
	 *
	 * @param Dispatcher      $dispatcher The dispatcher for this event
	 * @param PreparedRequest $request    The prepared request for this event
	 */
	public function __construct(Dispatcher $dispatcher, PreparedRequest $request)
	{
		$this->_dispatcher      = $dispatcher;
		$this->_preparedRequest = $request;
	}

	/**
	 * Get the prepared request for this event.
	 *
	 * @return PreparedRequest
	 */
	public function getPreparedRequest()
	{
		return $this->_preparedRequest;
	}

	/**
	 * Get the request from the prepared request for this event
	 *
	 * @return \Message\Mothership\Fedex\Api\Request\RequestInterface
	 */
	public function getRequest()
	{
		return $this->_preparedRequest->getRequest();
	}
}