<?php
declare(strict_types=1);
namespace Imgix\Magento\Model\Cart;

use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product;
use Imgix\Magento\Helper\Data;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Helper\Image;
use Magento\Checkout\CustomerData\ItemPoolInterface;

class ImageProvider extends \Magento\Checkout\Model\Cart\ImageProvider
{
    /**
     * @var CartItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var ItemPoolInterface
     * @deprecated 100.2.7 No need for the pool as images are resolved in the default item implementation
     * @see DefaultItem::getProductForThumbnail
     */
    protected $itemPool;

    /**
     * @var DefaultItem
     * @since 100.2.7
     */
    protected $customerDataItem;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param CartItemRepositoryInterface $itemRepository
     * @param ItemPoolInterface $itemPool
     * @param DefaultItem|null $customerDataItem
     * @param Image $imageHelper
     * @param ItemResolverInterface $itemResolver
     * @param Product $product
     * @param Data $helperData
     */
    public function __construct(
        CartItemRepositoryInterface $itemRepository,
        ItemPoolInterface $itemPool,
        DefaultItem $customerDataItem = null,
        Image $imageHelper = null,
        ItemResolverInterface $itemResolver = null,
        Product $product,
        Data $helperData
    ) {
        $this->itemRepository = $itemRepository;
        $this->itemPool = $itemPool;
        $this->customerDataItem = $customerDataItem ?: ObjectManager::getInstance()->get(DefaultItem::class);
        $this->imageHelper = $imageHelper ?: ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(
            ItemResolverInterface::class
        );
        $this->product = $product;
        $this->helperData = $helperData;
    }

    /**
     * Get images
     *
     * @param mixed $cartId
     * @return void
     */
    public function getImages($cartId)
    {
        $itemData = [];

        /** @see code/Magento/Catalog/Helper/Product.php */
        $items = $this->itemRepository->getList($cartId);
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        foreach ($items as $cartItem) {
            $itemData[$cartItem->getItemId()] = $this->getProductImageData($cartItem);
        }
        return $itemData;
    }

    /**
     * Get product image data
     *
     * @param \Magento\Quote\Model\Quote\Item $cartItem
     *
     * @return array
     */
    private function getProductImageData($cartItem)
    {
        $imageHelper = $this->imageHelper->init(
            $this->itemResolver->getFinalProduct($cartItem),
            'mini_cart_product_thumbnail'
        );
        $productId = $cartItem->getProductId();
        $product = $this->product->load($productId);
        
        if ($this->helperData->isEnabled() && strpos((string) $product->getThumbnail(), 'imgix') !== false) {
            $smallImage = $this->helperData->getSmallImageOptions();
            $imageData = [
                'src' => $product->getThumbnail().'?'.$smallImage,
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ];
        } else {
            $imageData = [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ];
        }
        return $imageData;
    }
}
