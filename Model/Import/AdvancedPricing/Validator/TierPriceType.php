<?php

// phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod

namespace Xigen\TierPricingUpload\Model\Import\AdvancedPricing\Validator;

use Xigen\TierPricingUpload\Model\Import\AdvancedPricing;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

/**
 * Class TierPriceType validates tier price type.
 */
class TierPriceType extends \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator
{
    /**
     * {@inheritdoc}
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * Validate tier price type.
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        if (isset($value[AdvancedPricing::COL_TIER_PRICE_TYPE])
            && !empty($value[AdvancedPricing::COL_TIER_PRICE_TYPE])
            && !in_array(
                $value[AdvancedPricing::COL_TIER_PRICE_TYPE],
                [AdvancedPricing::TIER_PRICE_TYPE_FIXED, AdvancedPricing::TIER_PRICE_TYPE_PERCENT]
            )
        ) {
            $this->_addMessages([RowValidatorInterface::ERROR_INVALID_TIER_PRICE_TYPE]);
            $isValid = false;
        }

        return $isValid;
    }
}
