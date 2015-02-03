<?php

namespace Message\Mothership\Fedex\Bootstrap;

use Message\Mothership\Fedex;

use Message\Cog\Bootstrap\EventsInterface;

/**
 * FedEx events bootstrap.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Events implements EventsInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new Fedex\EventListener\OrderListener);
	}
}