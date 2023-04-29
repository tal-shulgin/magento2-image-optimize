<?php

namespace  Mageit\ImageOptimize\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class SizeColumns extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getName();
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName] = isset($item[$fieldName]) ? round($item[$fieldName] / 1024) . ' KB' : '';
            }
        }

        return $dataSource;
    }
}
