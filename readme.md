# Mothership FedEx

The `Message\Mothership\Fedex` cogule provides Fedex integration for dispatching orders. It also provides new dispatch methods for these.

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

## Dispatch methods

This cogule provides two dispatch methods that are added to the `order.dispatch.methods` service:

- FedEx Express (`fedex-express`)
- FedEx UK (`fedex-uk`)

## Automated dispatch

This cogule integrates with the FedEx Express API and enables automatic dispatch for any dispatch using the type `fedex-express`.

It is important to note that there is no integration with FedEx UK as they are a totally separate entity and, at the time of writing this, don't have a suitable API for submitting shipments.

## API

This cogule contains a small library for interacting with FedEx's SOAP API.

### Configuration

There is a configuration fixture for this cogule. To connect to the FedEx API, you'll need to enter some authentication details in there. The base requirement is:

- `api-key` - The API key provided by FedEx
- `api-password` - The API password provided by FedEx
- `meter-number` - The meter number to bill shipments to
- `account-number` - The account number responsible for the shipments

For testing, it is possible to create an account on the [FedEx Developer Website](http://fedex.com/developer) and generate these details.

### Overview of the API library

The API library consists of three main parts:

- **Requests**: These detail the request and request data to be sent to the FedEx API.
- **Responses**: These detail the response back from the FedEx API for a request. They are defined by the corresponding request.
- **Dispatcher**: This is responsible for handling requests, sending them to the FedEx API and returning the relevant response or throwing error exceptions.
- **Services**: FedEx group their API into different "services". Each has their own WSDL endpoint. The request defined which service should be used.

You may also notice some references to something called a `PreparedRequest`. This is an implementation detail you shouldn't need to worry about as an API consumer, but it is essentially there so the API authentication and other top-level generic request data doesn't need to belong to every single `Request` class.

### Events

Before an API request is made, the `fedex.api.request` event is dispatched. The event is an instance of `Message\Mothership\Fedex\Api\Event\RequestEvent` so listeners can inspect the request and call methods on it. This happens after the request is validated.

Once a response is received and has been validated an initialised, the `fedex.api.response` event is dispatched. The event is an instance of `Message\Mothership\Fedex\Api\Event\ResponseEvent` so the response can be inspected by listeners.

### Uploading documents

There is an `UploadDocuments` request available which allows you to upload any document to the FedEx API and attach it to a shipment that you can submit later. Documents must be wrapped in the API's `Document` class so that the FedEx document identifier can be retrieved.

When the response has been successfully dispatched by the `Dispatcher`, the `Response` returned will have the documents and will have set the identifiers on them.

You can then add these `Document` instances to a `Shipment` you're about to submit using `addDocument()`.

### Generated Commercial Invoice

It is possible to request for FedEx to generate a commercial invoice for your shipment. This requires the shipper to be signed up for ETD (electronic trade documents) but it's generally a great idea because it saves on a lot of paper and it saves us having to make our own commercial invoices.

Mothership does not generate commercial invoices yet, so it's important to enable this.

To do so, just call `requestGeneratedCommercialInvoice(true)` on your `Shipment` instance.

### WSDL files

Each service needs its own `.wsdl` file supplied by FedEx on their [developer website](http://fedex.com/developer). These are stored in `/resources/wsdl` and are referenced by their relevant service classes (instances of `Message\Mothership\Fedex\Api\Service\ServiceInterface`).

The endpoints are automatically changed by the API subsystem depending on the value of the `test-mode` property on the `fedex` configuration group. The endpoint for both live and test must be set on the service class in the `getWsdlEndpoint()` method. When adding a new service or `.wsdl` file, you can get these endpoints by looking at the bottom of the file and finding the value of the `location` attribute in service->port->address. The live URI will be on the `ws.fedex.com` host and test URIs will be on the `wsbeta.fedex.com` host.
