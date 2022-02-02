<?php
declare(strict_types=1);
namespace Imgix\Magento\Model\Wysiwyg\Images;

use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;
use Imgix\Magento\Helper\Data;

class GetInsertImgixImageContent
{
    /**
     * @var ImagesHelper
     */
    private $imagesHelper;

    /**
     * @param ImagesHelper $imagesHelper
     * @param Data $helperData
     */
    public function __construct(
        ImagesHelper $imagesHelper,
        Data $helperData
    ) {
        $this->imagesHelper = $imagesHelper;
        $this->helperData = $helperData;
    }

    /**
     * Create a content (just a link or an html block) for inserting image to the content
     *
     * @param string $imageUrl
     * @param string $forceStaticPath
     * @param string $renderAsTag
     * @param int|null $storeId
     * @return string
     */
    public function execute(
        string $imageUrl,
        string $forceStaticPath,
        string $renderAsTag,
        ?int $storeId = null
    ): string {
        if ($forceStaticPath) {
            return $imageUrl;
        }
        return $this->helperData->getImgixImageHtmlDeclaration($imageUrl, $renderAsTag);
    }
}
