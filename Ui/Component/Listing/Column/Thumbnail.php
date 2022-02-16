<?php
namespace Imgix\Magento\Ui\Component\Listing\Column;

use Magento\Catalog\Helper\Image;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Imgix\Magento\Helper\Data as ImgixHelper;

class Thumbnail extends Column
{
    public const NAME = 'thumbnail';

    public const ALT_FIELD = 'name';

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param ImgixHelper $imgixHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Image $imageHelper,
        UrlInterface $urlBuilder,
        ImgixHelper $imgixHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->imgixHelper = $imgixHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        try {
            if (isset($dataSource['data']['items'])) {
                $fieldName = $this->getData('name');
                foreach ($dataSource['data']['items'] as & $item) {
                    $product = new DataObject($item);
                    $imageUrl = $product->getThumbnail();
                    if (strpos($imageUrl, 'imgix') !== false) {
                        $thumbnailOptions = null;
                        $thumbnailOptions = $this->imgixHelper->getThumbnailImageOptions();
                        if (!empty($thumbnailOptions)) {
                            $imageUrl = $imageUrl.'?'.$thumbnailOptions;
                        }
                        $item[$fieldName . '_src'] = $imageUrl;
                        $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $product->getName();
                        $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $product->getEntityId(), 'store' => $this->context->getRequestParam('store')]
                        );
                        $item[$fieldName . '_orig_src'] = $imageUrl;

                    } else {
                        $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
                        $item[$fieldName . '_src'] = $imageHelper->getUrl();
                        $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $imageHelper->getLabel();
                        $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $product->getEntityId(), 'store' => $this->context->getRequestParam('store')]
                        );
                        $origImageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail_preview');
                        $item[$fieldName . '_orig_src'] = $origImageHelper->getUrl();
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
        
        return $dataSource;
    }

    /**
     * Get Alt
     *
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}
