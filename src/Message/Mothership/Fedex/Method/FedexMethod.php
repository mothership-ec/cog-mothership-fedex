<?php

namespace Message\Mothership\Fedex\Method;

use Message\Mothership\Commerce\Order\Entity\Dispatch\MethodInterface;

class FedexMethod implements MethodInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'fedex';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName()
	{
		return 'Fedex';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTrackingLink($code)
	{

	}
}