# Mothership FedEx

The `Message\Mothership\Fedex` cogule provides Fedex integration for dispatching orders. It also provides new dispatch "methods" for these.

## Installation

Install this package using [Composer](http://getcomposer.org/). The package name is `message/cog-mothership-fedex`.

You will need to add Message's private package server to the `repositories` key in `composer.json`:

	{
		"repositories": [
			{
				"type": "composer",
				"url" : "http://packages.message.co.uk"
			}
		],
		"require": {
			"message/cog-mothership-fedex": "1.0.*"
		}
	}

## Dispatch Methods

## Shipping

### Electronic Trade Documents (ETD)

#### Generated Commercial Invoice

## Todo

- Add a `Dispatcher` to `Api` to handle the communications
- Simplify the `Api\Request` and `Api\Response` classes to not do the above
	- Make them implement an interface each
- Add the `Ship` request + response
- Does `Notification` need it's own namespace?
- Add service for the public API classes that need to be services
- Set the dispatch methos selection function in `uniform_wares/mothership` to select FedEx UK for UK delivery orders, and FedexExpress for others
- Add config for ETD stuff
- How to deal with the different endpoint URL in the WSDL when testing? we have separate WSDL files for live and test atm