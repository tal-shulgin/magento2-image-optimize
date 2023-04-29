<?php

namespace Mageit\ImageOptimize\Model\ResourceModel;

use Exception;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Image extends AbstractDb
{
    /**
     * Insert image data
     *
     * @param $data
     */
    public function insertImagesData($data)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $connection->insertMultiple($this->getMainTable(), $data);
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
        }
    }

    /** @SuppressWarnings(PHPMD.CamelCaseMethodName) */
    protected function _construct()
    {
        $this->_init('mageit_image_optimizer', 'image_id');
    }
}
