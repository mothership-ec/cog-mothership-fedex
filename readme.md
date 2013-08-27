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

This cogule provides two dispatch methods that are added to the `order.dispatch.methods` service:

- FedEx Express (`fedex-express`)
- FedEx UK (`fedex-uk`)

## Shipping

### Electronic Trade Documents (ETD)

#### Generated Commercial Invoice

## Todo

- Log requests and responses to the API
- Catch SoapFault's ?
- Add the `Ship` request + response
- Provide a way to set the transaction ID for a request? (new method?)
- Add service for the public API classes that need to be services
- Add config for ETD stuff
- Finish writing readme