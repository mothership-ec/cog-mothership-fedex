<?php

namespace Message\Mothership\Fedex\Api\Event;

use Message\Mothership\Fedex\Api\Dispatcher;
use Message\Mothership\Fedex\Api\Response\ResponseInterface;

class ResponseEvent extends Event
{
	protected $_response;

	public function __construct(Dispatcher $dispatcher, ResponseInterface $response)
	{
		$this->_dispatcher = $dispatcher;
		$this->_response    = $response;
	}

	public function getResponse()
	{
		return $this->_response;
	}
}