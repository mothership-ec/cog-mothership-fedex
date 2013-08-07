<?php

namespace Message\Mothership\Fedex\Method;

use Message\Mothership\Commerce\Order\Entity\Dispatch\MethodInterface;

class FedexUkMethod implements MethodInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'fedexuk';
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

	}
}