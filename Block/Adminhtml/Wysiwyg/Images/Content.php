<?php
declare(strict_types=1);
namespace Imgix\Magento\Block\Adminhtml\Wysiwyg\Images;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Json\EncoderInterface;

class Content extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Block construction
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_headerText = __('Media Storage');
        $this->buttonList->remove('back');
        $this->buttonList->remove('edit');
        $this->buttonList->add(
            'cancel',
            [
                'class' => 'cancel action-quaternary',
                'label' => __('CANCEL'),
                'type' => 'button',
                'onclick' => 'MediabrowserUtility.closeDialog();'
            ],
            0,
            0,
            'header'
        );

        $this->buttonList->add(
            'insert_files',
            [
                'class' => 'save action-primary',
                'label' => __('ADD IMAGE'),
                'type' => 'button'
            ],
            0,
            100,
            'header'
        );
    }

    /**
     * Files action source URL
     *
     * @return string
     */
    public function getContentsUrl()
    {
        return $this->getUrl('imgixadmin/*/contents', ['type' => $this->getRequest()->getParam('type')]);
    }

    /**
     * Javascript setup object for filebrowser instance
     *
     * @return string
     */
    public function getFilebrowserSetupObject()
    {
        $setupObject = new \Magento\Framework\DataObject();

        $setupObject->setData(
            [
                'targetElementId' => $this->getTargetElementId(),
                'contentsUrl' => $this->getContentsUrl(),
                'onInsertUrl' => $this->getOnInsertUrl(),
                'headerText' => $this->getHeaderText(),
                'showBreadcrumbs' => true,
            ]
        );

        return $this->_jsonEncoder->encode($setupObject);
    }

    /**
     * New directory action target URL
     *
     * @return string
     */
    public function getOnInsertUrl()
    {
        return $this->getUrl('imgixadmin/*/onInsert');
    }

    /**
     * Target element ID getter
     *
     * @return string
     */
    public function getTargetElementId()
    {
        return $this->getRequest()->getParam('target_element_id');
    }
}
