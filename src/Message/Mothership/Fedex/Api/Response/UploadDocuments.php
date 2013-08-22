<?php

namespace Message\Mothership\Fedex\Response;

use Message\Mothership\Fedex\Request\Request;

class UploadDocuments extends Response
{
	protected $_documents;

	public function __construct($response, Request $request)
	{
		// IF ONLY ONE DocumentStatus RETURNED, IT'S NOT RETURNED IN
		// AN ARRAY FOR SOME REASON. THIS FIXES THAT.
		if (isset($response->DocumentStatuses) && is_object($response->DocumentStatuses)) {
			$response->DocumentStatuses = array($response->DocumentStatuses);
		}
		parent::__construct($response, $request);
		$this->_setDocumentIDs();
	}

	public function getDocuments()
	{
		return $this->_request->getDocuments();
	}

	protected function _validate()
	{
		if (!isset($this->_response->DocumentStatuses) || empty($this->_response->DocumentStatuses)) {
			throw new \Exception('No document statuses returned.', Exception::NO_DOC_STATUS_RETURNED);
		}
	}

	protected function _setDocumentIDs()
	{
		foreach ($this->getDocuments() as $key => $document) {
			$document->setID($this->_getResponseDocument($key)->DocumentId);
		}
	}

	/**
	 * Get a DocumentStatuses node by LineNumber. LineNumber is
	 * also the index on the document array in the request.
	 *
	 * @return stdClass Node from DocumentStatuses with the matching LineNumber
	 */
	protected function _getResponseDocument($key)
	{
		foreach ($this->_response->DocumentStatuses as $document) {
			if ($document->LineNumber == $key) {
				return $document;
			}
		}
		throw new \Exception('Could not find document with key: ' . $key, Exception::DOC_KEY_NOT_FOUND);
	}

}