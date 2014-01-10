<?php

namespace Message\Mothership\Fedex\Api\Event;

use Message\Mothership\Fedex\Api\Dispatcher;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent
{
	protected $_dispatcher;

	public function __construct(Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}

	public function getDispatcher()
	{
		return $this->_dispatcher;
	}
}