<?php

namespace Message\Mothership\Fedex\EventListener;

use Message\Mothership\Fedex\Api;

use Message\Mothership\Commerce\Order;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Filesystem\File;

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

	/**
	 * Postage dispatches with a type of 'fedex-express'.
	 *
	 * The tracking code is set as the dispatch code, and the label data is
	 * saved as a document relating to the order and the dispatch.
	 *
	 * @param  Order\Entity\Dispatch\PostageAutomaticallyEvent $event
	 *
	 * @return false If the dispatch is not applicable for FedEx Express postaging
	 */
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
		$path = 'cog://data/order/dispatch-label/'
			. $event->getDispatch()->order->id
			. '-'
			. $event->getDispatch()->id
			. '.'
			. $shipment->getLabelFileExtension();

		if ($this->get('filesystem')->exists($path)) {
			throw new \LogicException(sprintf(
				'Label file already exists for order #%s, dispatch #%s',
				$event->getDispatch()->order->id,
				$event->getDispatch()->id
			));
		}

		// Stupid workaround because file_put_contents doesn't freakin' work with streams
		$manager = $this->get('filesystem.stream_wrapper_manager');
		$handler = $manager::getHandler('cog');
		$cogPath = $path;
		$path    = $handler->getLocalPath($path);

		$this->get('filesystem')->dumpFile($path, $response->getLabelData());

		$document       = new Order\Entity\Document\Document;
		$document->type = 'dispatch-label';

		// Create the file from the cog path to ensure it is stored with that reference
		$document->file = new File($cogPath);

		$event->addDocument($document);
	}
}