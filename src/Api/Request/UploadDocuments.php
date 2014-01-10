<?php

namespace Message\Mothership\Fedex\Api\Request;

use Message\Mothership\Fedex\Api\Service;
use Message\Mothership\Fedex\Api\Response;
use Message\Mothership\Fedex\Api\Exception;
use Message\Mothership\Fedex\Api\Document;

/**
 * Upload one or more documents to the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class UploadDocuments implements RequestInterface
{
	protected $_originCountryID;
	protected $_destinationCountryID;
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
	 */
	public function getResponseObject()
	{
		return new Response\UploadDocuments;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws Exception\Exception If no documents have been set
	 * @throws Exception\Exception If the origin country ID is not set
	 * @throws Exception\Exception If the destination country ID is not set
	 */
	public function validate()
	{
		if (count($this->_documents) < 1) {
			throw new Exception\Exception('At least one document must be set to upload documents');
		}

		if (!$this->_originCountryID) {
			throw new Exception\Exception('Origin country ID not set');
		}

		if (!$this->_destinationCountryID) {
			throw new Exception\Exception('Destination country ID not set');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequestData()
	{
		$data = array(
			'OriginCountryCode'      => $this->_originCountryID,
			'DestinationCountryCode' => $this->_destinationCountryID,
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
	 * Set the destination country id for the documents being uploaded.
	 *
	 * @param string $id The destination country id
	 */
	public function setDestinationCountryID($id)
	{
		$this->_destinationCountryID = $id;
	}

	/**
	 * Set the origin country id for the documents being uploaded.
	 *
	 * @param string $id The origin country id
	 */
	public function setOriginCountryID($id)
	{
		$this->_originCountryID = $id;
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

	/**
	 * Get all documents defined on this request.
	 *
	 * @return array[Document]
	 */
	public function getDocuments()
	{
		return $this->_documents;
	}
}