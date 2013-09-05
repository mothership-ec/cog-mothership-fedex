<?php

namespace Message\Mothership\Fedex\Api\Response;

use Message\Mothership\Fedex\Api\Exception;
use Message\Mothership\Fedex\Api\Document;

/**
 * Response for the `UploadDocuments` request.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class UploadDocuments extends AbstractResponse
{
	/**
	 * Validates the response data.
	 *
	 * @throws Exception\ResponseException If no document statuses are returned
	 */
	public function validate()
	{
		if (!isset($this->getData()->DocumentStatuses) || empty($this->getData()->DocumentStatuses)) {
			$exception = new Exception\ResponseException('No document statuses returned');
			$exception->setResponse($this);

			throw $exception;
		}
	}

	/**
	 * Initialise the response.
	 *
	 * This ensures the document statuses were returned as an array (if only one
	 * document was uploaded, the statuses are not returned in an array).
	 *
	 * This method, importantly, sets the FedEx document IDs on the documents
	 * that were uploaded.
	 */
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

	/**
	 * Get the uploaded documents (with the FedEx IDs set on them).
	 *
	 * @return array[Document]
	 */
	public function getDocuments()
	{
		return $this->getRequest()->getDocuments();
	}

	/**
	 * Get the response element for a document with a specific line number.
	 *
	 * @param  int $lineNumber The line number to fetch the response element for
	 *
	 * @return stdClass        The response data for the given document
	 *
	 * @throws Exception\Exception If there is no document element with the
	 *                             given line number
	 */
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