<?php

namespace Message\Mothership\Fedex\Api\Notification;

class Notification
{
	public $severity;
	public $source;
	public $code;
	public $message;

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

	public function hasHigherSeverity(Notification $notification)
	{
		$selfSeverityKey   = array_search($this->severity, self::getSeverities());
		$targetSeverityKey = array_search($notification->severity, self::getSeverities());

		return ($selfSeverityKey > $targetSeverityKey);
	}
}