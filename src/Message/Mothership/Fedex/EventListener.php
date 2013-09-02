<?php

namespace Message\Mothership\Fedex;

use Message\Mothership\Commerce\Order;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

class EventListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inhericDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			Order\Events::DISPATCH_POSTAGE_AUTO => array(
				array('postageFedexExpress'),
			),
		);
	}

	public function postageFedexExpress(Order\Entity\Dispatch\PostageAutomaticallyEvent $event)
	{
		$dispatch = $event->getDispatch();

		// Skip if this is not a FedEx Express dispatch
		if ('fedex-express' !== $dispatch->method->getName()) {
			return false;
		}

		$shipment = $this->get('fedex.api.shipment');
		$shipment->populateFromDispatch($dispatch);

		$request = new Api\Request\ProcessShipment;
		$request->setShipment($shipment);

		$response = $this->get('fedex.api.dispatcher')->dispatch($request);

		// TODO: set code + cost (if defined) + Order documents on the event!

		de($response);

		// if it was all good, do the following:
		$event->stopPropagation();
	}
}