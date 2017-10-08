<?php
/**
 * Created by OddWare.
 * User: tonycheetham
 * Date: 03.07.2017
 * Time: 10:13
 */

namespace OddWare\ShipsterConnect\Ui\Component\Listing\Column\Order;

class PluginBeforeOrderShipmentView extends Column {

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $viewUrlPath = $this->getData('config/viewUrlPath') ? : '#';
                $urlEntityParamName = $this->getData('config/urlEntityParamName') ? : 'entity_id';

                $item[$this->getData('name')] = [
                    'ModuleName' => [
                        'href' => $this->context->getUrl(
                                $viewUrlPath, [$urlEntityParamName => $item['entity_id']]
                        ),
                        'label' => 'Print',
                    ]
                ];
            }
        }

        return $dataSource;
    }

}