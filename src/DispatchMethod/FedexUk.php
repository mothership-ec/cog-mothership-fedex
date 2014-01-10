<?php

namespace Message\Mothership\Fedex\DispatchMethod;

use Message\Mothership\Commerce\Order\Entity\Dispatch\MethodInterface;

/**
 * FedEx UK dispatch method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FedexUk implements MethodInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'fedex-uk';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName()
	{
		return 'Fedex UK';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTrackingLink($code)
	{
		return 'http://www.fedexuk.net/accounts/QuickTrack.aspx?consignment=' . $code;
	}

	/**
	 * {@inheritDoc}
	 */
	public function allowAutomaticPostage()
	{
		return false;
	}
}