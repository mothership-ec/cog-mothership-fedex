<?php

namespace Message\Mothership\Fedex\Api;

use Message\Mothership\Commerce\Order\Entity\Item;

/**
 * Represents a FedEx "Commodity" (essentially a product in a package/shipment).
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Commodity
{
	public $quantity;
	public $description;
	public $manufactureCountryID;
	public $weight; // as always, in grams

	public $currencyID;
	public $price;
	public $customsValue;
	public $insuredValue;

	private $_harmonizedCode;

	/**
	 * Populate this Commodity from an order item row.
	 *
	 * @todo Ensure the Product from `Item::getProduct()` returns the Product in
	 *       the correct locale for the order, so the currency is correct.
	 *
	 * @param  Item\Row $row The item row to populate this Commodity from
	 *
	 * @return Commodity     Returns $this for chainability
	 */
	public function populateFromItemRow(Item\Row $row)
	{
		$product = $row->first()->getProduct();

		$this->quantity             = $row->count();
		$this->description          = $product ? $product->exportDescription : null;
		$this->manufactureCountryID = $product ? $product->exportManufactureCountryID : null;
		$this->weight               = $row->sum('weight');
		$this->currencyID           = $row->first()->order->currencyID;
		$this->customsValue         = $row->sum('net') + $row->sum('discount'); // Ex-Tax Price (including discounts)
		$this->price                = $this->customsValue / $this->quantity;
		$this->insuredValue         = $product ? $product->exportValue : 0;
		$this->_harmonizedCode      = $product ? $product->getExportCode() : null;

		return $this;
	}

	public function getHarmonizedCode()
	{
		return $this->_harmonizedCode;
	}
}