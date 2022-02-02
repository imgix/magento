<?php
declare(strict_types=1);
namespace Imgix\Magento\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Contents extends \Imgix\Magento\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Save current path in session
     *
     * @return $this
     */
    protected function _saveSessionCurrentPath()
    {
        $this->getStorage()->getSession()->setCurrentPath(
            $this->_objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->getCurrentPath()
        );
        return $this;
    }

    /**
     * Contents action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->_initAction()->_saveSessionCurrentPath();
            /** @var \Magento\Framework\View\Result\Layout $resultLayout */
            $resultLayout = $this->resultLayoutFactory->create();
            return $resultLayout;
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($result);
            return $resultJson;
        }
    }
}
