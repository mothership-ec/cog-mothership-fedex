<?php

namespace Message\Mothership\Fedex\Api;

/**
 * Blank class describing the events available in the FedEx API integration.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
final class Events
{
	/**
	 * Occurs before a request is submitted to the API, but after it has been
	 * validated and prepared.
	 */
	const REQUEST  = 'fedex.api.request';

	/**
	 * Occurs after a response to a request is received from the API once it
	 * has been validated and initialised.
	 */
	const RESPONSE = 'fedex.api.response';
}