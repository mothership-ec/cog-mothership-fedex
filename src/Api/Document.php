<?php

namespace Message\Mothership\Fedex\Api;

use Message\Cog\Filesystem\File;

/**
 * Represents a document to be sent, or already sent to the FedEx API.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Document
{
	protected $_file;
	protected $_id;
	protected $_reference;
	protected $_type = 'OTHER';

	protected $_allowedTypes = array(
		'COMMERCIAL_INVOICE',
		'CERTIFICATE_OF_ORIGIN',
		'NAFTA_CERTIFICATE_OF_ORIGIN',
		'PRO_FORMA_INVOICE',
		'OTHER',
	);

	/**
	 * Constructor.
	 *
	 * @param File $file The file object for the document to use
	 *
	 * @throws \InvalidArgumentException If the file does not exist or is not readable
	 */
	public function __construct(File $file)
	{
		if (!$file->isReadable()) {
			throw new \InvalidArgumentException(sprintf('Document is not readable: `%s`', $file->getRealPath()));
		}

		$this->_file = $file;
	}

	/**
	 * Set the document type.
	 *
	 * Allowed values:
	 *
	 *  * COMMERCIAL_INVOICE
	 *  * CERTIFICATE_OF_ORIGIN
	 *  * NAFTA_CERTIFICATE_OF_ORIGIN
	 *  * PRO_FORMA_INVOICE
	 *  * OTHER
	 *
	 * @param string $type The document type
	 *
	 * @throws Exception\Exception If the document type is not allowed
	 */
	public function setType($type)
	{
		if (!in_array($type, $this->_allowedTypes)) {
			throw new Exception\Exception(sprintf('Document type not allowed: `%s`', $type));
		}

		$this->_type = $type;
	}

	/**
	 * Set the custom reference for this document.
	 *
	 * @param string $reference
	 */
	public function setReference($reference)
	{
		$this->_reference = $reference;
	}

	/**
	 * Set the FedEx ID for this document. This happens only once it has been
	 * uploaded to the FedEx API
	 *
	 * @param string $id The FedEx Document ID
	 */
	public function setID($id)
	{
		$this->_id = $id;
	}

	/**
	 * Get the document type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Get the document reference.
	 *
	 * @return string|null
	 */
	public function getReference()
	{
		return $this->_reference;
	}

	/**
	 * Get the file contained within this document.
	 *
	 * @return File
	 */
	public function getFile()
	{
		return $this->_file;
	}

	/**
	 * Get the FedEx ID for this document. This is only set once it has been
	 * uploaded to the FedEx API
	 *
	 * @return string|null $id The FedEx Document ID
	 */
	public function getID()
	{
		return $this->_id;
	}

	/**
	 * Get the contents of the file.
	 *
	 * @return string File contents
	 */
	public function getContents()
	{
		return file_get_contents($this->_file->getRealPath());
	}

	/**
	 * Get the document's file name, without the path. Including the file
	 * extension.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->_file->getFilename();
	}
}