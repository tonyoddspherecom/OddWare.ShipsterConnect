<?php
/**
 * Created by OddWare.
 * User: tonycheetham
 * Date: 03.07.2017
 * Time: 10:13
 */
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
        $this->_urlBuilder = $urlBuilder;
    }

    public function afterPrepareDataSource(\Magento\Sales\Ui\Component\Listing\Column\ViewAction $subject, array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
            	$url2 = $this->_urlBuilder->getUrl('shipsterconnect/xml/generate', ['order_id' => $item['entity_id']]);
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