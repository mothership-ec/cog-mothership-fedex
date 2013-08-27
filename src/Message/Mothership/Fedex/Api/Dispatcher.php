<?php

namespace Message\Mothership\Fedex\Api;

class Dispatcher
{
	protected $_apiKey;
	protected $_apiPassword;
	protected $_accountNumber;
	protected $_meterNumber;

	protected $_testMode = false;

	protected $_client;

	public function setTestMode($bool = true)
	{
		$this->_testMode = (bool) $bool;
	}

	public function setApiDetails($key, $password)
	{
		$this->_apiKey      = $key;
		$this->_apiPassword = $password;
	}

	public function setAccountNumber($number)
	{
		$this->_accountNumber = $number;
	}

	public function setMeterNumber($number)
	{
		$this->_meterNumber = $number;
	}

	public function isReady()
	{
		return ($this->_apiKey && $this->_apiPassword && $this->_accountNumber && $this->_meterNumber);
	}

	public function dispatch(Request\RequestInterface $request)
	{
		if (!$this->isReady()) {
			throw new \BadMethodCallException('FedEx Dispatcher is not yet ready to dispatch: ensure API details; account number and meter number are defined');
		}

		// TODO: log !
		// TODO: catch soapfaults?

		// Validate the request
		$request->validate();

		// Build the full request data
		$data = $this->buildRequestData($request);

		// Create the SOAP client
		$client = $this->getSoapClient($request->getService());

		// Send the request
		$responseData = $client->{$request->getMethod()}($data);

		// Build response object
		$response = $request->getResponseObject();
		$response->setData($responseData);
		$response->setRequest($request);
		$response->setNotifications(Notification\Collection::loadFromResponse($response));

		// Throw response failure exception if the response has errors
		if ($response->getNotifications()->hasErrors()) {
			throw Exception\ResponseErrorException::createFromResponse($response);
		}

		de($response);

		return $response;
	}

	public function buildRequestData(Request\RequestInterface $request)
	{
		$data = array();

		$data['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
				'Key'      => $this->_apiKey,
				'Password' => $this->_apiPassword,
			)
		);

		$data['ClientDetail'] = array(
			'AccountNumber' => $this->_accountNumber,
			'MeterNumber'   => $this->_meterNumber,
		);

		$data['TransactionDetail'] = array(
			'CustomerTransactionId' => null
		);

		list($major, $intermediate, $minor) = explode('.', $request->getService()->getVersion());

		$data['Version'] = array(
			'ServiceId'    => $request->getService()->getServiceName(),
			'Major'        => (string) $major,
			'Intermediate' => (string) $intermediate,
			'Minor'        => (int) $minor,
		);

		return array_merge($data, $request->getRequestData());
	}

	public function getSoapClient(Service\ServiceInterface $service)
	{
		$client = new \SoapClient($service->getWsdlPath(), array('trace' => 1));

		$client->__setLocation($service->getWsdlEndpoint($this->_testMode));

		return $client;
	}
}