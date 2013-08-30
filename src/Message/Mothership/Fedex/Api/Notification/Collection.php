<?php

namespace Message\Mothership\Fedex\Api\Notification;

use Message\Mothership\Fedex\Api\Response\ResponseInterface;

class Collection implements \IteratorAggregate, \Countable
{
	protected $_notifications = array();

	static public function loadFromResponse(ResponseInterface $response)
	{
		$collection = new self;

		if (!isset($response->getRequestData()->Notifications)) {
			return $collection;
		}

		$notifications = $response->getRequestData()->Notifications;

		// If there's only one notification, it's never in an array :-(
		if (is_object($notifications)) {
			$notifications = array($notifications);
		}

		foreach ($notifications as $notificationData) {
			$notification = new Notification;

			$notification->severity = $notificationData->Severity;
			$notification->source   = $notificationData->Source;
			$notification->code     = $notificationData->Code;
			$notification->message  = $notificationData->Message;

			$collection->add($notification);
		}

		return $collection;
	}

	public function add(Notification $notification)
	{
		$this->_notifications[] = $notification;
	}

	public function count()
	{
		return count($this->_notifications);
	}

	public function hasErrors()
	{
		return in_array($this->getHighestSeverity(), array('ERROR', 'FAILURE'));
	}

	public function getHighestSeverity()
	{
		$mostSevere = null;
		$severities = Notification::getSeverities();

		foreach ($this->_notifications as $notification) {
			if (is_null($mostSevere) || $notification->hasHigherSeverity($mostSevere)) {
				$mostSevere = $notification;
			}
		}

		return $mostSevere ? $mostSevere->severity : null;
	}

	public function getBySeverity($severity)
	{
		$return = array();

		if (!is_array($severity)) {
			$severity = array($severity);
		}

		foreach ($this->_notifications as $notification) {
			if (in_array($notification->severity, $severity)) {
				$return[] = $notification;
			}
		}

		return $return;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_notifications);
	}
}