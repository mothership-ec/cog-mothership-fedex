<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\PreparedRequest;
use Message\Mothership\Fedex\Api\Request\RequestInterface;
use Message\Mothership\Fedex\Api\Notification;

/**
 * Defines a response from the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ResponseInterface
{
	/**
	 * Set the prepared request that this response is for.
	 *
	 * @param PreparedRequest $request
	 */
	public function setPreparedRequest(PreparedRequest $request);

	/**
	 * Set the notifications defined in this response.
	 *
	 * @param Notification\Collection $collection
	 */
	public function setNotifications(Notification\Collection $collection);

	/**
	 * Set the response data
	 *
	 * @param mixed $data
	 */
	public function setData($data);

	/**
	 * Get the prepared request that this response is for.
	 *
	 * @return PreparedRequest
	 */
	public function getPreparedRequest();

	/**
	 * Get the request that this response is for.
	 *
	 * @return RequestInterface
	 */
	public function getRequest();

	/**
	 * Get the data for the request that this response is for.
	 *
	 * @return mixed
	 */
	public function getRequestData();

	/**
	 * Get the notifications in this response.
	 *
	 * @return Notification\Collection
	 */
	public function getNotifications();

	/**
	 * Get the response data.
	 *
	 * @return string
	 */
	public function getData();

	/**
	 * Validates the response data.
	 */
	public function validate();

	/**
	 * Initialises the response, manipulating the response data or making it
	 * easier to access.
	 */
	public function init();
}