<?php

namespace Message\Mothership\Fedex\Api\Notification;

use Message\Mothership\Fedex\Api\Response\ResponseInterface;

/**
 * Collection class for FedEx notifications.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_notifications = array();

	/**
	 * Factory method to create a collection of notification from a FedEx API
	 * response.
	 *
	 * @param  ResponseInterface $response The response to get notifications from
	 *
	 * @return Collection
	 */
	static public function loadFromResponse(ResponseInterface $response)
	{
		$collection = new self;

		if (!isset($response->getData()->Notifications)) {
			return $collection;
		}

		$notifications = $response->getData()->Notifications;

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

	/**
	 * Add a notification to this collection.
	 *
	 * @param Notification $notification
	 */
	public function add(Notification $notification)
	{
		$this->_notifications[] = $notification;
	}

	/**
	 * Get the number of notifications in this collection.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_notifications);
	}

	/**
	 * Check whether any of the notifications have a severity of "ERROR" or
	 * higher ("FAILURE").
	 *
	 * @return boolean Result of the check
	 */
	public function hasErrors()
	{
		return in_array($this->getHighestSeverity(), array('ERROR', 'FAILURE'));
	}

	/**
	 * Get the most severe notification in this collection.
	 *
	 * @return string|null The highest level of severity in this collection
	 */
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

	/**
	 * Get notifications set on this collection that match a certain severity
	 * identifier.
	 *
	 * @param  string|array $severity The severity/severities to get
	 *                                notifications from
	 *
	 * @return array[Notification]    Notifications on this collection with the
	 *                                defined severity
	 */
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

	/**
	 * Get the iterator to use for iterating over this class.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_notifications);
	}
}