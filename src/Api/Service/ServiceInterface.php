<?php

namespace Message\Mothership\Fedex\Api\Service;

/**
 * Defines a FedEx API service. Each service has it's own .wsdl file.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ServiceInterface
{
	/**
	 * Get the file path to the WSDL file.
	 *
	 * @return string
	 */
	public function getWsdlPath();

	/**
	 * Get the endpoint URL to use for the WSDL file.
	 *
	 * This is actually already embedded in the WSDL file on most ocassions, but
	 * FedEx require a different endpoint when not using a real production
	 * account
	 *
	 * @param  boolean $testMode True to use test mode, false to use production
	 *
	 * @return string            The endpoint URL to use for this WSDL file
	 */
	public function getWsdlEndpoint($testMode = false);

	/**
	 * Get the version of the service that this WSDL file is for.
	 *
	 * The returned value must be in the format '[major].[intermediate].[minor]',
	 * for example: 1.2.3 would represent major version 1; intermediate version
	 * 2 and minor version 3.
	 *
	 * @return string Version number in the format [major].[intermediate].[minor]
	 */
	public function getVersion();

	/**
	 * Get the service name / ID for this service.
	 *
	 * @return string The service name / ID
	 */
	public function getServiceName();
}