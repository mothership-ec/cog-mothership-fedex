<?php

namespace Message\Mothership\Fedex\Api\Notification;

/**
 * Model representing a FedEx notification in one of their API responses.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Notification
{
	public $severity;
	public $source;
	public $code;
	public $message;

	/**
	 * Get the possible notification severities in order of severity.
	 *
	 * @return array[string]
	 */
	static public function getSeverities()
	{
		return array(
			'SUCCESS',
			'NOTE',
			'WARNING',
			'ERROR',
			'FAILURE',
		);
	}

	/**
	 * Check whether a given notification has a higher severity than this
	 * notification object.
	 *
	 * @param  Notification $notification The notification to compare against
	 *
	 * @return boolean                    True if the given notification has a
	 *                                    higher severity, false otherwise
	 */
	public function hasHigherSeverity(Notification $notification)
	{
		$selfSeverityKey   = array_search($this->severity, self::getSeverities());
		$targetSeverityKey = array_search($notification->severity, self::getSeverities());

		return ($selfSeverityKey > $targetSeverityKey);
	}
}