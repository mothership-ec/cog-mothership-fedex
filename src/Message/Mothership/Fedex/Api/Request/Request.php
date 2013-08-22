<?php

namespace Message\Mothership\Fedex\Request;

abstract class Request
{

	protected $_wsdlName;
	protected $_serviceID;

	protected $_client;
	protected $_request;

	public function send()
	{
		if (!$this->_wsdlName || !file_exists($this->_getWsdlPath())) {
			throw new \Exception('WSDL file not set or not found: ' . $this->_wsdlName, Exception::WSDL_NOT_SET);
		}
		if (!$this->_serviceID) {
			throw new \Exception('Service ID not set', Exception::SERVICE_ID_NOT_SET);
		}
		if (!$this->_validate()) {
			throw new \Exception('Request failed to validate and could not be sent.', Exception::REQUEST_INVALID);
		}
		$this->_build();
		$this->_init();
		$response = $this->_client->{$this->_getMethodName()}($this->_request);
		$responseClass = '\Fedex\Response\\' . $this->_getMethodName();
		return new $responseClass($response, $this);
	}

	/**
	 * Initiates SoapClient connection.
	 */
	protected function _init()
	{
		if ($this->_client instanceof \SoapClient) {
			return true;
		}
		$this->_client = new \SoapClient($this->_getWsdlPath(), array('trace' => 1));
		if (!$this->_client instanceof \SoapClient) {
			throw new \Exception('Could not connect to Fedex SOAP client.', \Exception::SOAP_CONNECT_FAIL);
		}
	}

	protected function _build()
	{
		$version = $this->_getWsdlVersion();
		$this->_request = array(
			'WebAuthenticationDetail' => array(
				'UserCredential' => array(
					'Key'      => \Config::get('fedex')->apiKey,
					'Password' => \Config::get('fedex')->apiPassword
				)
			),
			'ClientDetail' => array(
				'AccountNumber' => \Config::get('fedex')->accountNumber,
				'MeterNumber'   => \Config::get('fedex')->meterNumber
			),
			'TransactionDetail' => array(
				'CustomerTransactionId' => null
			),
			'Version' => array(
				'ServiceId'    => $this->_serviceID,
				'Major'        => $version[0],
				'Intermediate' => $version[1],
				'Minor'        => $version[2]
			)
		);
		$this->_request = array_merge($this->_request, $this->_getRequestFields());
		$this->_buildRequest();
	}

	/**
	 * Returns the name of the class with the base class
	 * removed and the first character lowercased.
	 * E.g. FedexProcessShipment becomes processShipment
	 *
	 * @return string Fedex method name for SOAP client
	 */
	protected function _getMethodName()
	{
		return substr(get_class($this), strlen(__CLASS__) + 1); // + 1 to remove preceeding namespace slash
	}

	protected function _getWsdlPath()
	{
		return SYSTEM_PATH . 'library/wsdl/Fedex/' . $this->_wsdlName . '.wsdl' . (ENVIRONMENT === 'dev' ? '.test' : '');
	}

	/**
	 * Returns 3-part version number for the WSDL file based on the
	 * filename. All compatible WSDL files should end with _v[NUMBER]
	 * separated with decimals. It is not necessary to include all
	 * 3 parts. This method pads the result to 3.
	 *
	 * @return array The 3 version parts
	 */
	protected function _getWsdlVersion()
	{
		if (preg_match('/_v([0-9\.]+)$/', $this->_wsdlName, $matches) !== 1) {
			throw new \Exception('Invalid version number for WSDL: ' . $this->_wsdlName);
		}
		return array_pad(explode('.', $matches[1]), 3, 0);
	}

	abstract protected function _validate();

	/**
	 * Get the request fields for the specific request.
	 *
	 * @return array request fields for SOAP call
	 */
	abstract protected function _getRequestFields();

	abstract protected function _buildRequest();

}