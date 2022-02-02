<?php
namespace Imgix\Magento\Block\Product;
 
use Magento\Catalog\Block\Product\Image as ImageBlock;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\ConfigInterface;
use Imgix\Magento\Helper\Data;
use Imgix\UrlBuilder;

class ImageFactory extends \Magento\Catalog\Block\Product\ImageFactory
{
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;
 
    /**
     * @var AssetImageFactory
     */
    private $viewAssetImageFactory;
 
    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;
 
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
 
    /**
     * @var PlaceholderFactory
     */
    private $viewAssetPlaceholderFactory;
    
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface $presentationConfig
     * @param AssetImageFactory $viewAssetImageFactory
     * @param PlaceholderFactory $viewAssetPlaceholderFactory
     * @param ParamsBuilder $imageParamsBuilder
     * @param Data $helperData
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $presentationConfig,
        AssetImageFactory $viewAssetImageFactory,
        PlaceholderFactory $viewAssetPlaceholderFactory,
        ParamsBuilder $imageParamsBuilder,
        Data $helperData
    ) {
        $this->objectManager = $objectManager;
        $this->presentationConfig = $presentationConfig;
        $this->viewAssetPlaceholderFactory = $viewAssetPlaceholderFactory;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->helperData = $helperData;
        parent::__construct(
            $objectManager,
            $presentationConfig,
            $viewAssetImageFactory,
            $viewAssetPlaceholderFactory,
            $imageParamsBuilder
        );
    }

    /**
     * Remove class from custom attributes
     *
     * @param array $attributes
     * @return array
     */
    private function filterCustomAttributes(array $attributes): array
    {
        if (isset($attributes['class'])) {
            unset($attributes['class']);
        }
        return $attributes;
    }

    /**
     * Retrieve image class for HTML element
     *
     * @param array $attributes
     * @return string
     */
    private function getClass(array $attributes): string
    {
        return $attributes['class'] ?? 'product-image-photo';
    }

    /**
     * Calculate image ratio
     *
     * @param int $width
     * @param int $height
     * @return float
     */
    private function getRatio(int $width, int $height): float
    {
        if ($width && $height) {
            return $height / $width;
        }
        return 1.0;
    }

    /**
     * Get image label
     *
     * @param Product $product
     * @param string $imageType
     * @return string
     */
    private function getLabel(Product $product, string $imageType): string
    {
        $label = $product->getData($imageType . '_' . 'label');
        if (empty($label)) {
            $label = $product->getName();
        }
        return (string)$label;
    }
    
    /**
     * Create
     *
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     * @return ImageBlock
     */
    public function create(Product $product, string $imageId, array $attributes = null): ImageBlock
    {
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );
        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);
        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type']
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $originalFilePath,
                ]
            );
        }
        $attributes = $attributes === null ? [] : $attributes;
        if (strpos($originalFilePath, 'imgix') !== false) {
            $smallImage = $this->helperData->getSmallImageOptions();

            $url = $originalFilePath;
            
            // Remove https:// from url
            $imgix_subdomain = substr($url, 8, strrpos($url, 'imgix.net/')+1);
        
            $origin_path = substr($url, strpos($url, "imgix.net/") + 9);
        
            $builder = new UrlBuilder($imgix_subdomain, true, "", false);

            $optionVal =  $options = $params = [] ;
            if (!empty($smallImage)) {
                $options = explode("&", $smallImage);
                if (!empty($options) && is_array($options) && isset($options)) {
                    foreach ($options as $value) {
                        $optionVal = explode("=", $value);
                        if (!empty($optionVal) && is_array($optionVal) && isset($optionVal[1])) {
                            $params [$optionVal[0]] = $optionVal[1] ;
                        }
                    }
                }
            }
            $moduleEnable = $this->helperData->isEnabled();
            if ((!empty($params)) && (is_array($params)) && (!empty($smallImage)) && ($moduleEnable)) {
                $srcset = $builder->createSrcSet($origin_path, $params);
            } else {
                $srcset = $builder->createSrcSet($origin_path);
            }

            $data = [
                'data' => [
                    'template' => 'Imgix_Magento::product/image_with_borders.phtml',
                    'image_url' => $originalFilePath.'?'.$smallImage,
                    'width' => $imageMiscParams['image_width'],
                    'height' => $imageMiscParams['image_height'],
                    'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                    'ratio' => $this->getRatio(
                        $imageMiscParams['image_width'] ?? 0,
                        $imageMiscParams['image_height'] ?? 0
                    ),
                    'custom_attributes' => $this->filterCustomAttributes($attributes),
                    'class' => $this->getClass($attributes),
                    'product_id' => $product->getId(),
                    'product_type' => $product->getTypeId(),
                    'image_srcset' => $srcset
                ],
            ];
        } else {
            $data = [
                'data' => [
                    'template' => 'Magento_Catalog::product/image_with_borders.phtml',
                    'image_url' => $imageAsset->getUrl(),
                    'width' => $imageMiscParams['image_width'],
                    'height' => $imageMiscParams['image_height'],
                    'label' => $this->getLabel($product, $imageMiscParams['image_type']),
                    'ratio' => $this->getRatio(
                        $imageMiscParams['image_width'] ?? 0,
                        $imageMiscParams['image_height'] ?? 0
                    ),
                    'custom_attributes' => $this->filterCustomAttributes($attributes),
                    'class' => $this->getClass($attributes),
                    'product_id' => $product->getId()
                ],
            ];
        }
        return $this->objectManager->create(ImageBlock::class, $data);
    }
}
