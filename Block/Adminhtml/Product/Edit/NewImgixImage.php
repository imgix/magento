<?php
namespace Imgix\Magento\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Imgix\Magento\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class NewImgixImage extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var string
     */
    protected $videoSelector = '#media_gallery_content';

    /**
     * @var \Imgix\Magento\Helper\Data
     */
    protected $helperData;
    
    /**
     * Get url key
     *
     * @var UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param EncoderInterface $jsonEncoder
     * @param Data $helperData
     * @param ManagerInterface $messageManager
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        EncoderInterface $jsonEncoder,
        Data $helperData,
        ManagerInterface $messageManager,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
        $this->setUseContainer(true);
    }

    /**
     * Form preparation
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'new_imgix_form',
                'class' => 'admin__scope-old',
                'enctype' => 'multipart/form-data',
            ]
        ]);
        $form->setUseContainer($this->getUseContainer());
        $form->addField('new_video_messages', 'note', []);
        $fieldset = $form->addFieldset('new_imgix_form_fieldset', []);
        $fieldset->addField(
            '',
            'hidden',
            [
                'name' => 'form_key',
                'value' => $this->getFormKey(),
            ]
        );
        
        $this->setForm($form);
    }

    /**
     * Get html id
     *
     * @return mixed
     */
    public function getHtmlId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->mathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * Get widget options
     *
     * @return string
     */
    public function getWidgetOptions()
    {
        return $this->jsonEncoder->encode(
            [
                'htmlId' => $this->getHtmlId()
            ]
        );
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Get enable imgix sources
     *
     * @return array
     */
    public function getImgixImageSources()
    {
        $sources = [];
        $sources = $this->helperData->getEnabledImgixSources();
       
        if (isset($sources['errors'])) {
            $this->messageManager->addErrorMessage($sources['errors']);
            return $sources = [];
        }

        return $sources;
    }
    
    /**
     * Get imgix image url
     *
     * @return void
     */
    public function getImgixImageUrl()
    {
        return $this->urlBuilder->getUrl('imgixadmin/imgix/post');
    }
}
