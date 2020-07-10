<?php


namespace SwedbankPay\Checkout\Helper\Factory;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use SwedbankPay\Api\Service\Paymentorder\Resource\Collection\Item\OrderItem;

class OrderItemFactory
{
    /**
     * @param QuoteItem $quoteItem
     * @return OrderItem
     */
    public function createByQuoteItem(QuoteItem $quoteItem)
    {
        $sku = $quoteItem->getSku();
        $name = $quoteItem->getName();
        $type = 'PRODUCT';
        $itemClass = 'ProductGroup1';
        $quantity = (float) $quoteItem->getQty();
        $unitPrice = (int) round($quoteItem->getPriceInclTax() * 100);
        $amount = (int) round($quoteItem->getRowTotalInclTax() * 100);
        $vatAmount = (int) round($quoteItem->getTaxAmount() * 100);
        $vatPercent = (int) round($quoteItem->getTaxPercent() * 100);
        $discountPrice = (int) round($quoteItem->getDiscountAmount() * 100);
        $description = $quoteItem->getDescription();

        return $this->create(
            $sku,
            $name,
            $type,
            $itemClass,
            $quantity,
            $unitPrice,
            $amount,
            $vatAmount,
            $vatPercent,
            $discountPrice,
            $description
        );
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return OrderItem
     */
    public function createByOrderItem(OrderItemInterface $orderItem)
    {
        $sku = $orderItem->getSku();
        $name = $orderItem->getName();
        $type = 'PRODUCT';
        $itemClass = 'ProductGroup1';
        $quantity = (float) $orderItem->getQtyOrdered();
        $unitPrice = (int) round($orderItem->getPriceInclTax() * 100);
        $amount = (int) round($orderItem->getRowTotalInclTax() * 100);
        $vatAmount = (int) round($orderItem->getTaxAmount() * 100);
        $vatPercent = (int) round($orderItem->getTaxPercent() * 100);
        $discountPrice = (int) round($orderItem->getDiscountAmount() * 100);
        $description = $orderItem->getDescription();

        return $this->create(
            $sku,
            $name,
            $type,
            $itemClass,
            $quantity,
            $unitPrice,
            $amount,
            $vatAmount,
            $vatPercent,
            $discountPrice,
            $description
        );
    }

    /**
     * @param Quote $quote
     * @return OrderItem
     */
    public function createShippingByQuote(Quote $quote)
    {
        $shippingAmount = (int) round($quote->getShippingAddress()->getShippingAmount() * 100);
        $shippingInclTax = (int) round($quote->getShippingAddress()->getShippingInclTax() * 100);
        $shippingTaxAmount = (int) round($quote->getShippingAddress()->getShippingTaxAmount() * 100);
        $shippingTaxPercent = (int) round(($shippingTaxAmount * 100 / $shippingAmount) * 100);

        return $this->create(
            'ShippingFee',
            'Shipping Fee',
            'SHIPPING_FEE',
            'ShippingFee',
            1,
            $shippingInclTax,
            $shippingInclTax,
            $shippingTaxAmount,
            $shippingTaxPercent
        );
    }

    /**
     * @param Order $order
     * @return OrderItem
     */
    public function createShippingByOrder(Order $order)
    {
        $shippingAmount = (int) round($order->getShippingAmount() * 100);
        $shippingInclTax = (int) round($order->getShippingInclTax() * 100);
        $shippingTaxAmount = (int) round($order->getShippingTaxAmount() * 100);
        $shippingTaxPercent = (int) round(($shippingTaxAmount * 100 / $shippingAmount) * 100);

        return $this->create(
            'ShippingFee',
            'Shipping Fee',
            'SHIPPING_FEE',
            'ShippingFee',
            1,
            $shippingInclTax,
            $shippingInclTax,
            $shippingTaxAmount,
            $shippingTaxPercent
        );
    }

    /**
     * @param string $reference
     * @param string $name
     * @param string $type
     * @param string $itemClass
     * @param float $quantity
     * @param int $unitPrice
     * @param int $amount
     * @param int $vatAmount
     * @param int $vatPercent
     * @param int|null $discountPrice
     * @param string|null $description
     * @return OrderItem
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function create(
        string $reference,
        string $name,
        string $type,
        string $itemClass,
        float $quantity,
        int $unitPrice,
        int $amount,
        int $vatAmount,
        int $vatPercent,
        int $discountPrice = null,
        string $description = null
    ) {
        $quantityUnit = $this->getQuantityUnit($quantity);

        if (!$this->isDecimal($quantity)) {
            $quantity = (int) $quantity;
        }

        if ($discountPrice) {
            $amount = $amount - $discountPrice;
        }

        $orderItem = new OrderItem();
        $orderItem
            ->setReference($reference)
            ->setName($name)
            ->setType($type)
            ->setItemClass($itemClass)
            ->setQuantity($quantity)
            ->setQuantityUnit($quantityUnit)
            ->setUnitPrice($unitPrice)
            ->setAmount($amount)
            ->setVatAmount($vatAmount)
            ->setVatPercent($vatPercent);

        if ($description) {
            $orderItem->setDescription($description);
        }

        if ($discountPrice) {
            $orderItem->setDiscountPrice($discountPrice);
        }

        return $orderItem;
    }

    /**
     * @param float $quantity
     * @return string
     */
    protected function getQuantityUnit(float $quantity)
    {
        if ($this->isDecimal($quantity)) {
            return 'grams';
        }

        return 'pcs';
    }

    /**
     * @param $num
     * @return bool
     */
    protected function isDecimal($num)
    {
        return is_numeric($num) && floor($num) != $num;
    }
}
