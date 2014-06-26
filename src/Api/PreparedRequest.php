<?php

namespace Message\Mothership\Fedex\Api;

/**
 * A wrapper for a request that adds generic top-level request data such as the
 * API authentication credentials, account number and so on.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class PreparedRequest
{
	protected $_apiKey;
	protected $_apiPassword;
	protected $_accountNumber;
	protected $_meterNumber;

	protected $_request;

	protected $_data;

	/**
	 * Get the request that this instance is wrapping.
	 *
	 * @return Request\RequestInterface
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * Set the request to wrap.
	 *
	 * @param Request\RequestInterface $request
	 */
	public function setRequest(Request\RequestInterface $request)
	{
		$this->_request = $request;
	}

	/**
	 * Set the API authentication details.
	 *
	 * @param string $key      The API key
	 * @param string $password The API password
	 */
	public function setApiDetails($key, $password)
	{
		$this->_apiKey      = $key;
		$this->_apiPassword = $password;
	}

	/**
	 * Set the account number to send requests as from.
	 *
	 * @param string $number The FedEx account number
	 */
	public function setAccountNumber($number)
	{
		$this->_accountNumber = $number;
	}

	/**
	 * Set the meter number to send requests as from.
	 *
	 * @param string $number The FedEx meter number
	 */
	public function setMeterNumber($number)
	{
		$this->_meterNumber = $number;
	}

	/**
	 * Get the full request data including the top-level generic properties such
	 * as API authentication details.
	 *
	 * The return value of this method is memoized. Passing the `$reload`
	 * property as true will force a refresh of the memoized data.
	 *
	 * @param  boolean $reload True to force a reload of memoized return data
	 *
	 * @return array           The full request data
	 */
	public function getData($reload = false)
	{
		if (!$this->_data || $reload) {
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