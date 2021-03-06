<?php

namespace Message\Mothership\Fedex\Bootstrap;

use Message\Mothership\Fedex;

use Message\Mothership\Commerce\Address\Address;

use Message\Cog\Bootstrap\ServicesInterface;

/**
 * FedEx services bootstrap.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Services implements ServicesInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function registerServices($services)
	{
		// Add dispatch methods
		$services->extend('order.dispatch.methods', function($methods) {
			$methods->add(new Fedex\DispatchMethod\FedexExpress);
			$methods->add(new Fedex\DispatchMethod\FedexUk);

			return $methods;
		});

		$services['fedex.api.prepared_request'] = $services->factory(function($c) {
			$cfg     = $c['cfg']->fedex;
			$request = new Fedex\Api\PreparedRequest;

			$request->setApiDetails($cfg->apiKey, $cfg->apiPassword);
			$request->setAccountNumber($cfg->accountNumber);
			$request->setMeterNumber($cfg->meterNumber);

			return $request;
		});

		$services['fedex.api.dispatcher'] = $services->factory(function($c) {
			$dispatcher = new Fedex\Api\Dispatcher($c['fedex.api.prepared_request'], $c['event.dispatcher']);

			$dispatcher->setTestMode($c['cfg']->fedex->testMode);

			return $dispatcher;
		});

		$services['fedex.api.shipment'] = $services->factory(function($c) {
			$shipment = new Fedex\Api\Shipment($c['currency.company']);

			// Create Address object for merchant address
			$shipperAddress = new Address;
			$shipperAddress->setLines($c['cfg']->merchant->address->lines);
			$shipperAddress->telephone = $c['cfg']->merchant->telephone;
			$shipperAddress->town      = $c['cfg']->merchant->address->town;
			$shipperAddress->postcode  = $c['cfg']->merchant->address->postcode;
			$shipperAddress->countryID = $c['cfg']->merchant->address->countryID;
			$shipperAddress->stateID   = $c['cfg']->merchant->address->stateID;

			// Set shipper address & contact details
			$shipment->setShipper($shipperAddress, $c['cfg']->merchant->companyName, $c['cfg']->merchant->companyName);

			// Set VAT registration number, if defined
			if ($vatReg = $c['cfg']->merchant->vatRegistration) {
				$shipment->setTin($vatReg);
			}

			// Set terms of sale
			$shipment->setTermsOfSale($c['cfg']->fedex->termsOfSale);

			// Set payment types
			$shipment->setTransportationPayment(
				$c['cfg']->fedex->payment->transportation->type,
				$c['cfg']->fedex->payment->transportation->accountNumber,
				$c['cfg']->merchant->companyName,
				$c['cfg']->merchant->companyName
			);

			$shipment->setDutiesPaymentType($c['cfg']->fedex->payment->duties->type);

			// Set service type
			$shipment->setServiceType($c['cfg']->fedex->serviceType);

			// Set label specification
			$shipment->setLabelSpec(
				$c['cfg']->fedex->label->format,
				$c['cfg']->fedex->label->imageType,
				$c['cfg']->fedex->label->stockType
			);

			return $shipment;
		});
	}
}