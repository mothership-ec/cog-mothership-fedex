<?php

namespace Message\Mothership\Fedex\Dispatch;

use Message\Mothership\Commerce\Order\Entity\Dispatch\Dispatch;

class Fedex extends Dispatch
{
	public function getTrackingCode()
	{
		//USE THE TEST WSDL IF WE'RE NOT IN LIVE MODE
		if (SITE_MODE == 'dev') {
			$this->wsdlFile .= '.test';
		}

		//LOAD THE REQUEST ARAY EXPECTED BY FEDEX
		$this->initRequestArray();

		//CONFIGURE
		$this->setKey(array(Config::get('fedex')->apiKey, Config::get('fedex')->apiPassword));
		$this->setMeterNumber(Config::get('fedex')->meterNumber);
		$this->setAccountNumber(Config::get('fedex')->accountNumber);
		$this->setService(Config::get('fedex')->getServices());
		$this->setShipper(Config::get('fedex')->getShipperData());

		//SET TRANSACTION ID
		$this->request['TransactionDetail']['CustomerTransactionId'] = $this->order->orderID . '_' . $this->despatch->despatchID;

		//SET WEIGHT
		$weight = (($this->despatch->weight > 500) ? ($this->despatch->weight / 1000) : 0.5);
		$this->request['RequestedShipment']['RequestedPackageLineItems']['Weight']['Value'] = $weight;

		//ADD ITEMS
		$itemlist = array();
		$total = 0;
		$totalCustoms = 0;

		foreach ($this->despatch->getItemIDs() as $itemID) {
			$item = $this->order->getItems($itemID);
			$itemlist[$item->unitID][] = $item;
		}

		foreach ($itemlist as $unitID => $items) {
			$item = current($items);

			// SKIP GIFT WRAP
			if ($item->productID == Config::get('gifting')->wrap->productID) {
				continue;
			}

			if (!$exportInfo = $this->getProductExportInfo($item->catalogueID, $item->productID, $this->order)) {
				return;
			}
			$total += $exportInfo['export_value'];
			//USE FEDEX CURRENCY ID IN PLACE OF GBP
			if ($exportInfo['currency_id'] == 'GBP') {
				$exportInfo['currency_id'] = 'UKL';
			}

			// customs value is ex vat price
			$customsValue = round($items[0]->originalPrice / (1 + ($items[0]->taxRate / 100)), 2);

			$totalCustoms += $customsValue;

			$commodity = array(
				'NumberOfPieces'       => count($items),
				'Description'          => $exportInfo['export_description'],
				'CountryOfManufacture' => $exportInfo['export_manufacture_country_id'],
				'Weight'               => array(
					'Value' => ($item->weight * count($items)) / 1000,
					'Units' => 'KG'
				),
				'Quantity'             => count($items),
				'QuantityUnits'        => 'EA',
				'UnitPrice'            => array(
					'Amount'   => $customsValue,
					'Currency' => $exportInfo['currency_id']
				),
				'CustomsValue'         => array(
					'Amount'   => $customsValue,
					'Currency' => $exportInfo['currency_id']
				),
				'InsuredValue'         => array(
					'Amount'   => $exportInfo['export_value'],
					'Currency' => $exportInfo['currency_id']
				)
			);
			$this->request['RequestedShipment']['InternationalDetail']['Commodities'][] = $commodity;
			if (Config::get('fedex')->etd->commercialInvoice->enabled) {
				$this->request['RequestedShipment']['CustomsClearanceDetail']['Commodities'][] = $commodity;
			}
		}

		//SET CUSTOMS & CARRIAGE VALUES
		$this->request['RequestedShipment']['InternationalDetail']['CustomsValue']['Amount']   = $totalCustoms;
		$this->request['RequestedShipment']['InternationalDetail']['CustomsValue']['Currency'] = $exportInfo['currency_id'];

		$this->request['RequestedShipment']['RequestedPackageLineItems']['InsuredValue']['Amount']   = $total;
		$this->request['RequestedShipment']['RequestedPackageLineItems']['InsuredValue']['Currency'] = $exportInfo['currency_id'];

		if (Config::get('fedex')->etd->commercialInvoice->enabled) {
			$this->request['RequestedShipment']['CustomsClearanceDetail']['CustomsValue']['Amount']   = $totalCustoms;
			$this->request['RequestedShipment']['CustomsClearanceDetail']['CustomsValue']['Currency'] = $exportInfo['currency_id'];
		}

		//ADD RECIPIENT
		$address = $this->order->getAddress('delivery');
		$addressCodeType = 'PostalCode';
		$this->request['RequestedShipment']['Recipient'] = array(
			'Contact' => array(
				'PersonName'  => $address->name,
				'PhoneNumber' => $address->telephone
			),
			'Address' => array(
				'StreetLines'         => array(
					$address->address_1,
					$address->address_2
				),
				'City'                => $address->town,
				'PostalCode'          => $address->postcode,
				'StateOrProvinceCode' => '',
				'CountryCode'         => $address->countryID,
				'Residential'         => false
			)
		);
		if (in_array($address->countryID, array('US', 'CA', 'MX'))) {
			$this->request['RequestedShipment']['Recipient']['Address']['StateOrProvinceCode'] = $address->stateID;
		}

		//LOAD THE CLIENT
		$this->client = new SoapClient(SYSTEM_PATH . $this->wsdlFile, array('trace' => 1));
		if ($this->client instanceof SoapClient) {
			$this->readyToSend = true;
		} else {
			$this->fb->addError('Connection failure: unable to create a SOAP client');
		}

	}
}