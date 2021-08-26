<?php

namespace Xigen\TierPricingUpload\Block\Adminhtml\Import;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Xigen\CsvUpload\Model\ImportFactory;

/**
 * Index block class
 */
class Index extends Template
{
    /**
     * @var \Xigen\CsvUpload\Model\ImportFactory
     */
    protected $importFactory;

    /**
     * Index constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Xigen\CsvUpload\Model\ImportFactory $importFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ImportFactory $importFactory,
        array $data = []
    ) {
        $this->importFactory = $importFactory;
        parent::__construct($context, $data);
    }

    /**
     * Are imports
     * @return void
     */
    public function isImports()
    {
        $importCollection = $this->importFactory
            ->create()
            ->getCollection();
        if ($importCollection && $importCollection->getSize() > 0) {
            return true;
        }
        return false;
    }
}
