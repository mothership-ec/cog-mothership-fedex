<?php

namespace Message\Mothership\Fedex\Request;

class UploadDocuments extends Request
{
	protected $_wsdlName  = 'UploadDocumentService_v1.1';
	protected $_serviceID = 'cdus';

	protected $_destinationCountryID;
	protected $_documents = array();

	public function setDestinationCountryID($countryID)
	{
		static $countries;
		if (!$countries) {
			$countries = getCountries();
		}
		if (!isset($countries[$countryID])) {
			throw new \InvalidArgumentException('Invalid country code: ' . $countryID);
		}
		$this->_destinationCountryID = $countryID;
	}

	public function addDocument(\Fedex\Document $document)
	{
		$this->_documents[] = $document;
	}

	public function getDocuments()
	{
		return $this->_documents;
	}

	protected function _validate()
	{
		if (count($this->_documents) < 1) {
			throw new \Exception('At least one document must be set to upload documents.', \Exception::DOCS_NOT_SET);
		}
		if (!$this->_destinationCountryID) {
			throw new \Exception('Destination country code not set.', \Exception::DEST_COUNTRY_NOT_SET);
		}
		return true;
	}

	protected function _getRequestFields()
	{
		return array(
			'OriginCountryCode'      => \Config::get('fedex')->contact->address->countryCode,
			'DestinationCountryCode' => null,
			'Documents'              => array()
		);
	}

	protected function _buildRequest()
	{
		$this->_request['DestinationCountryCode'] = $this->_destinationCountryID;
		foreach ($this->_documents as $i => $document) {
			$this->_request['Documents'][] = array(
				'LineNumber'        => $i,
				'CustomerReference' => $document->getReference(),
				'DocumentType'      => $document->getType(),
				'FileName'          => $document->getFileName(),
				'DocumentContent'   => $document->getContents()
			);
		}
	}
}