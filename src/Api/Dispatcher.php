<?php

namespace Message\Mothership\Fedex\Api;

use Message\Cog\Event\Dispatcher as EventDispatcher;

/**
 * FedEx API request dispatcher.
 *
 * Responsible for configuring the SOAP client, sending the requests and
 * returning the responses.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Dispatcher
{
	protected $_preparedRequest;
	protected $_eventDispatcher;

	protected $_testMode = false;

	/**
	 * Constructor
	 *
	 * @param PreparedRequest $preparedRequest Prototype for a prepared request
	 *                                         that will be cloned for each
	 *                                         request
	 * @param EventDispatcher $eventDispatcher The event dispatcher
	 */
	public function __construct(PreparedRequest $preparedRequest, EventDispatcher $eventDispatcher)
	{
		$this->_preparedRequest = $preparedRequest;
		$this->_eventDispatcher = $eventDispatcher;
	}

	/**
	 * Enable or disable test mode.
	 *
	 * @param boolean $bool True to enable, false to disable
	 */
	public function setTestMode($bool = true)
	{
		$this->_testMode = (bool) $bool;
	}

	/**
	 * Dispatch a request and return the response
	 *
	 * @param  Request\RequestInterface $request The request to dispatch
	 *
	 * @return Response\ResponseInterface        The response to the request
	 *
	 * @throws \SoapFault if a soap fault occurs
	 * @throws Exception\ResponseErrorException If an error-level notification
	 *                                          is returned
	 */
	public function dispatch(Request\RequestInterface $request)
	{
		// TODO: log !
		// TODO: catch soapfaults?

		// Validate the request
		$request->validate();

		// Prepare the request
		$preparedRequest = clone $this->_preparedRequest;
		$preparedRequest->setRequest($request);

		// Dispatch the "API Request" event
		$this->_eventDispatcher->dispatch(
			Events::REQUEST,
			new Event\RequestEvent($this, $preparedRequest)
		);

		// Create the SOAP client
		$client = $this->getSoapClient($request->getService());

		// Send the request
		try {
			$responseData = $client->{$request->getMethod()}($preparedRequest->getData());
		}
		catch (\SoapFault $e) {
			throw $e;
			// TODO: Do stuff with this
		}

		// Build response object
		$response = $request->getResponseObject();
		$response->setData($responseData);
		$response->setPreparedRequest($preparedRequest);
		$response->setNotifications(Notification\Collection::loadFromResponse($response));

		// Throw response failure exception if the response has errors
		if ($response->getNotifications()->hasErrors()) {
			throw Exception\ResponseErrorException::createFromResponse($response);
		}

		$response->validate();
		$response->init();

		// Dispatch the "API Response" event
		$this->_eventDispatcher->dispatch(
			Events::RESPONSE,
			new Event\ResponseEvent($this, $response)
		);

		return $response;
	}

	/**
	 * Get the soap client for a given service.
	 *
	 * @param  Service\ServiceInterface $service
	 *
	 * @return \SoapClient
	 */
	public function getSoapClient(Service\ServiceInterface $service)
	{
		$options = array();

		if ($this->_testMode) {
			$options['trace'] = 1;
		}

		$client = new \SoapClient($service->getWsdlPath(), $options);

		$client->__setLocation($service->getWsdlEndpoint($this->_testMode));

		return $client;
	}
}