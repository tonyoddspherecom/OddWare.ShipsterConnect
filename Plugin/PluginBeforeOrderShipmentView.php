<?php
/**
 * Created by OddWare.
 * User: tonycheetham
 * Date: 03.07.2017
 * Time: 10:13
 */

namespace OddWare\ShipsterConnect\Plugin;

use Magento\Framework\UrlInterface;

class PluginBeforeOrderShipmentView
{
    protected $context;
    protected $urlBuilder;
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder
    )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
    }

    public function afterPrepareDataSource(\Magento\Shipping\Ui\Component\Listing\Columns\ViewAction $subject, array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
                $item[$subject->getData('name')]['do_something'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'catalog/product/do_something',
                        ['id' => $item['entity_id'], 'store' => $storeId]
                    ),
                    'label' => __('Do Something'),
                    'hidden' => false,
                ];
                $item[$subject->getData('name')]['do_something_else'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'catalog/product/do_something_else',
                        ['id' => $item['entity_id'], 'store' => $storeId]
                    ),
                    'label' => __('Do Something else'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;

        $url2 = $this->_urlBuilder->getUrl('shipsterconnect/xml/generate', ['order_id' => $subject->getShipment()->getOrder()->getId()]);

        $message = 'An export file will be generated, please save it to downloads.';

        // add button to order view with, url in button to generate .pvx xml
        $subject->addButton(
            'generateshipxml',
            [
                'label' => __('Send to Shipster'),
                'class' => 'xml-button, ship',
                'onclick' => "confirmSetLocation('{$message}', '{$url2}')"
            ]
        );
    }
}