<?php
declare(strict_types=1);
namespace Imgix\Magento\Plugin;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Imgix\Magento\Helper\Data;

class AddImagesToGalleryBlock
{
    /**
     * @var CollectionFactory
     */
    protected $dataCollectionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * AddImagesToGalleryBlock constructor.
     *
     * @param CollectionFactory $dataCollectionFactory
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $dataCollectionFactory,
        Data $helperData
    ) {
        $this->dataCollectionFactory = $dataCollectionFactory;
        $this->helperData = $helperData;
    }

    /**
     * Gallery
     *
     * @param Gallery $subject
     * @param mixed $images
     * @return void
     */
    public function afterGetGalleryImages(Gallery $subject, $images)
    {
        $defaultImage = $this->helperData->getDefaultImageOptions();
        $thumbnailImage = $this->helperData->getThumbnailImageOptions();

        if (!empty($defaultImage) && ($defaultImage !='') && ($this->helperData->isEnabled())) {
            $defaultImage = '?'.$defaultImage;
        } else {
            $defaultImage = '';
        }

        if (!empty($thumbnailImage) && ($thumbnailImage !='') && ($this->helperData->isEnabled())) {
            $thumbnailImage = '?'.$thumbnailImage;
        } else {
            $thumbnailImage = '';
        }

        try {
            $product = $subject->getProduct();
            $imagesCollection = $this->dataCollectionFactory->create();
            $productName = $product->getName();
            foreach ($images as $item) {
                if (strpos($item->getFile(), 'imgix') !== false) {
                    $image = [
                        'file' => $item->getFile(),
                        'media_type' => $item->getMediaType(),
                        'value_id' => $item->getValueId(), // unique value
                        'row_id' => $item->getId(), // unique value
                        'label' => $productName,
                        'label_default' => $productName,
                        'position' => $item->getPosition(),
                        'position_default' => $item->getPositionDefault(),
                        'disabled' => 0,
                        'url'  => $item->getFile().'?'.$defaultImage,
                        'path' => $item->getFile(),
                        'small_image_url' => $item->getFile().$thumbnailImage,
                        'medium_image_url' => $item->getFile().$defaultImage,
                        'large_image_url' => $item->getFile().$defaultImage
                    ];
                } else {
                    $image = $item->getData();
                }
                $imagesCollection->addItem(new DataObject($image));
            }
            return $imagesCollection;
        } catch (\Exception $e) {
            return $images;
        }
    }
    /**
     * AfterGetGalleryImagesJson
     *
     * @param Gallery $subject
     * @param mixed $result
     * @return void
     */
    public function afterGetGalleryImagesJson(Gallery $subject, $result)
    {
        $newImagesItems = [];
        $imagesItems = json_decode($result);
        
        $defaultImageOptions = $this->helperData->getDefaultImageOptions();
        $thumbnailImageOptions = $this->helperData->getThumbnailImageOptions();
        
        $sourceDomain = $thumbSourceDomain = $thumbImageSrcset =  $imgImageSrcset = null;

        foreach ($imagesItems as $itemImage) {

            $thumbImage = $itemImage->thumb;
            $imgImage = $itemImage->img;

            $thumbImageSrcset = $thumbImage;
            $imgImageSrcset = $imgImage;

            if (preg_match('/[?]/', $itemImage->thumb)) {
                $thumbSourceDomain = strstr($itemImage->thumb, '?', true);
            } else {
                $thumbSourceDomain = $itemImage->thumb;
            }
            $thumbImageSrcset = $this->helperData->createSrcset($thumbnailImageOptions, $thumbSourceDomain);

            if (preg_match('/[?]/', $itemImage->img)) {
                $sourceDomain = strstr($itemImage->img, '?', true);
            } else {
                $sourceDomain = $itemImage->img;
            }
            $imgImageSrcset = $this->helperData->createSrcset($defaultImageOptions, $sourceDomain);
            
            $itemImage->thumbSrcset = $thumbImageSrcset;
            $itemImage->imgSrcset = $imgImageSrcset;
            
            $newImagesItems[] = $itemImage;
        }
        
        return json_encode($newImagesItems);
    }
}
