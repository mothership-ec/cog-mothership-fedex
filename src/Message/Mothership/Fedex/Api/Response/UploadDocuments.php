<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\Exception;

class UploadDocuments extends AbstractResponse
{
	public function validate()
	{
		if (!isset($this->getData()->DocumentStatuses) || empty($this->getData()->DocumentStatuses)) {
			$exception = new Exception\ResponseException('No document statuses returned');
			$exception->setResponse($this);

			throw $exception;
		}
	}

	public function init()
	{
		// Ensure DocumentStatuses is iterable (if only one document, it's not returned in an array)
		if (!is_array($this->getData()->DocumentStatuses)) {
			$this->getData()->DocumentStatuses = array($this->getData()->DocumentStatuses);
		}

		// Set the FedEx IDs for the documents
		foreach ($this->getRequest()->getDocuments() as $lineNum => $document) {
			$responseDoc = $this->_getResponseDocument($lineNum);

			$document->setID($responseDoc->DocumentId);
		}
	}

	public function getDocuments()
	{
		return $this->getRequest()->getDocuments();
	}

	protected function _getResponseDocument($lineNumber)
	{
		foreach ($this->getData()->DocumentStatuses as $doc) {
			if ($lineNumber == $doc->LineNumber) {
				return $doc;
			}
		}

		throw new Exception\Exception(sprintf('UploadDocuments did not return document with line number `%s`', $lineNumber));
	}
}