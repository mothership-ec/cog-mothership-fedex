<?php

namespace Message\Mothership\Fedex\Api\Event;

use Message\Mothership\Fedex\Api\Dispatcher;
use Message\Mothership\Fedex\Api\PreparedRequest;

class RequestEvent extends Event
{
	protected $_request;

	public function __construct(Dispatcher $dispatcher, PreparedRequest $request)
	{
		$this->_dispatcher = $dispatcher;
		$this->_request    = $request;
	}

	public function getRequest()
	{
		return $this->_request;
	}
}