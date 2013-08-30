<?php

namespace Message\Mothership\Fedex\Api;

class PreparedRequest
{
	protected $_apiKey;
	protected $_apiPassword;
	protected $_accountNumber;
	protected $_meterNumber;

	protected $_request;

	protected $_data;

	public function setRequest(Request\RequestInterface $request)
	{
		$this->_request = $request;
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

	public function getData($reload = false)
	{
		if (!$this->_data) {
			if (!$this->_request) {
				throw new \LogicException('API request must be set for a prepared FedEx API request');
			}

			if (!$this->_apiKey || !$this->_apiPassword) {
				throw new \LogicException('API key & password must be set for a prepared FedEx API request');
			}

			if (!$this->_accountNumber) {
				throw new \LogicException('Account number must be set for a prepared FedEx API request');
			}

			if (!$this->_meterNumber) {
				throw new \LogicException('Meter number must be set for a prepared FedEx API request');
			}

			$service     = $this->_request->getService();
			$this->_data = array();

			$this->_data['WebAuthenticationDetail'] = array(
				'UserCredential' => array(
					'Key'      => $this->_apiKey,
					'Password' => $this->_apiPassword,
				)
			);

			$this->_data['ClientDetail'] = array(
				'AccountNumber' => $this->_accountNumber,
				'MeterNumber'   => $this->_meterNumber,
			);

			$this->_data['TransactionDetail'] = array(
				'CustomerTransactionId' => null
			);

			list($major, $intermediate, $minor) = explode('.', $service->getVersion());

			$this->_data['Version'] = array(
				'ServiceId'    => $service->getServiceName(),
				'Major'        => (string) $major,
				'Intermediate' => (string) $intermediate,
				'Minor'        => (int) $minor,
			);

			$this->_data = array_merge($this->_data, $this->_request->getRequestData());
		}

		return $this->_data;
	}
}