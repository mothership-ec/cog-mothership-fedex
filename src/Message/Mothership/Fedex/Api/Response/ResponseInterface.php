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
	public function setPreparedRequest(PreparedRequest $request);

	public function setNotifications(Notification\Collection $collection);

	public function setData($data);

	public function getPreparedRequest();

	public function getRequest();

	public function getRequestData();

	public function getNotifications();

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