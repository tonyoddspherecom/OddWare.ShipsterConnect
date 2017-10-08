<?php
/**
 * Created by OddWare.
 * User: tonycheetham
 * Date: 03.07.2017
 * Time: 10:13
 */

namespace OddWare\ShipsterConnect\Plugin;

use Magento\Framework\UrlInterface;

class PluginBeforeYourView
{
    protected $_urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    )
    {
        $this->_urlBuilder = $urlBuilder;
    }

    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $subject)
    {

        $url2 = $this->_urlBuilder->getUrl('oddwareshipsterconnect/xml/generate', ['order_id' => $subject->getOrderId()]);

        $message = 'The .pvx xml file will be generated from current order.';

        // add button to order view with, url in button to generate .pvx xml
        $subject->addButton(
            'generateshipxml',
            [
                'label' => __('Generate .pvx file'),
                'class' => 'xml-button, ship',
                'onclick' => "confirmSetLocation('{$message}', '{$url2}')"
            ]
        );

    }
}