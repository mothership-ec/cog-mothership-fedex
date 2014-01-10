<?php

namespace Message\Mothership\Fedex\Api\Service;

/**
 * "Ship" service.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Ship implements ServiceInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getWsdlPath()
	{
		return 'cog://@Message:Mothership:Fedex::resources/wsdl/Ship/13.0.0.wsdl';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getWsdlEndpoint($testMode = false)
	{
		return $testMode
			? 'https://wsbeta.fedex.com:443/web-services/ship'
			: 'https://ws.fedex.com:443/web-services/ship';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion()
	{
		return '13.0.0';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getServiceName()
	{
		return 'ship';
	}
}