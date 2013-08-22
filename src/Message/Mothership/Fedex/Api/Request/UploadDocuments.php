<?php

namespace Message\Mothership\Fedex\Api\Request;

use Message\Mothership\Fedex\Api\Service;
use Message\Mothership\Fedex\Api\Exception;
use Message\Mothership\Fedex\Api\Document;

/**
 * Upload one or more documents to the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class UploadDocuments implements RequestInterface
{
	protected $_originCountryCode;
	protected $_destinationCountryCode;
	protected $_documents = array();

	/**
	 * {@inheritDoc}
	 */
	public function getService()
	{
		return new Service\UploadDocument;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMethod()
	{
		return 'UploadDocuments';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Exception\Exception If no documents have been set
	 * @throws Exception\Exception If the origin country code is not set
	 * @throws Exception\Exception If the destination country code is not set
	 */
	public function validate()
	{
		if (count($this->_documents) < 1) {
			throw new Exception\Exception('At least one document must be set to upload documents');
		}

		if (!$this->_originCountryCode) {
			throw new Exception\Exception('Origin country code not set');
		}

		if (!$this->_destinationCountryID) {
			throw new Exception\Exception('Destination country code not set');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequestData()
	{
		$data = array(
			'OriginCountryCode'      => $this->_originCountryCode,
			'DestinationCountryCode' => $this->_destinationCountryCode,
			'Documents'              => array(),
		);

		foreach ($this->_documents as $i => $document) {
			$data['Documents'][] = array(
				'LineNumber'        => $i,
				'CustomerReference' => $document->getReference(),
				'DocumentType'      => $document->getType(),
				'FileName'          => $document->getFileName(),
				'DocumentContent'   => $document->getContents()
			);
		}

		return $data;
	}

	/**
	 * Set the destination country code for the documents being uploaded.
	 *
	 * @param string $code The destination country code
	 */
	public function setDestinationCountryCode($code)
	{
		$this->_destinationCountryCode = $code;
	}

	/**
	 * Set the origin country code for the documents being uploaded.
	 *
	 * @param string $code The origin country code
	 */
	public function setOriginCountryCode($code)
	{
		$this->_originCountryCode = $code;
	}

	/**
	 * Add a document to be uploaded to FedEx with this request.
	 *
	 * @param Document $document The document to add
	 */
	public function addDocument(Document $document)
	{
		$this->_documents[] = $document;
	}
}