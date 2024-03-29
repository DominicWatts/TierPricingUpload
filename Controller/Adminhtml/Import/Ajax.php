<?php

namespace Xigen\TierPricingUpload\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Xigen\CsvUpload\Helper\Import as CsvHelper;
use Xigen\TierPricingUpload\Helper\Import as TierHelper;
use Xigen\TierPricingUpload\Model\Import\AdvancedPricing;

/**
 * Ajax controller
 */
class Ajax extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Xigen\TierPricingUpload\Helper\Import
     */
    protected $importHelper;

    /**
     * @var \Xigen\CsvUpload\Helper\Import
     */
    protected $csvImportHelper;

    /**
     * @var Xigen\TierPricingUpload\Model\Import\AdvancedPricing
     */
    protected $tier;

    /**
     * Ajax constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Xigen\TierPricingUpload\Helper\Import $importHelper
     * @param \Xigen\CsvUpload\Helper\Import $csvImportHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        TierHelper $importHelper,
        CsvHelper $csvImportHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->importHelper = $importHelper;
        $this->csvImportHelper = $csvImportHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        try {
            if ($type == 'replace' || $type == 'append') {
                return $this->processTierPricing($type);
            }
            return $this->returnJson('finish', __('Invalid request'), 0);
        } catch (\Exception $e) {
            return $this->returnJson('finish', __('%1 - please check import data', $e->getMessage()), 0);
        }
    }

    /**
     * Import tier pricing process
     * @param string $type
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function processTierPricing($type)
    {
        $imports = $this->csvImportHelper->getImports();

        $processArray = [];
        foreach ($imports as $import) {
            $priceEntry = $this->csvImportHelper->parseImport($import);
            $processArray[$priceEntry['sku']][] = $priceEntry;
        }

        foreach ($processArray as $sku => $tierPricing) {
            $importData = [];
            try {
                foreach ($tierPricing as $tierPrice) {
                    if (!isset($tierPrice['sku'])) {
                        throw new LocalizedException(__('Problem with data'));
                    }
                    $product = $this->importHelper->get($tierPrice['sku']);
                    if (!$product) {
                        $this->csvImportHelper->deleteImportBySku($sku);
                        continue;
                    }
                    $importData = $tierPricing;
                }

                if ($importData) {
                    $this->tier = $this->_objectManager->create(AdvancedPricing::class);
                    $this->tier->saveAdvancedPrices($importData, $type);
                }

                $this->csvImportHelper->deleteImportBySku($sku);

                $collection = $this->csvImportHelper->getImports();
                $collectionSize = $collection->getSize();
                if ($collection->getSize() > 0) {
                    return $this->returnJson(
                        'continue',
                        __(
                            '%1 more %2 price(s) to process',
                            $collectionSize,
                            $type
                        ),
                        $collectionSize
                    );
                }
            } catch (\Exception $e) {
                return $this->returnJson('finish', __('%1 - please check import data', $e->getMessage()), 0);
            }
        }
        return $this->returnJson('finish', 'Process complete', 0);
    }

    /**
     * Return Json response
     * @param string $action
     * @param string $message
     * @param int $process
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function returnJson($action, $message, $process)
    {
        $result = $this->resultJsonFactory->create();
        $data = [
            'action' => $action,
            'message' => $message,
            'process' => $process,
        ];
        return $result->setData($data);
    }
}
