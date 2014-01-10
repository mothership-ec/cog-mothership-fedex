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
	 * Get the name of the method to run on the service returned by
	 * `getService()` for this request.
	 *
	 * @return string
	 */
	public function getMethod();

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

	/**
	 * Get the response object to use for the response to this request.
	 *
	 * @return \Message\Mothership\Fedex\Api\Response\ResponseInterface
	 */
	public function getResponseObject();
}