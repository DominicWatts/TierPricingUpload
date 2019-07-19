<?php

namespace Xigen\TierPricingUpload\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Import helper class
 */
class Import extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productInterfaceFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\GroupFactory $groupFactory
    ) {
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->logger = $logger;
        $this->groupFactory = $groupFactory;
        parent::__construct($context);
    }

    /**
     * Get product by SKU
     * @return \Magento\Catalog\Model\Data\Product
     */
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false)
    {
        try {
            return $this->productRepositoryInterface->get($sku, $editMode, $storeId, $forceReload);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * Get customer group by code
     * @param string $groupCode
     * @return \Magento\Customer\Model\Group
     */
    public function loadCustomerGroupByCode($groupCode)
    {
        return $this->groupFactory->create()
            ->load($groupCode, 'customer_group_code');
    }
}
