<?php

namespace Message\Mothership\Fedex\Api\Request;

use Message\Mothership\Fedex\Api\Service;
use Message\Mothership\Fedex\Api\Response;
use Message\Mothership\Fedex\Api\Exception;
use Message\Mothership\Fedex\Api\Shipment;

/**
 * Process a shipment by sending it to the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ProcessShipment implements RequestInterface
{
	protected $_shipment;

	/**
	 * {@inheritDoc}
	 */
	public function getService()
	{
		return new Service\Ship;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMethod()
	{
		return 'ProcessShipment';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getResponseObject()
	{
		return new Response\ProcessShipment;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Exception\Exception If the shipment has not been set on this request
	 */
	public function validate()
	{
		if (!$this->_shipment) {
			throw new Exception\Exception('Shipment must be defined on ProcessShipment request');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequestData()
	{
		return array(
			'RequestedShipment' => $this->_shipment->getRequestData(),
		);
	}

	/**
	 * Set the shipment to process.
	 *
	 * @param Shipment $shipment
	 */
	public function setShipment(Shipment $shipment)
	{
		$this->_shipment = $shipment;
	}

	/**
	 * Get the shipment that's being processed with this request.
	 *
	 * @return Shipment
	 */
	public function getShipment()
	{
		return $this->_shipment;
	}
}