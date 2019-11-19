<?php
declare(strict_types=1);

namespace Magento\AsynchronousImportWebsite\Model\Import\ConvertingRule;

use Magento\AsynchronousImportDataConvertingApi\Api\Data\ConvertingRuleInterface;
use Magento\AsynchronousImportDataConvertingApi\Model\ApplyConvertingRuleStrategyInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Converts WebsiteCode to Website Id
 */
class WebsiteCodeToId implements ApplyConvertingRuleStrategyInterface
{
    /**
     * Store manager instance.
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $websiteCodeToId;

    /**
     * WebsiteCodeToId constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Executes converting rule
     *
     * Takes apply_to columns and converts values websiteCode to WebsiteId.
     *
     * @param array $importData
     * @param ConvertingRuleInterface $convertingRule
     * @return array
     */
    public function execute(
        array $importData,
        ConvertingRuleInterface $convertingRule
    ): array {
        $applyTo = $convertingRule->getApplyTo();

        $this->initWebsites();

        foreach ($importData as &$row) {
            foreach ($applyTo as $columnName) {
                if (isset($row[$columnName])) {
                    $row[$columnName] = $this->getWebsiteId($row[$columnName], $columnName);
                }
            }
        }
        unset($row);

        return $importData;
    }

    /**
     * Gets WebsiteId by WebsiteCode
     *
     * @param $websiteCode
     * @param $column
     *
     * @return int
     */
    protected function getWebsiteId($websiteCode, $column)
    {
        if (!isset($this->websiteCodeToId[$websiteCode])) {
            throw new NotFoundException(__(
                'The converting rule apply_to cannot be applied to the column: "%column". WebsiteCode "%code%" not exists',
                [
                    'column' => $column,
                    'code' => $websiteCode
                ]
            ));
        }

        return $this->websiteCodeToId[$websiteCode];
    }

    /**
     * Initialize websites hash.
     *
     * @return $this
     */
    protected function initWebsites()
    {
        foreach ($this->storeManager->getWebsites(true) as $website) {
            $this->websiteCodeToId[$website->getCode()] = $website->getId();
        }
        return $this;
    }
}
