<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\Exception;
use Message\Mothership\Fedex\Api\Document;

/**
 * Response for the `ProcessShipment` request.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ProcessShipment extends AbstractResponse
{
	protected $_trackingCode;
	protected $_labelData;

	/**
	 * {@inheritDoc}
	 *
	 * @throws Exception\ResponseException If no tracking code is returned
	 * @throws Exception\ResponseException If no label data is returned
	 */
	public function validate()
	{
		if (!isset($this->getData()->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber)) {
			$exception = new Exception\ResponseException('No tracking code returned');
			$exception->setResponse($this);

			throw $exception;
		}

		if (!isset($this->getData()->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image)) {
			$exception = new Exception\ResponseException('No label data returned');
			$exception->setResponse($this);

			throw $exception;
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * This sets the tracking code and label data for easy access from outside
	 * of this class.
	 */
	public function init()
	{
		$this->_trackingCode = $this->getData()->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;
		$this->_labelData    = $this->getData()->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
	}

	/**
	 * Get the tracking code set on the response.
	 *
	 * @return string
	 */
	public function getTrackingCode()
	{
		return $this->_trackingCode;
	}

	/**
	 * Get the label data returned in the response.
	 *
	 * @return string
	 */
	public function getLabelData()
	{
		return $this->_labelData;
	}
}