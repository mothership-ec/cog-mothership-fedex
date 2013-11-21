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
	protected $_transportationPayorCountryCode;

	protected $_purpose;

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

	public function __construct()
	{
		$this->_shipAt = new DateTimeImmutable;
	}

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
	}

	public function getDispatch()
	{
		return $this->_dispatch;
	}

	public function getCommodities()
	{
		return $this->_commodities;
	}

	public function setServiceType($type)
	{
		$this->_serviceType = $type;
	}

	public function setTin($number, $type = 'BUSINESS_NATIONAL')
	{
		$this->_tin = array(
			'number' => $number,
			'type'   => $type,
		);
	}

	public function setTermsOfSale($terms)
	{
		$this->_termsOfSale = $terms;
	}

	public function setLabelSpec($format, $imgType = null, $stockType = null)
	{
		$this->_labelSpec['format']    = $format;
		$this->_labelSpec['imgType']   = $imgType;
		$this->_labelSpec['stockType'] = $stockType;
	}

	public function setTransportationPayment($type, $accountNumber, $countryCode)
	{
		$this->_transportationPaymentType        = $type;
		$this->_transportationPayorAccountNumber = $accountNumber;
		$this->_transportationPayorCountryCode   = $countryCode;
	}

	public function setDutiesPaymentType($type)
	{
		$this->_dutiesPaymentType = $type;
	}

	public function setPurpose($purpose)
	{
		$allowedPurposes = array(
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

	public function requestGeneratedCommercialInvoice($bool = true)
	{
		$this->_requestGeneratedCommercialInvoice = (bool) $bool;

		return $this;
	}

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

	public function addDocument(Document $document)
	{
		$this->_documents[] = $document;
	}

	public function setShipper(Address $address, $personName, $companyName = null)
	{
		$this->_shipperAddress = $address;
		$this->_shipperPerson  = $personName;
		$this->_shipperCompany = $companyName;
	}

	public function setRecipient(Address $address, $personName, $companyName = null)
	{
		$this->_recipientAddress = $address;
		$this->_recipientPerson  = $personName;
		$this->_recipientCompany = $companyName;
	}

	public function setCurrencyID($id)
	{
		$this->_currencyID = $id;
	}

	public function getInsuredValue()
	{
		$value = 0;

		foreach ($this->_commodities as $commodity) {
			$value += $commodity->insuredValue;
		}

		return $value;
	}

	public function getCustomsValue()
	{
		$value = 0;

		foreach ($this->_commodities as $commodity) {
			$value += $commodity->customsValue;
		}

		return $value;
	}

	public function getWeight()
	{
		$weight = 0;

		foreach ($this->_commodities as $commodity) {
			$weight += $commodity->weight;
		}

		return $weight < 0.5 ? 0.5 : $weight;
	}

	public function getFedexCurrencyID()
	{
		return 'GBP' === $this->_currencyID ? 'UKL' : $this->_currencyID;
	}

	public function getRecipientAddress()
	{
		return $this->_recipientAddress;
	}

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
					'AccountNumber' => $this->_transportationPayorAccountNumber,
					'CountryCode'   => $this->_transportationPayorCountryCode,
				)
			),
			'InternationalDetail' => array(
				'DutiesPayment' => array(
					'PaymentType' => $this->_dutiesPaymentType, // valid values RECIPIENT, SENDER and THIRD_PARTY
				),
				'CustomsValue' => array(
					'Amount'   => $this->getCustomsValue(),
					'Currency' => $this->getFedexCurrencyID(),
				),
				'Commodities'  => array(),
			),
			'LabelSpecification' => array(
				'LabelFormatType' => $this->_labelSpec['format'],
				'ImageType'       => $this->_labelSpec['imgType'],
				'LabelStockType'  => $this->_labelSpec['stockType'],
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
					'Currency' => $this->getFedexCurrencyID(),
				)
			),
			'CustomerReferences' => array(),
		);

		// If defined, set the tax identification number
		if ($this->_tin) {
			$data['Shipper']['Tins'] = array(
				'TinType' => $this->_tin['type'],
				'Number'  => $this->_tin['number'],
			);
		}

		// Set commodities
		foreach ($this->_commodities as $commodity) {
			$data['InternationalDetail']['Commodities'][] = array(
				'NumberOfPieces'       => $commodity->quantity,
				'Description'          => $commodity->description,
				'CountryOfManufacture' => $commodity->manufactureCountryID,
				'Weight'               => array(
					'Value' => $commodity->weight / 1000,
					'Units' => 'KG'
				),
				'Quantity'             => $commodity->quantity,
				'QuantityUnits'        => 'EA',
				'UnitPrice'            => array(
					'Amount'   => $commodity->price,
					'Currency' => $this->getFedexCurrencyID(),
				),
				'CustomsValue'         => array(
					'Amount'   => $commodity->customsValue,
					'Currency' => $this->getFedexCurrencyID(),
				),
				'InsuredValue'         => array(
					'Amount'   => $commodity->insuredValue,
					'Currency' => $this->getFedexCurrencyID(),
				)
			);
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
					'Currency' => $this->getFedexCurrencyID(),
					'Amount'   => $this->getCustomsValue(),
				),
				'Commodities'  => $data['InternationalDetail']['Commodities'],
			);

			if ($this->_purpose) {
				$data['CustomsClearanceDetail']['CommercialInvoice']['Purpose'] = $this->_purpose;
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

	protected function _convertAddressLines(array $lines)
	{
		$lines = array_filter($lines);
		$return = array();

		$return[] = array_shift($lines);
		$return[] = implode(', ', $lines);

		return $return;
	}
}