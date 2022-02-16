<?php

namespace Imgix\Magento\Ui\Component\Listing\Column\ConfigurableProducts;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Imgix\Magento\Helper\Data as ImgixHelper;
use Magento\Catalog\Helper\Image;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Thumbnail extends Column
{
    public const NAME = 'thumbnail';

    public const ALT_FIELD = 'name';

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Image $imageHelper
     * @param ImgixHelper $imgixHelper
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Image $imageHelper,
        ImgixHelper $imgixHelper,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->imgixHelper = $imgixHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = new \Magento\Framework\DataObject($item);
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
