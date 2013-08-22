<?php

namespace Message\Mothership\Fedex\Api\Request;

/**
 * Defines a request to the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface RequestInterface
{
	/**
	 * Get the service to use when sending this request.
	 *
	 * @return Service\ServiceInterface
	 */
	public function getService();

	/**
	 * Validate this request.
	 *
	 * This method should throw the appropriate exceptions when necessary. It
	 * needn't return anything.
	 */
	public function validate();

	/**
	 * Get the data to send to the FedEx API for this request.
	 *
	 * @return array
	 */
	public function getRequestData();
}