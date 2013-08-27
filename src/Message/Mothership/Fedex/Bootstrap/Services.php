<?php

namespace Message\Mothership\Fedex\Bootstrap;

use Message\Mothership\Fedex;

use Message\Cog\Bootstrap\ServicesInterface;

/**
 * FedEx services bootstrap.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Services implements ServicesInterface
{
	public function registerServices($container)
	{
		// Add dispatch methods
		$container['order.dispatch.methods'] = $container->share($container->extend('order.dispatch.methods', function($methods) {
			$methods->add(new Fedex\DispatchMethod\FedexExpress);
			$methods->add(new Fedex\DispatchMethod\FedexUk);

			return $methods;
		}));

		$container['fedex.api.dispatcher'] = function($c) {
			$dispatcher = new Fedex\Api\Dispatcher;
			$cfg        = $c['cfg']->fedex;

			$dispatcher->setTestMode($cfg->testMode);

			$dispatcher->setApiDetails($cfg->apiKey, $cfg->apiPassword);
			$dispatcher->setAccountNumber($cfg->accountNumber);
			$dispatcher->setMeterNumber($cfg->meterNumber);

			return $dispatcher;
		};
	}
}