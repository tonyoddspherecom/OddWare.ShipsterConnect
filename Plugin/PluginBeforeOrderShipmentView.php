<?php
/**
 * Created by OddWare.
 * User: tonycheetham
 * Date: 03.07.2017
 * Time: 10:13
 */
\Magento\Sales\Block\Items\AbstractItems
namespace OddWare\ShipsterConnect\Plugin;

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

    public function afterPrepareDataSource(\Magento\Sales\Ui\Component\Listing\Column\ViewAction $subject, array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');

            foreach ($dataSource['data']['items'] as &$item) {
            	$url2 = $this->_urlBuilder->getUrl('shipsterconnect/xml/generate', ['order_id' => $subject->getShipment()->getOrder()->getId()]);
            	$item[$subject->getData('name')]['Send To Shipster'] = [
                    'href' => $url2,
                    'label' => __('Send To Shipster'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}