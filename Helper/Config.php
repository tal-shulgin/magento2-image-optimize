<?php

declare(strict_types=1);

namespace Mageit\ImageOptimize\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends AbstractHelper
{
    public const XML_CONFIG_PATH = 'mageitimageoptimizer/%s/%s';

    /**
     * @param Context               $context
     * @param StoreManagerInterface $_storeManager
     * @param Json                  $serialize
     */
    public function __construct(
        protected Context $context,
        protected StoreManagerInterface $_storeManager,
        protected Json $serialize
    ) {
        parent::__construct($context);
    }

    /**
     * Gets the path for system.xml field.
     * @param string $section
     * @param string $field
     *
     * @return string
     */
    protected function getPath(string $section, string $field): string
    {
        return sprintf(self::XML_CONFIG_PATH, $section, $field);
    }

    /**
     * Gets the config.
     *
     * @param string          $path
     * @param string|int|null $storeId
     *
     * @return mixed
     */
    public function getConfig(string $path, string|int|null $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $path
     * @param int|string|null $storeId
     *
     * @return bool
     */
    public function isFlagConfig(string $path, int|string|null $storeId = null): bool
    {
        return (boolean)$this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /***
     * is module enabled.
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->isFlagConfig(
            $this->getPath('general', 'enabled'),
        );
    }

    /***
     * Is backup_image enabled.
     *
     * @return boolean
     */
    public function isBackupEnabled(): bool
    {
        return $this->isFlagConfig(
            $this->getPath('general', 'backup_image'),
        );
    }

    /**
     * Gets image quality.
     * @return mixed
     */
    public function getImageQuality(): mixed
    {
        return $this->getConfig(
            $this->getPath('optimize_options', 'image_quality')
        );
    }

    /**
     * Gets custom quality percent.
     * @return mixed
     */
    public function getQualityPercent(): mixed
    {
        return $this->getOptimizeOptions('quality_percent');
    }

    /**
     * Is permission forced.
     * @return bool
     */
    public function isForcePermission(): bool
    {
        return $this->isFlagConfig(
            $this->getPath('optimize_options', 'force_permission')
        );
    }

    /**
     * Gets the selected permission.
     * @return mixed
     */
    public function getSelectPermission(): mixed
    {
        return $this->getOptimizeOptions('select_permission');
    }

    /**
     * Gets field value from optimize_options group in system.xml.
     * @param string $field
     *
     * @return mixed
     */
    public function getOptimizeOptions(string $field): mixed
    {
        return $this->getConfig(
            $this->getPath('optimize_options', $field)
        );
    }

    /**
     * Gets array of included directories provided to scan.
     * @return array|null
     */
    public function getIncludeDirectories(): ?array
    {
        $tableConfig = $this->getConfig(
            $this->getPath('image_directory', 'include_directories')
        );

        if ($tableConfig == '' || $tableConfig == null) {
            return null;
        }

        $tableConfig = $this->serialize->unserialize($tableConfig);

        $tableConfigArray = [];
        foreach ($tableConfig as $key => $row) {
            $tableConfigArray[$key] = $row['path'];
        }

        return $tableConfigArray;
    }

    /**
     * Gets array of excluded directories provided to scan.
     * @return array
     */
    public function getExcludeDirectories(): array
    {
        $tableConfig = $this->getConfig(
            $this->getPath('image_directory', 'exclude_directories')
        );

        if ($tableConfig == '' || $tableConfig == null) {
            return [];
        }

        $tableConfig = $this->serialize->unserialize($tableConfig);

        $tableConfigArray = [];
        foreach ($tableConfig as $key => $row) {
            $tableConfigArray[$key] = $row['path'];
        }

        return $tableConfigArray;
    }

    /**
     * Is scan cron enabled.
     * @return bool
     */
    public function isScanCronEnabled(): bool
    {
        return $this->isFlagConfig(
            $this->getPath('cron_job', 'enabled_scan')
        );
    }

    /**
     * Is optimize cron enabled.
     * @return bool
     */
    public function isOptimizeCronEnabled(): bool
    {
        return $this->isFlagConfig(
            $this->getPath('cron_job', 'enabled_optimize')
        );
    }

    /**
     * Gets field value from cron_job group in system.xml.
     * @param string $code
     *
     * @return mixed
     */
    public function getCronJobConfig(string $code = ''): mixed
    {
        return $this->getConfig(
            $this->getPath('cron_job', $code)
        );
    }

    /**
     * Is use custom paths for optimizers.
     * @return bool
     */
    public function isUseCustomPaths(): bool
    {
        return $this->getOptimizeOptions('use_custom_path') ?? false;
    }
}
