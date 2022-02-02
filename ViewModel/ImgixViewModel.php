<?php
 
namespace Imgix\Magento\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Imgix\Magento\Helper\Data as ImgixHelper;

class ImgixViewModel implements ArgumentInterface
{
    /**
     * Imgix helper
     *
     * @var ImgixHelper
     */
    private $helper;

    /**
     * Imgix viewmodel
     *
     * @param ImgixHelper $helper
     */
    public function __construct(
        ImgixHelper $helper
    ) {
        $this->helper= $helper;
    }

    /**
     * Imgix module enable
     */
    public function isImgixEnabled()
    {
        return $this->helper->isEnabled();
    }
}
