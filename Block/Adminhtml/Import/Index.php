<?php


namespace Xigen\TierPricingUpload\Block\Adminhtml\Import;

/**
 * Index block class
 */
class Index extends \Magento\Backend\Block\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Xigen\CsvUpload\Model\ImportFactory $importFactory,
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
        $importCollection = $this->importFactory->create()
            ->getCollection();
        if ($importCollection && $importCollection->getSize() > 0) {
            return true;
        }
        return false;
    }
}
