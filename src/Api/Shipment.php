<?php

namespace Message\Mothership\Fedex\Api;

use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Order\Entity\Dispatch\Dispatch;

use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Represents a FedEx shipment.
 *
 * Currently, this model only supports one package per shipment and not
 * multi-package shipments.
 *
 * This model also only supports generated Commercial Invoices, and no other
 * document types.
 *
 * Custom documents can also be assigned to shipments.
 *
 * @todo Add a way to validate shipments... do this within the request? or no?
 * @todo Perhaps move the functionality in getRequestData() to another class.. ShipmentTransformer?
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Shipment
{
	protected $_shipAt;
	protected $_serviceType;

	protected $_shipperAddress;
	protected $_shipperCompany;
	protected $_shipperPerson;

	protected $_recipientAddress;
	protected $_recipientCompany;
	protected $_recipientPerson;

	protected $_transportationPaymentType;
	protected $_transportationPayorAccountNumber;
	protected $_transportationPayorAddress;
	protected $_transportationPayorPersonName;
	protected $_transportationPayorCompanyName;
	protected $_companyCurrency;
	protected $_purpose;
	protected $_customsOptionType;

	protected $_dutiesPaymentType;

	protected $_currencyID;

	protected $_commodities = array();

	protected $_documents = array();

	protected $_specialServices;

	protected $_requestGeneratedCommercialInvoice = false;

	protected $_termsOfSale;

	protected $_tin;
	protected $_labelSpec = array(
		'format'    => 'COMMON2D',
		'imgType'   => 'PDF',
		'stockType' => null,
	);

	protected $_dispatch;

	/**
	 * Constructor.
	 */
	public function __construct($currency)
	{
		$this->_shipAt = new DateTimeImmutable;
		$this->_companyCurrency = $currency;
	}

	/**
	 * Populate this shipment from an order dispatch.
	 *
	 * The delivery address and recipient details are set from the order, and
	 * the commodities are set from the items in the dispatch.
	 *
	 * @param  Dispatch $dispatch The dispatch to populate from
	 *
	 * @return Shipment           Implements a fluent interface
	 */
	public function populateFromDispatch(Dispatch $dispatch)
	{
		$this->setCurrencyID($dispatch->order->currencyID);

		$deliveryAddress = $dispatch->order->addresses->getByType('delivery');

		$this->setRecipient(
			$deliveryAddress,
			$deliveryAddress->getName()
		);

		foreach ($dispatch->items->getRows() as $row) {
			$commodity = new Commodity;
			$commodity->populateFromItemRow($row);

			$this->addCommodity($commodity);
		}

		$this->_dispatch = $dispatch;

		return $this;
	}

	/**
	 * Get the dispatch set for this shipment. This is only set when the
	 * shipment was set up using `populateFromDispatch()`.
	 *
	 * @return Dispatch|null
	 */
	public function getDispatch()
	{
		return $this->_dispatch;
	}

	/**
	 * Get the commodities set on this shipment.
	 *
	 * @return array[Commodity]
	 */
	public function getCommodities()
	{
		return $this->_commodities;
	}

	/**
	 * Set the service type for this shipment.
	 *
	 * @param string $type Service type identifier as defined in FedEx API docs
	 */
	public function setServiceType($type)
	{
		$this->_serviceType = $type;
	}

	/**
	 * Set tax identification number.
	 *
	 * @param string $number The tax identification number
	 * @param string $type   The TIN type identifier as defined in the FedEx API
	 *                       docs
	 */
	public function setTin($number, $type = 'BUSINESS_NATIONAL')
	{
		$this->_tin = array(
			'number' => $number,
			'type'   => $type,
		);
	}

	/**
	 * Set the terms of sale for this shipment (DDU or DDP).
	 *
	 * @param string $terms The terms of sale (DDU or DDP)
	 */
	public function setTermsOfSale($terms)
	{
		$this->_termsOfSale = $terms;
	}

	/**
	 * Set the specification for the returned shipment label.
	 *
	 * @param string $format    The format for the label (see FedEx API docs)
	 * @param string $imgType   The image type for the label (see FedEx API docs)
	 * @param string $stockType The type of stock used for printing the label
	 *                          (see FedEx API docs)
	 */
	public function setLabelSpec($format, $imgType = null, $stockType = null)
	{
		$this->_labelSpec['format']    = $format;
		$this->_labelSpec['imgType']   = $imgType;
		$this->_labelSpec['stockType'] = $stockType;
	}

	/**
	 * Set the transportation payment details (who/what pays for the shipment's
	 * transportation).
	 *
	 * @param string  $type          Transportation payment type (eg. SHIPPER)
	 * @param string  $accountNumber Account number for account who is paying
	 * @param string  $personName    Name of the person who is paying
	 * @param string  $companyName   Company name for who is paying
	 * @param Address $address       Address for who is paying
	 */
	public function setTransportationPayment($type, $accountNumber, $personName, $companyName = null, Address $address = null)
	{
		$this->_transportationPaymentType        = $type;
		$this->_transportationPayorAccountNumber = $accountNumber;
		$this->_transportationPayorAddress       = $address;
		$this->_transportationPayorPersonName    = $personName;
		$this->_transportationPayorCompanyName   = $companyName;
	}

	/**
	 * Set the party responsible for paying for any duties.
	 *
	 * @param string $type Value from the FedEx API (SENDER, RECIPIENT etc)
	 */
	public function setDutiesPaymentType($type)
	{
		$this->_dutiesPaymentType = $type;
	}

	/**
	 * Set the purpose for the shipment.
	 *
	 * Allowed values are as follows:
	 *
	 *  - GIFT
	 *  - NOT_SOLD
	 *  - PERSONAL_EFFECTS
	 *  - REPAIR_AND_RETURN
	 *  - SAMPLE
	 *  - SOLD
	 *
	 * @param string $purpose Purpose identifer (see FedEx API docs)
	 */
	public function setPurpose($purpose)
	{
		$allowed = array(
			'GIFT',
			'NOT_SOLD',
			'PERSONAL_EFFECTS',
			'REPAIR_AND_RETURN',
			'SAMPLE',
			'SOLD',
		);

		if (!in_array($purpose, $allowed)) {
			throw new \InvalidArgumentException(sprintf(
				'Invalid shipment purpose: `%s`. Allowed values: `%s`',
				$purpose,
				implode('`, `', $allowed)
			));
		}

		$this->_purpose = $purpose;
	}

	/**
	 * Set the customs option type.
	 *
	 * Allowed values are as follows:
	 *
	 * - COURTESY_RETURN_LABEL
	 * - EXHIBITION_TRADE_SHOW
	 * - FAULTY_ITEM
	 * - FOLLOWING_REPAIR
	 * - FOR_REPAIR
	 * - ITEM_FOR_LOAN
	 * - OTHER
	 * - REJECTED
	 * - REPLACEMENT
	 * - TRIAL
	 *
	 * @param string $type Customs option type identifer (see FedEx API docs)
	 * @throws \InvalidArgumentException
	 */
	public function setCustomsOptionType($type)
	{
		$allowed = array(
			'COURTESY_RETURN_LABEL',
			'EXHIBITION_TRADE_SHOW',
			'FAULTY_ITEM',
			'FOLLOWING_REPAIR',
			'FOR_REPAIR',
			'ITEM_FOR_LOAN',
			'OTHER',
			'REJECTED',
			'REPLACEMENT',
			'TRIAL',
		);

		if (!in_array($type, $allowed)) {
			throw new \InvalidArgumentException(sprintf(
				'Invalid shipment customs option type: `%s`. Allowed values: `%s`',
				$type,
				implode('`, `', $type)
			));
		}

		$this->_customsOptionType = $type;
	}

	/**
	 * Enable or disable the generated commercial invoice from FedEx.
	 *
	 * @param  boolean $bool True to request a generated commercial invoice
	 *
	 * @return Shipment      Implements a fluent interface
	 */
	public function requestGeneratedCommercialInvoice($bool = true)
	{
		$this->_requestGeneratedCommercialInvoice = (bool) $bool;

		return $this;
	}

	/**
	 * Add a commodity to this shipment.
	 *
	 * @param Commodity $commodity
	 * @throws \InvalidArgumentException
	 */
	public function addCommodity(Commodity $commodity)
	{
		if ($this->_currencyID !== $commodity->currencyID) {
			throw new \InvalidArgumentException(sprintf(
				'Cannot add Commodity to Shipment: currency ID must match (`%s` passed; Shipment set to `%s`)',
				$commodity->currencyID,
				$this->_currencyID
			));
		}

		$this->_commodities[] = $commodity;
	}

	/**
	 * Clear all commodities and set them from an array.
	 *
	 * @param array[Commodity] $commodities
	 */
	public function setCommodities(array $commodities)
	{
		$this->_commodities = array();

		foreach ($commodities as $commodity) {
			$this->addCommodity($commodity);
		}
	}

	/**
	 * Add a document to this shipment.
	 *
	 * The document must have an ID set.
	 *
	 * @param Document $document
	 */
	public function addDocument(Document $document)
	{
		$this->_documents[] = $document;
	}

	/**
	 * Set the shipper for this shipment.
	 *
	 * @param Address $address     Shipper address
	 * @param string  $personName  Shipper person name
	 * @param string  $companyName Shipper company name
	 */
	public function setShipper(Address $address, $personName, $companyName = null)
	{
		$this->_shipperAddress = $address;
		$this->_shipperPerson  = $personName;
		$this->_shipperCompany = $companyName;
	}

	/**
	 * Set the recipient for this shipment.
	 *
	 * @param Address $address     Recipient address
	 * @param string  $personName  Recipient person name
	 * @param string  $companyName Recipient company name
	 */
	public function setRecipient(Address $address, $personName, $companyName = null)
	{
		$this->_recipientAddress = $address;
		$this->_recipientPerson  = $personName;
		$this->_recipientCompany = $companyName;
	}

	/**
	 * Set the currency ID for this shipment
	 *
	 * @param string $id
	 */
	public function setCurrencyID($id)
	{
		$this->_currencyID = $id;
	}

	/**
	 * Get the total insured value for all commodities in this shipment.
	 *
	 * @return float
	 */
	public function getInsuredValue()
	{
		$value = 0;

		foreach ($this->_commodities as $commodity) {
			$value += $commodity->insuredValue;
		}

		return $value;
	}

	/**
	 * Get the total customs value for all commodities in this shipment.
	 *
	 * @return float
	 */
	public function getCustomsValue()
	{
		$value = 0;

		foreach ($this->_commodities as $commodity) {
			$value += $commodity->customsValue;
		}

		return $value;
	}

	/**
	 * Get the total weight for this shipment based on all commodities. If the
	 * total weight is below 0.5, 0.5 is returned.
	 *
	 * @return float
	 */
	public function getWeight()
	{
		$weight = 0;

		foreach ($this->_commodities as $commodity) {
			$weight += $commodity->weight;
		}

		return $weight < 0.5 ? 0.5 : $weight;
	}

	/**
	 * Get the FedEx currency ID. Generally this is the ISO currency code, with
	 * the notable exception of Pounds Sterling which is "UKL" instead of "GBP"
	 * for no good reason.
	 *
	 * @return string
	 */
	public function getFedexCurrencyID()
	{
		return 'GBP' === $this->_currencyID ? 'UKL' : $this->_currencyID;
	}

	public function getCompanyCurrencyID()
	{
		return 'GBP' === $this->_currencyID ? 'UKL' : $this->_companyCurrency;
	}

	/**
	 * Get the recipient's address.
	 *
	 * @return Address|null
	 */
	public function getRecipientAddress()
	{
		return $this->_recipientAddress;
	}

	/**
	 * Get the file extension for the label that is being requested.
	 *
	 * @return string
	 */
	public function getLabelFileExtension()
	{
		if ('PDF' === $this->_labelSpec['imgType']) {
			return 'pdf';
		}

		if ('PNG' === $this->_labelSpec['imgType']) {
			return 'png';
		}

		return 'txt';
	}

	/**
	 * Get the request data for this shipment.
	 *
	 * @return array
	 */
	public function getRequestData()
	{
		$data = array(
			'ShipTimestamp' => $this->_shipAt->format(\DateTime::ATOM),
			'DropoffType'   => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
			'ServiceType'   => $this->_serviceType,
			'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOK, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			'Shipper'       => array(
				'Contact' => array(
					'PersonName'  => $this->_shipperPerson,
					'CompanyName' => $this->_shipperCompany,
					'PhoneNumber' => $this->_shipperAddress->telephone,
				),
				'Address' => array(
					'StreetLines'         => $this->_convertAddressLines($this->_shipperAddress->lines),
					'City'                => $this->_shipperAddress->town,
					'StateOrProvinceCode' => $this->_shipperAddress->stateID,
					'PostalCode'          => $this->_shipperAddress->postcode,
					'CountryCode'         => $this->_shipperAddress->countryID,
				),
			),
			'Recipient' => array(
				'Contact' => array(
					'PersonName'  => $this->_recipientPerson,
					'CompanyName' => $this->_recipientCompany,
					'PhoneNumber' => $this->_recipientAddress->telephone,
				),
				'Address' => array(
					'StreetLines'         => $this->_convertAddressLines($this->_recipientAddress->lines),
					'City'                => $this->_recipientAddress->town,
					'StateOrProvinceCode' => $this->_recipientAddress->stateID,
					'PostalCode'          => $this->_recipientAddress->postcode,
					'CountryCode'         => $this->_recipientAddress->countryID,
					'Residential'         => isset($this->_recipientCompany) && !empty($this->_recipientCompany),
				),
			),
			'ShippingChargesPayment' => array(
				'PaymentType' => $this->_transportationPaymentType,
				'Payor' => array(
					'ResponsibleParty' => array(
						'AccountNumber' => $this->_transportationPayorAccountNumber,
						'Contact' => array(
							'PersonName'  => $this->_transportationPayorPersonName,
							'CompanyName' => $this->_transportationPayorCompanyName,
							'PhoneNumber' => $this->_transportationPayorAddress ? $this->_transportationPayorAddress->telephone : null,
						),
					),
				)
			),
			'InternationalDetail' => array(
				'DutiesPayment' => array(
					'PaymentType' => $this->_dutiesPaymentType, // valid values RECIPIENT, SENDER and THIRD_PARTY
				),
				'CustomsValue' => array(
					'Amount'   => $this->getCustomsValue(),
					'Currency' => $this->getCompanyCurrencyID(),
				),
				'Commodities'  => array(),
			),
			'LabelSpecification' => array(
				'LabelFormatType' => $this->_labelSpec['format'],
				'ImageType'       => $this->_labelSpec['imgType'],
				'LabelStockType'  => $this->_labelSpec['stockType'],
				'CustomerSpecifiedDetail' => [
					'CustomContent' => [
						'BarcodeEntries' => [
							'BarHeight' => 432,
							'Position' => [
								'X' => 50,
								'Y' => 50,
							],
							'BarcodeSymbology' => 'PDF417',
						],
					]
				],
			),
			'RateRequestTypes'  => array('ACCOUNT'), // valid values ACCOUNT and LIST
			'PackageCount'      => 1,
			'RequestedPackageLineItems' => array(
				'SequenceNumber' => 1,
				'Weight' => array(
					'Value' => $this->getWeight() / 1000,
					'Units' => 'KG', // only LB and KG allowed here
				),
				'InsuredValue' => array(
					'Amount'   => $this->getInsuredValue(),
					'Currency' => $this->getCompanyCurrencyID(),
				)
			),
			'CustomerReferences' => array(),
		);

		if ($this->_transportationPayorAddress) {
			$data['ShippingChargesPayment']['Payor']['ResponsibleParty']['Address'] = array(
				'StreetLines'         => $this->_convertAddressLines($this->_transportationPayorAddress->lines),
				'City'                => $this->_transportationPayorAddress->town,
				'StateOrProvinceCode' => $this->_transportationPayorAddress->stateID,
				'PostalCode'          => $this->_transportationPayorAddress->postcode,
				'CountryCode'         => $this->_transportationPayorAddress->countryID,
				'Residential'         => isset($this->_transportationCompanyName) && !empty($this->_transportationCompanyName),
			);
		}

		// If defined, set the tax identification number
		if ($this->_tin) {
			$data['Shipper']['Tins'] = array(
				'TinType' => $this->_tin['type'],
				'Number'  => $this->_tin['number'],
			);
		}

		// Set commodities
		foreach ($this->_commodities as $commodity) {
			$commodityData = [
				'NumberOfPieces'       => $commodity->quantity,
				'Description'          => $commodity->description,
				'CountryOfManufacture' => $commodity->manufactureCountryID,
				'Weight'               => [
					'Value' => $commodity->weight / 1000,
					'Units' => 'KG'
				],
				'Quantity'             => $commodity->quantity,
				'QuantityUnits'        => 'EA',
				'UnitPrice'            => [
					'Amount'   => $commodity->price,
					'Currency' => $this->getFedexCurrencyID(),
				],
				'CustomsValue'         => [
					'Amount'   => $commodity->customsValue,
					'Currency' => $this->getCompanyCurrencyID(),
				],
				'InsuredValue'         => [
					'Amount'   => $commodity->insuredValue,
					'Currency' => $this->getCompanyCurrencyID(),
				]
			];

			if ($harmonizedCode = $commodity->getHarmonizedCode()) {
				$commodityData['HarmonizedCode'] = $harmonizedCode;
			}

			$data['InternationalDetail']['Commodities'][] = $commodityData;
		}

		// Add extra fields if the user wants to request a generated commercial invoice
		if ($this->_requestGeneratedCommercialInvoice) {
			$data['SpecialServicesRequested'] = array(
				'SpecialServiceTypes' => 'ELECTRONIC_TRADE_DOCUMENTS',
			);

			$data['CustomsClearanceDetail'] = array(
				'DutiesPayment' => array(
					'PaymentType' => $this->_dutiesPaymentType,
				),
				'ExportDetail' => array(
					'B13AFilingOption' => 'NOT_REQUIRED' // dunno what this means but Jeff from FedEx included it in his example
				),
				'CommercialInvoice' => array(
					'TermsOfSale' => $this->_termsOfSale,
				),
				'CustomsValue' => array(
					'Currency' => $this->getCompanyCurrencyID(),
					'Amount'   => $this->getCustomsValue(),
				),
				'Commodities'  => $data['InternationalDetail']['Commodities'],
			);

			if ($this->_purpose) {
				$data['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] = $this->_purpose;
			}

			if ($this->_customsOptionType) {
				$data['CustomsClearanceDetail']['CustomsOptionType'] = $this->_customsOptionType;
			}

			$data['ShippingDocumentSpecification'] = array(
				'ShippingDocumentTypes' => 'COMMERCIAL_INVOICE',
				'CommercialInvoiceDetail' => array(
					'Format' => array(
						'ImageType'           => 'PDF',
						'StockType'           => 'PAPER_LETTER',
						'ProvideInstructions' => true,
					)
				)
			);
		}

		// Set the document IDs on this shipment, if any are defined
		if (!empty($this->_documents)) {
			if (!isset($data['SpecialServicesRequested']['SpecialServiceTypes'])) {
				$data['SpecialServicesRequested']['SpecialServiceTypes'] = 'ELECTRONIC_TRADE_DOCUMENTS';
			}

			$data['SpecialServicesRequested']['EtdDetail'] = array(
				'DocumentReferences' => array(),
			);

			foreach ($this->_documents as $doc) {
				$data['SpecialServicesRequested']['EtdDetail']['DocumentReferences'][] = array(
					'DocumentType' => $doc->getType(),
					'DocumentId'   => $doc->getID(),
				);
			}
		}

		return $data;
	}

	/**
	 * Convert an array of address lines into an acceptable format for FedEx.
	 *
	 * Any falsey values are stripped, and if the array has more than 2 elements
	 * then all elements after the first are collapsed into the second element
	 * and an array with 2 elements is always returned.
	 *
	 * @param  array  $lines Address lines to convert
	 *
	 * @return array         Converted lines, with only 2 elements
	 */
	protected function _convertAddressLines(array $lines)
	{
		$lines = array_filter($lines);
		$return = array();

		$return[] = array_shift($lines);
		$return[] = implode(', ', $lines);

		return $return;
	}
}