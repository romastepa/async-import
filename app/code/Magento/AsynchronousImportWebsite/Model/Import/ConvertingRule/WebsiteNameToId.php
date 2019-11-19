<?php
declare(strict_types=1);

namespace Magento\AsynchronousImportWebsite\Model\Import\ConvertingRule;

use Magento\AsynchronousImportDataConvertingApi\Api\Data\ConvertingRuleInterface;
use Magento\AsynchronousImportDataConvertingApi\Model\ApplyConvertingRuleStrategyInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Converts WebsiteName to Website Id
 */
class WebsiteNameToId implements ApplyConvertingRuleStrategyInterface
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
    protected $websiteNameToId;

    /**
     * WebsiteNameToId constructor.
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
     * Takes apply_to columns and converts values WebsiteName to WebsiteId.
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
     * Gets WebsiteId by WebsiteName
     *
     * @param $websiteName
     * @param $column
     *
     * @return int
     */
    protected function getWebsiteId($websiteName, $column)
    {
        if (!isset($this->websiteNameToId[$websiteName])) {
            throw new NotFoundException(__(
                'The converting rule apply_to cannot be applied to the column: "%column". WebsiteName "%code%" not exists',
                [
                    'column' => $column,
                    'code' => $websiteName
                ]
            ));
        }

        return $this->websiteNameToId[$websiteName];
    }

    /**
     * Initialize websites hash.
     *
     * @return $this
     */
    protected function initWebsites()
    {
        foreach ($this->storeManager->getWebsites(true) as $website) {
            $this->websiteNameToId[$website->getName()] = $website->getId();
        }
        return $this;
    }
}
