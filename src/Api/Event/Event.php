<?php

namespace Message\Mothership\Fedex\Api\Event;

use Message\Mothership\Fedex\Api\Dispatcher;

use Message\Cog\Event\Event as BaseEvent;

/**
 * Base event for FedEx API subsystem.
 *
 * A dispatcher must be set on the event.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Event extends BaseEvent
{
	protected $_dispatcher;

	/**
	 * Constructor.
	 *
	 * @param Dispatcher $dispatcher The API dispatcher relevant to this event
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}

	/**
	 * Get the API dispatcher set on this event.
	 *
	 * @return Dispatcher
	 */
	public function getDispatcher()
	{
		return $this->_dispatcher;
	}
}