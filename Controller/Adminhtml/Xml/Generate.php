<?php

namespace OddWare\ShipsterConnect\Controller\Adminhtml\Xml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\RedirectFactory;

class Generate extends Action
{

    protected $_order;

    protected $_rawResultFactory;

    protected $_products;

    protected $_redirectResultFactory;

    public function __construct(
        OrderInterface $order,
        RawFactory $rawResultFactory,
        RedirectFactory $redirectFactory,
        Context $context
    )
    {
        parent::__construct($context);
        $this->_redirectResultFactory = $redirectFactory;
        $this->_rawResultFactory = $rawResultFactory;
        $this->_order = $order;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if ($orderId) {
            $order = $this->_order->load($orderId);
            $shippingAddress = $order->getShippingAddress(); // shipping address
            $billingAddress = $order->getBillingAddress(); // shipping address

            $shipXML = new \SimpleXMLElement("<Despatch></Despatch>");
            $shipXML->addAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
            $shipXML->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $shipXML->addChild('CustomerName', $order->getData('customer_firstname') . ' ' . $order->getData('customer_lastname'));
            $shipXML->addChild('CustomerEmail', $order->getData('customer_email'));
            $carrierName = $shipXML->addChild('CarrierName', $order->getData('shipping_description'));
            $shipXML->addChild('ChannelName', 'Magento');
            $shipXML->addChild('ServiceTypeName', $order->getData('shipping_description'));
            $shipXML->addChild('ServiceTypeCode', $order->getData('shipping_method'));
            $shipXML->addChild('SalesOrderNumber', $order->getData('increment_id'));
            $shipXML->addChild('OrderStatus', $order->getData('status'));
            $shipXML->addChild('CustomerPurchaseOrderReferenceNumber', $order->getData('increment_id'));
            $shipXML->addChild('RequestedDeliveryDate', '');
            $shipXML->addChild('ShippingCost', $order->getData('shipping_amount'));
            $shipXML->addChild('CustomerReference', $order->getCustomerId());

            $shipXML->addChild('CustomerPhone', $shippingAddress->getTelephone());
            $shipXML->addChild('TotalSale', $order->getData('grand_total'));
            $shipXML->addChild('Discount', $order->getData('discount_amount'));
            $shipXML->addChild('TaxPaid', $order->getData('customer_taxvat'));
            $shipXML->addChild('CreatedDate', $order->getData('created_at'));
            $shipXML->addChild('OnHold', $order->getData('status') == 'hold' ? true : false);

            $shipXML->addChild('InvoiceAddressLine1', $billingAddress->getData('street'));
            $shipXML->addChild('InvoiceAddressLine2', '');
            $shipXML->addChild('InvoiceAddressRegion', $billingAddress->getData('region'));
            $shipXML->addChild('InvoiceAddressTownCity', $billingAddress->getData('city'));
            $shipXML->addChild('InvoiceAddressPostCode', $billingAddress->getData('postcode'));
            $shipXML->addChild('InvoiceAddressCountry', $billingAddress->getData('country_id'));

            $shipXML->addChild('ShippingAddressLine1', $shippingAddress->getData('street'));
            $shipXML->addChild('ShippingAddressLine2', '');
            $shipXML->addChild('ShippingAddressTownCity', $shippingAddress->getData('city'));
            $shipXML->addChild('ShippingAddressRegion', $shippingAddress->getData('region'));
            $shipXML->addChild('ShippingAddressPostCode', $shippingAddress->getData('postcode'));
            $shipXML->addChild('ShippingAddressCountry', $shippingAddress->getData('country_id'));

            $shipXML->addChild('Priority', '');

            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();

            $shipXML->addChild('PaymentMethod', $method->getTitle());

            $shipXML->addChild('CreatedBy', '');


            $orderItems = $order->getAllItems();
            $items = $shipXML->addChild('Items');

            foreach ($orderItems as $item) {

                $product = $item->getProduct();
                $categoryIds = $product->getData('category_ids');

                $this->_products[$item->getSku()] = $product;

                if (count($categoryIds) > 1) {
                    $categoryId = $categoryIds[0] == 2 ? $categoryIds[1] : $categoryIds[0];
                } else if (count($categoryIds) == 1) {
                    $categoryId = $categoryIds[0];
                } else {
                    $categoryId = null;
                }

                $xmlItem = $items->addChild('Item');
                $xmlItem->addChild('Name', html_entity_decode($item->getData('name')));
                $xmlItem->addChild('Description', $item->getData('description'));
                $xmlItem->addChild('ItemCode', $item->getData('sku'));
                $xmlItem->addChild('ParentItemCode', '');
                $xmlItem->addChild('DefaultSuppliersPartNumber', '');
                $xmlItem->addChild('Barcode', $item->getData('sku'));
                $xmlItem->addChild('BuyPrice', $item->getData('price'));
                $xmlItem->addChild('RetailPrice', $item->getData('price'));
                $xmlItem->addChild('Weight', $item->getData('weight'));

                if ($categoryId) {
                    $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $category = $_objectManager->create('Magento\Catalog\Model\Category')
                                               ->load($categoryId);

                    $xmlItem->addChild('ItemGroupName', $category->getName());
                }


                $xmlItem->addChild('SalePrice', $item->getData('price'));
                $xmlItem->addChild('QuantityOrdered', $item->getData('qty_ordered'));

            }

            $shipments = $order->getShipmentsCollection();


            $shipXML->addChild('NumberOfPackages', $shipments->count());

            if ($shipments->count() > 0) {


                $packages = $shipXML->addChild('Packages');

                foreach ($shipments as $shipment) {

                    $shipXML->addChild('DespatchNumber', $shipment->getIncrementId());

                    $package = $packages->addChild('DespatchPackage');

                    $package->addChild('CustomerName', $order->getData('customer_firstname') . ' ' . $order->getData('customer_lastname'));
                    $package->addChild('CustomerEmail', $order->getData('customer_email'));

                    if (count($shipment->getAllTracks()) == 0) {
                        $package->addChild('CarrierName', $order->getData('shipping_description'));
                        $package->addChild('ServiceTypeName', $order->getData('shipping_description'));
                        $package->addChild('ServiceTypeCode', $order->getData('shipping_method'));
                    }

                    $package->addChild('SalesOrderNumber', $order->getData('increment_id'));
                    $package->addChild('OrderStatus', $order->getData('status'));
                    $package->addChild('CustomerPurchaseOrderReferenceNumber', $order->getData('increment_id'));

                    $package->addChild('RequestedDeliveryDate', '');
                    $package->addChild('ShippingCost', $order->getData('shipping_amount'));
                    $package->addChild('CustomerReference', $order->getCustomerId());

                    $package->addChild('CustomerPhone', $shippingAddress->getTelephone());
                    $package->addChild('TotalSale', $order->getData('grand_total'));

                    $package->addChild('Discount', $order->getData('discount_amount'));
                    $package->addChild('TaxPaid', $order->getData('customer_taxvat'));
                    $package->addChild('CreatedDate', $shipment->getData('created_at'));
                    $package->addChild('OnHold', $order->getData('status') == 'hold' ? true : false);


                    $package->addChild('InvoiceAddressLine1', $billingAddress->getData('street'));
                    $package->addChild('InvoiceAddressLine2', '');
                    $package->addChild('InvoiceAddressRegion', $billingAddress->getData('region'));
                    $package->addChild('InvoiceAddressTownCity', $billingAddress->getData('city'));
                    $package->addChild('InvoiceAddressPostCode', $billingAddress->getData('postcode'));
                    $package->addChild('InvoiceAddressCountry', $billingAddress->getData('country_id'));

                    $package->addChild('ShippingAddressLine1', $shippingAddress->getData('street'));
                    $package->addChild('ShippingAddressLine2', '');
                    $package->addChild('ShippingAddressTownCity', $shippingAddress->getData('city'));
                    $package->addChild('ShippingAddressRegion', $shippingAddress->getData('region'));
                    $package->addChild('ShippingAddressPostCode', $shippingAddress->getData('postcode'));
                    $package->addChild('ShippingAddressCountry', $shippingAddress->getData('country_id'));

                    $package->addChild('PaymentMethod', $method->getTitle());

                    $items = $package->addChild('Items');

                    foreach ($shipment->getItems() as $item) {

                        $product = $this->_products[$item->getSku()];

                        $categoryIds = $product->getData('category_ids');

                        if (count($categoryIds) > 1) {
                            $categoryId = $categoryIds[0] == 2 ? $categoryIds[1] : $categoryIds[0];
                        } else if (count($categoryIds) == 1) {
                            $categoryId = $categoryIds[0];
                        } else {
                            $categoryId = null;
                        }

                        $xmlItem = $items->addChild('Item');
                        $xmlItem->addChild('Name', html_entity_decode($item->getData('name')));
                        $xmlItem->addChild('Description', $item->getData('description'));
                        $xmlItem->addChild('ItemCode', $item->getData('sku'));
                        $xmlItem->addChild('ParentItemCode', '');
                        $xmlItem->addChild('DefaultSuppliersPartNumber', '');
                        $xmlItem->addChild('Barcode', $item->getData('sku'));
                        $xmlItem->addChild('BuyPrice', $item->getData('price'));
                        $xmlItem->addChild('RetailPrice', $item->getData('price'));
                        $xmlItem->addChild('Weight', $item->getData('weight'));

                        if ($categoryId) {
                            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                            $category = $_objectManager->create('Magento\Catalog\Model\Category')
                                                       ->load($categoryId);

                            $xmlItem->addChild('ItemGroupName', $category->getName());
                        }


                        $xmlItem->addChild('SalePrice', $item->getData('price'));
                        $xmlItem->addChild('QuantityOrdered', $item->getData('qty'));

                    }

                    $package->addChild('DespatchNumber', $shipment->getIncrementId());
                    $package->addChild('NumberOfPackages', 1);
                    $package->addChild('DespatchDate', 1);
                    $package->addChild('Weight', $shipment->getData('total_weight'));


                    if (count($shipment->getAllTracks()) > 0) {
                        foreach ($shipment->getAllTracks() as $tracknum) {

                            $carrierName[0][0] = $tracknum->getData('title');

                            $package->addChild('CarrierName', $tracknum->getData('title'));
                            $package->addChild('ServiceTypeName', $tracknum->getData('title'));
                            $package->addChild('ServiceTypeCode', $tracknum->getData('carrier_code'));
                            $package->addChild('PackageTrackingNumber', $tracknum->getNumber());
                        }
                    }

                }

            }

            // create result object, set xml types and return to browser as file to download
            $result = $this->_rawResultFactory->create();
            $result->setHeader('Content-Type', 'text/xml');
            $result->setHeader('Content-Disposition', 'attachment;filename="order_' . $orderId . '.pvx"');
            $result->setContents($shipXML->asXML());
            return $result;
        }

        $this->messageManager->addErrorMessage('Something went wrong with your xml. ID was ' . $orderId);
        $resultRedirect = $this->_redirectResultFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}