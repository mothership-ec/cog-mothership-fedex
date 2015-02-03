<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\PreparedRequest;
use Message\Mothership\Fedex\Api\Request\RequestInterface;
use Message\Mothership\Fedex\Api\Notification;

/**
 * Abstract response class that has basic implementation of most of the methods
 * defined on the `ResponseInterface` that need not be duplicated in each
 * concretion.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class AbstractResponse implements ResponseInterface
{
	protected $_request;
	protected $_data;
	protected $_notifications;

	/**
	 * {@inheritDoc}
	 */
	public function setPreparedRequest(PreparedRequest $request)
	{
		$this->_request = $request;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setNotifications(Notification\Collection $collection)
	{
		$this->_notifications = $collection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setData($data)
	{
		$this->_data = $data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPreparedRequest()
	{
		return $this->_request;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequest()
	{
		return $this->_request->getRequest();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequestData()
	{
		return $this->_request->getData();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getNotifications()
	{
		return $this->_notifications;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData()
	{
		return $this->_data;
	}
}