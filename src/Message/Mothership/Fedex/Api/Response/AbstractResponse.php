<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\Request\RequestInterface;
use Message\Mothership\Fedex\Api\Notification;

abstract class AbstractResponse implements ResponseInterface
{
	protected $_request;
	protected $_data;
	protected $_notifications;

	public function setRequest(RequestInterface $request)
	{
		$this->_request = $request;
	}

	public function setData($data)
	{
		$this->_data = $data;
	}

	public function setNotifications(Notification\Collection $collection)
	{
		$this->_notifications = $collection;
	}

	public function getRequest()
	{
		return $this->_request;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function getNotifications()
	{
		return $this->_notifications;
	}
}