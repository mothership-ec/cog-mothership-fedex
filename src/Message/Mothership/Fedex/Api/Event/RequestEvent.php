<?php

namespace Message\Mothership\Fedex\Api\Event;

use Message\Mothership\Fedex\Api\Dispatcher;
use Message\Mothership\Fedex\Api\PreparedRequest;

class RequestEvent extends Event
{
	protected $_preparedRequest;

	public function __construct(Dispatcher $dispatcher, PreparedRequest $request)
	{
		$this->_dispatcher      = $dispatcher;
		$this->_preparedRequest = $request;
	}

	public function getPreparedRequest()
	{
		return $this->_preparedRequest;
	}

	public function getRequest()
	{
		return $this->_preparedRequest->getRequest();
	}
}