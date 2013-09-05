<?php

namespace Message\Mothership\Fedex\EventListener;

use Message\Mothership\Fedex\Api;

use Message\Mothership\Commerce\Order;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for registering FedEx functionality for the Orders subsystem
 * in the Mothership\Commerce module.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class OrderListener extends BaseListener implements SubscriberInterface
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

		$event->setCode($response->getTrackingCode());

		// Create file for label data
		$path = 'cog://data/order/dispatch-label/' . $event->getDispatch()->order->id . '-' . $event->getDispatch()->id;

		if ($this->get('filesystem')->exists($path)) {
			throw new \LogicException(sprintf(
				'Label file already exists for order #%s, dispatch #%s',
				$event->getDispatch()->order->id,
				$event->getDispatch()->id
			);
		}

		// Stupid workaround because file_put_contents doesn't freakin' work with streams
		$handler = $this->get('filesystem.stream_wrapper_manager')::getHandler('cog');
		$path    = $handler->getLocalPath($path);

		$this->_container['filesystem']->dumpFile($path, $contents);


		// TODO: set code + cost (if defined) + Order documents on the event!

		de($response);

		// if it was all good, do the following:
		$event->stopPropagation();
	}
}