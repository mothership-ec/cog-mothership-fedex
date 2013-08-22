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