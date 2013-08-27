<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\Request\RequestInterface;
use Message\Mothership\Fedex\Api\Notification;

/**
 * Defines a response from the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ResponseInterface
{
	public function setRequest(RequestInterface $request);

	public function setData($data);

	public function setNotifications(Notification\Collection $collection);

	public function getRequest();

	public function getData();

	public function getNotifications();

	public function validate();

	public function init();
}