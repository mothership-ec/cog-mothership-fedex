<?php

namespace Message\Mothership\Fedex\Api\Service;

/**
 * "Upload Document" service.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class UploadDocument implements ServiceInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getWsdlPath()
	{
		return 'cog://@Message:Mothership:Fedex::resources/wsdl/UploadDocument/1.1.0.wsdl';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getWsdlEndpoint($testMode = false)
	{
		return $testMode
			? 'https://wsbeta.fedex.com:443/web-services/uploaddocument'
			: 'https://ws.fedex.com:443/web-services/uploaddocument';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getVersion()
	{
		return '1.1.0';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getServiceName()
	{
		return 'cdus';
	}
}