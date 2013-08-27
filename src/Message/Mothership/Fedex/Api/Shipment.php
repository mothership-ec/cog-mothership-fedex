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
 * Custom documents can also be assigned to this shipment.
 *
 * @todo Make commodity class (?)
 * @todo In populateFromDispatch, we need to make Order give us a nice way to make ItemRows from the items in a dispatch? propbably requires custom entity collections again
 * @todo Tidy up!
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
	protected $_dutiesPaymentType;

	protected $_currencyID;

	//protected $_shippingChargesPayment;
	protected $_internationalDetail;
	protected $_labelSpecification;
	protected $_rateRequestTypes;
	protected $_customerReferences;

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

	public function __construct()
	{
		$this->_shipAt = new DateTimeImmutable;
	}

	public function populateFromDispatch(Dispatch $dispatch)
	{
		$this->setCurrencyID($dispatch->order->currencyID);

		foreach ($dispatch->items as $item) {
			$commodity = new Commodity;
			$commodity->populateFromItem($item);

			$this->addCommodity($commodity);
		}
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

	public function setTransportationPaymentType($type)
	{
		$this->_transportationPaymentType = $type;
	}

	public function setDutiesPaymentType($type)
	{
		$this->_dutiesPaymentType = $type;
	}

	public function requestGeneratedCommercialInvoice($bool = true)
	{
		$this->_requestGeneratedCommercialInvoice = (bool) $bool;

		return $this;
	}

	public function addCommodity(Commodity $commodity)
	{
		$this->_commodities[] = $commodity;
	}

	public function addDocument(Document $document)
	{
		$this->_documents[] = $document;
	}

	public function setShipper(Address $address, $companyName, $personName = null)
	{
		$this->_shipperAddress = $address;
		$this->_shipperCompany = $companyName;
		$this->_shipperPerson  = $personName;
	}

	public function setRecipient(Address $address, $companyName, $personName = null)
	{
		$this->_recipientAddress = $address;
		$this->_recipientCompany = $companyName;
		$this->_recipientPerson  = $personName;
	}

	public function setCurrencyID($id)
	{
		$this->_currencyID = ('GBP' === $id) ? 'UKL' : $id;
	}

	public function getInsuredValue()
	{
		// tot up all commodities
	}

	public function getCustomsValue()
	{
		// tot up all commodities
	}

	public function getRequestData()
	{
		$data = array(
			'ShipTimestamp' => $this->_shipAt->getTimestamp(),
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
					'StreetLines'         => $this->_shipperAddress->lines,
					'City'                => $this->_shipperAddress->city,
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
					'StreetLines'         => $this->_recipientAddress->lines,
					'City'                => $this->_recipientAddress->city,
					'StateOrProvinceCode' => $this->_recipientAddress->stateID,
					'PostalCode'          => $this->_recipientAddress->postcode,
					'CountryCode'         => $this->_recipientAddress->countryID,
					'Residential'         => isset($this->_recipientCompany) && !empty($this->_recipientCompany),
				),
			),
			'ShippingChargesPayment' => array(
				'PaymentType' => $this->_transportationPaymentType,
				// 'Payor' => array(
				// 	'AccountNumber' => null, // Replace 'XXX' with payors account number
				// 	'CountryCode'   => 'GB',
				// )
			),
			'InternationalDetail' => array(
				'DutiesPayment' => array(
					'PaymentType' => $this->_dutiesPaymentType, // valid values RECIPIENT, SENDER and THIRD_PARTY
				),
				'CustomsValue' => array(
					'Amount'   => $this->getCustomsValue(),
					'Currency' => $this->_currencyID,
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
					'Currency' => $this->_currencyID,
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
					'Currency' => $this->_currencyID,
					'Amount'   => $this->getCustomsValue(),
				),
				'Commodities'  => $data['InternationalDetail']['Commodities'],
			);

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

		// Set commodities

		return $data;
	}
}