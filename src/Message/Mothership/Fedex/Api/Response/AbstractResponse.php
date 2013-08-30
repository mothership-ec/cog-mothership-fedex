<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\PreparedRequest;
use Message\Mothership\Fedex\Api\Request\RequestInterface;
use Message\Mothership\Fedex\Api\Notification;

abstract class AbstractResponse implements ResponseInterface
{
	protected $_request;
	protected $_notifications;

	public function setPreparedRequest(PreparedRequest $request)
	{
		$this->_request = $request;
	}

	public function setNotifications(Notification\Collection $collection)
	{
		$this->_notifications = $collection;
	}

	public function getPreparedRequest()
	{
		return $this->_request;
	}

	public function getRequest()
	{
		return $this->_request->getRequest();
	}

	public function getRequestData()
	{
		return $this->_request->getData();
	}

	public function getNotifications()
	{
		return $this->_notifications;
	}
}