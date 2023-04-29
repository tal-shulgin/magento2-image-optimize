<?php

namespace Mageit\ImageOptimize\Service\Optimizers;

use Mageit\ImageOptimize\Service\Optimizer;

abstract class BaseOptimizer implements Optimizer
{
    public array $options = [];
    public string $imagePath = '';
    public string $binaryPath = '';
    protected string $binaryName;

    public function __construct($options = [], $binaryName = null)
    {
        $this->setOptions($options);
        if (!empty($binaryName)) {
            $this->setBinaryName($binaryName);
        }
    }

    public function binaryName(): string
    {
        return $this->binaryName;
    }

    public function setBinaryName(string $binaryName)
    {
        $this->binaryName = $binaryName;
    }

    public function setBinaryPath(string $binaryPath): static
    {
        if (strlen($binaryPath) > 0 && substr($binaryPath, -1) !== DIRECTORY_SEPARATOR) {
            $binaryPath = $binaryPath . DIRECTORY_SEPARATOR;
        }

        $this->binaryPath = $binaryPath;

        return $this;
    }

    public function setImagePath(string $imagePath): BaseOptimizer|static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function setOptions(array $options = []): BaseOptimizer|static
    {
        $this->options = $options;

        return $this;
    }

    public function getCommand(): string
    {
        $optionString = implode(' ', $this->options);

        return "\"{$this->binaryPath}{$this->binaryName}\" {$optionString} " . escapeshellarg($this->imagePath);
    }
}
