<?php
declare(strict_types=1);
namespace Imgix\Magento\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Json\Helper\Data;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class CreateHandler extends \Magento\Catalog\Model\Product\Gallery\CreateHandler
{
    /**
     * @var EntityMetadata
     */
    protected $metadata;

    /**
     * @var ProductAttributeInterface
     */
    protected $attribute;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * Resource model gallery
     *
     * @var Gallery
     */
    protected $resourceModel;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var Config
     */
    protected $mediaConfig;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var Database
     */
    protected $fileStorageDb;

    /**
     * @var array
     */
    private $mediaAttributeCodes;

    /**
     * @var array
     */
    private $imagesGallery;

    /**
     * @var  StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string[]
     */
    private $mediaAttributesWithLabels = [
        'image',
        'small_image',
        'thumbnail'
    ];

    /**
     * @param MetadataPool $metadataPool
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param Gallery $resourceModel
     * @param Data $jsonHelper
     * @param Config $mediaConfig
     * @param Filesystem $filesystem
     * @param Database $fileStorageDb
     * @param StoreManagerInterface|null $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductAttributeRepositoryInterface $attributeRepository,
        Gallery $resourceModel,
        Data $jsonHelper,
        Config $mediaConfig,
        Filesystem $filesystem,
        Database $fileStorageDb,
        StoreManagerInterface $storeManager = null
    ) {
        $this->metadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
        $this->jsonHelper = $jsonHelper;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileStorageDb = $fileStorageDb;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Execute create handler
     *
     * @param object $product
     * @param array $arguments
     * @return object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 101.0.0
     */
    public function execute($product, $arguments = [])
    {
        $attrCode = $this->getAttribute()->getAttributeCode();

        $value = $product->getData($attrCode);

        if (!is_array($value) || !isset($value['images'])) {
            return $product;
        }

        if (!is_array($value['images']) && strlen((string) $value['images']) > 0) {
            $value['images'] = $this->jsonHelper->jsonDecode($value['images']);
        }

        if (!is_array($value['images'])) {
            $value['images'] = [];
        }

        $clearImages = [];
        $newImages = [];
        $existImages = [];
        if ($product->getIsDuplicate() != true) {
            foreach ($value['images'] as &$image) {
                if (!empty($image['removed']) && !$this->canRemoveImage($product, $image['file'])) {
                    $image['removed'] = '';
                }
                if (!empty($image['removed'])) {
                    $clearImages[] = $image['file'];
                } elseif (empty($image['value_id']) || !empty($image['recreate'])) {
                    if (strpos((string) $image['file'], 'imgix') !== false) {
                        $image['new_file'] = $image['file'];
                        $newImages[$image['file']] = $image;
                    } else {
                        $newFile = $this->moveImageFromTmp($image['file']);
                        $image['new_file'] = $newFile;
                        $newImages[$image['file']] = $image;
                        $image['file'] = $newFile;
                    }
                } else {
                    $existImages[$image['file']] = $image;
                }
            }
        } else {
            // For duplicating we need copy original images.
            $duplicate = [];
            foreach ($value['images'] as &$image) {
                if (!empty($image['removed']) && !$this->canRemoveImage($product, $image['file'])) {
                    $image['removed'] = '';
                }

                if (empty($image['value_id']) || !empty($image['removed'])) {
                    continue;
                }
                $duplicate[$image['value_id']] = $this->copyImage($image['file']);
                $image['new_file'] = $duplicate[$image['value_id']];
                $newImages[$image['file']] = $image;
            }
            $value['duplicate'] = $duplicate;
        }
        if (!empty($value['images'])) {
            $this->processMediaAttributes($product, $existImages, $newImages, $clearImages);
        }
        $product->setData($attrCode, $value);
        if ($product->getIsDuplicate() == true) {
            $this->duplicate($product);
            return $product;
        }
        if (!is_array($value) || !isset($value['images']) || $product->isLockedAttribute($attrCode)) {
            return $product;
        }
        $this->processDeletedImages($product, $value['images']);
        $this->processNewAndExistingImages($product, $value['images']);
        $product->setData($attrCode, $value);
        return $product;
    }

    /**
     * Save media gallery store value
     *
     * @param Product $product
     * @param array $data
     */
    private function saveGalleryStoreValue(Product $product, array $data): void
    {
        if (!$product->isObjectNew()) {
            $this->resourceModel->deleteGalleryValueInStore(
                $data['value_id'],
                $data[$this->metadata->getLinkField()],
                $data['store_id']
            );
        }
        $this->resourceModel->insertGalleryValueInStore($data);
    }

    /**
     * Returns safe filename for posted image
     *
     * @param string $file
     * @return string
     */
    private function getSafeFilename($file)
    {
        $file = DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);

        return $this->mediaDirectory->getDriver()->getRealPathSafety($file);
    }

    /**
     * Get Media Attribute Codes cached value
     *
     * @return array
     */
    private function getMediaAttributeCodes()
    {
        if ($this->mediaAttributeCodes === null) {
            $this->mediaAttributeCodes = $this->mediaConfig->getMediaAttributeCodes();
        }
        return $this->mediaAttributeCodes;
    }

    /**
     * Process media attribute
     *
     * @param Product $product
     * @param string $mediaAttrCode
     * @param array $clearImages
     * @param array $newImages
     */
    private function processMediaAttribute(
        Product $product,
        string $mediaAttrCode,
        array $clearImages,
        array $newImages
    ): void {
        $storeId = $product->isObjectNew() ? Store::DEFAULT_STORE_ID : (int) $product->getStoreId();
        /***
         * Attributes values are saved as default value in single store mode
         * @see \Magento\Catalog\Model\ResourceModel\AbstractResource::_saveAttributeValue
         */
        if ($storeId === Store::DEFAULT_STORE_ID
            || $this->storeManager->hasSingleStore()
            || $this->getMediaAttributeStoreValue($product, $mediaAttrCode, $storeId) !== null
        ) {
            $value = $product->getData($mediaAttrCode);
            $newValue = $value;
            if (in_array($value, $clearImages)) {
                $newValue = 'no_selection';
            }
            if (in_array($value, array_keys($newImages))) {
                $newValue = $newImages[$value]['new_file'];
            }
            $product->setData($mediaAttrCode, $newValue);
            $product->addAttributeUpdate(
                $mediaAttrCode,
                $newValue,
                $storeId
            );
        }
    }

    /**
     * Process media attribute label
     *
     * @param Product $product
     * @param string $mediaAttrCode
     * @param array $clearImages
     * @param array $newImages
     * @param array $existImages
     */
    private function processMediaAttributeLabel(
        Product $product,
        string $mediaAttrCode,
        array $clearImages,
        array $newImages,
        array $existImages
    ): void {
        $resetLabel = false;
        $attrData = $product->getData($mediaAttrCode);
        if (in_array($attrData, $clearImages)) {
            $product->setData($mediaAttrCode . '_label', null);
            $resetLabel = true;
        }

        if (in_array($attrData, array_keys($newImages))) {
            $product->setData($mediaAttrCode . '_label', $newImages[$attrData]['label']);
        }

        if (in_array($attrData, array_keys($existImages)) && isset($existImages[$attrData]['label'])) {
            $product->setData($mediaAttrCode . '_label', $existImages[$attrData]['label']);
        }

        if ($attrData === 'no_selection' && !empty($product->getData($mediaAttrCode . '_label'))) {
            $product->setData($mediaAttrCode . '_label', null);
            $resetLabel = true;
        }
        if (!empty($product->getData($mediaAttrCode . '_label'))
            || $resetLabel === true
        ) {
            $product->addAttributeUpdate(
                $mediaAttrCode . '_label',
                $product->getData($mediaAttrCode . '_label'),
                $product->getStoreId()
            );
        }
    }

    /**
     * Get product images for all stores
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getImagesForAllStores(ProductInterface $product)
    {
        if ($this->imagesGallery ===  null) {
            $storeIds = array_keys($this->storeManager->getStores());
            $storeIds[] = 0;

            $this->imagesGallery = $this->resourceModel->getProductImages($product, $storeIds);
        }

        return $this->imagesGallery;
    }

    /**
     * Check possibility to remove image
     *
     * @param ProductInterface $product
     * @param string $imageFile
     * @return bool
     */
    private function canRemoveImage(ProductInterface $product, string $imageFile) :bool
    {
        $canRemoveImage = true;
        $gallery = $this->getImagesForAllStores($product);
        $storeId = $product->getStoreId();
        $storeIds = [];
        $storeIds[] = 0;
        $websiteIds = array_map('intval', $product->getWebsiteIds() ?? []);
        foreach ($this->storeManager->getStores() as $store) {
            if (in_array((int) $store->getWebsiteId(), $websiteIds, true)) {
                $storeIds[] = (int) $store->getId();
            }
        }

        if (!empty($gallery)) {
            foreach ($gallery as $image) {
                if (in_array((int) $image['store_id'], $storeIds)
                    && $image['filepath'] === $imageFile
                    && (int) $image['store_id'] !== $storeId
                ) {
                    $canRemoveImage = false;
                }
            }
        }

        return $canRemoveImage;
    }

    /**
     * Get media attribute value for store view
     *
     * @param Product $product
     * @param string $attributeCode
     * @param int|null $storeId
     * @return string|null
     */
    private function getMediaAttributeStoreValue(Product $product, string $attributeCode, int $storeId = null): ?string
    {
        $gallery = $this->getImagesForAllStores($product);
        $storeId = $storeId === null ? (int) $product->getStoreId() : $storeId;
        foreach ($gallery as $image) {
            if ($image['attribute_code'] === $attributeCode && ((int)$image['store_id']) === $storeId) {
                return $image['filepath'];
            }
        }
        return null;
    }

    /**
     * Update media attributes
     *
     * @param Product $product
     * @param array $existImages
     * @param array $newImages
     * @param array $clearImages
     */
    private function processMediaAttributes(
        Product $product,
        array $existImages,
        array $newImages,
        array $clearImages
    ): void {
        foreach ($this->getMediaAttributeCodes() as $mediaAttrCode) {
            $this->processMediaAttribute(
                $product,
                $mediaAttrCode,
                $clearImages,
                $newImages
            );
            if (in_array($mediaAttrCode, $this->mediaAttributesWithLabels)) {
                $this->processMediaAttributeLabel(
                    $product,
                    $mediaAttrCode,
                    $clearImages,
                    $newImages,
                    $existImages
                );
            }
        }
    }
}
