<?php

declare(strict_types=1);

namespace Imgix\Magento\Model\Config\ContentType\AdditionalData\Provider\Uploader;

use Magento\PageBuilder\Model\Config\ContentType\AdditionalData\ProviderInterface;
use Magento\Backend\Model\Url;

/**
 * Provides open dialog URL for media gallery slideout
 */
class OpenDialogUrl implements ProviderInterface
{
    /**
     * @var Url
     */
    private $urlBuilder;

    private const DEFAULT_IMGIX_OPEN_DIALOG_URL = 'imgixadmin/wysiwyg_images/index';

    /**
     * @var string
     */
    private $openDialogUrl;

    /**
     * @param Url $urlBuilder
     */
    public function __construct(
        Url $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->openDialogUrl = self::DEFAULT_IMGIX_OPEN_DIALOG_URL;
    }

    /**
     * @inheritdoc
     */
    public function getData(string $itemName) : array
    {
        return [
            $itemName => $this->urlBuilder->getUrl($this->openDialogUrl, ['_secure' => true])
        ];
    }
}
