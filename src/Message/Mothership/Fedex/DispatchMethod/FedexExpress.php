<?php

namespace Message\Mothership\Fedex\DispatchMethod;

use Message\Mothership\Commerce\Order\Entity\Dispatch\MethodInterface;

/**
 * FedEx Express dispatch method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FedexExpress implements MethodInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'fedex-express';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName()
	{
		return 'Fedex Express';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTrackingLink($code)
	{
		return 'http://fedex.com/Tracking?tracknumbers=' . $code;
	}
}