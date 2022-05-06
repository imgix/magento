<?php
declare(strict_types=1);
namespace Imgix\Magento\Block\Adminhtml\Wysiwyg\Images;

use Magento\Backend\Block\Template\Context;
use Imgix\Magento\Helper\Data;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;

class Tree extends \Magento\Backend\Block\Template
{
    /**
     * imgix helper
     *
     * @var Data
     */
    protected $helperData;

    /**
     * Get form_key
     *
     * @var FormKey
     */
    protected $formKey;

    /**
     * Get url key
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param Data $helperData
     * @param FormKey $formKey
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        FormKey $formKey,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->formKey = $formKey;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Get imgix sources
     *
     * @return array
     */
    public function getImgixImageSources()
    {
        $sources = [];
        if ($this->isImgixEnabled() == 1) {
            $sources = $this->helperData->getEnabledImgixSources();
            if (isset($sources['errors'])) {
                return $sources = [];
            }
        }
        return $sources;
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

    /**
     * Get imgix image url
     *
     * @return void
     */
    public function getImgixImageUrl()
    {
        return $this->urlBuilder->getUrl('imgixadmin/wysiwyg/imgiximage');
    }
    
    /**
     * Check module is enable
     *
     * @return array
     */
    public function isImgixEnabled()
    {
        return $this->helperData->isEnabled();
    }
}
