<?php
namespace Imgix\Magento\Helper\ConfigurableProduct;

use Magento\ConfigurableProduct\Helper\Data as MainHelper;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image;
use Imgix\Magento\Helper\Data as ImgixHelper;

class Data extends MainHelper
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @var ImgixHelper
     */
    protected $imgixHelper;

    /**
     * @param ImageHelper $imageHelper
     * @param UrlBuilder $urlBuilder
     * @param ImgixHelper $imgixHelper
     */
    public function __construct(
        ImageHelper $imageHelper,
        UrlBuilder $urlBuilder = null,
        ImgixHelper $imgixHelper
    ) {
        $this->imageHelper = $imageHelper;
        $this->imageUrlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->imgixHelper = $imgixHelper;
        parent::__construct($imageHelper, $urlBuilder);
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param ProductInterface $product
     * @return Image[]|null
     */
    public function getGalleryImages(ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();

        if ($images instanceof \Magento\Framework\Data\Collection) {
            /** @var $image Image */
            foreach ($images as $image) {
                if (strpos((string) $image->getFile(), 'imgix') !== false) {

                    $defaultImageOptions = $this->imgixHelper->getDefaultImageOptions();
                    $thumbnailImageOptions = $this->imgixHelper->getThumbnailImageOptions();

                    if (!empty($defaultImageOptions)
                        && ($defaultImageOptions !='')
                        && ($this->imgixHelper->isEnabled())
                    ) {
                        $defaultImageOptions = '?'.$defaultImageOptions;
                    } else {
                        $defaultImageOptions = '';
                    }

                    if (!empty($thumbnailImageOptions)
                        && ($thumbnailImageOptions !='')
                        && ($this->imgixHelper->isEnabled())
                    ) {
                        $thumbnailImageOptions = '?'.$thumbnailImageOptions;
                    } else {
                        $thumbnailImageOptions = '';
                    }

                    $smallImageUrl = $image->getFile().$thumbnailImageOptions;
                    $mediumImageUrl = $image->getFile().$defaultImageOptions;
                    $largeImageUrl = $image->getFile().$defaultImageOptions;

                } else {
                    $smallImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_small');

                    $mediumImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_medium');

                    $largeImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_large');
                }
                
                $image->setData('small_image_url', $smallImageUrl);
                
                $image->setData('medium_image_url', $mediumImageUrl);

                $image->setData('large_image_url', $largeImageUrl);
            }
        }
        
        return $images;
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {
        $options = [];
        $allowAttributes = $this->getAllowAttributes($currentProduct);

        foreach ($allowedProducts as $product) {
            $productId = $product->getId();
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if ($product->isSalable()) {
                    $options[$productAttributeId][$attributeValue][] = $productId;
                }
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        return $options;
    }

    /**
     * Get allowed attributes
     *
     * @param Product $product
     * @return array
     */
    public function getAllowAttributes($product)
    {
        return ($product->getTypeId() == Configurable::TYPE_CODE)
            ? $product->getTypeInstance()->getConfigurableAttributes($product)
            : [];
    }
}
