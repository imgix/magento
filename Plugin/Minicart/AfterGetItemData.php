<?php
declare(strict_types=1);
namespace Imgix\Magento\Plugin\Minicart;

use Magento\Checkout\CustomerData\AbstractItem;
use Imgix\Magento\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AfterGetItemData
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Product
     */
    private $product;

    /**
     * @param Data $helperData
     * @param ProductRepositoryInterface $productData
     */
    public function __construct(
        Data $helperData,
        ProductRepositoryInterface $productData
    ) {
        $this->helperData = $helperData;
        $this->productData = $productData;
    }

    /**
     * Get item data
     *
     * @param AbstractItem $item
     * @param mixed $result
     * @return mixed
     */
    public function afterGetItemData(AbstractItem $item, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        
        $product = $this->productData->getById($result['product_id']);
        try {
            if (strpos((string) $product->getThumbnail(), 'imgix') !== false) {
                if ($result['product_id'] > 0) {
                    $thumbnailImage = $this->helperData->getThumbnailImageOptions();
                    $result['product_image']['src'] = $product->getThumbnail().'?'.$thumbnailImage;
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        return $result;
    }
}
