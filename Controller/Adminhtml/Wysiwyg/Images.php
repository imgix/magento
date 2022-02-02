<?php
declare(strict_types=1);
namespace Imgix\Magento\Controller\Adminhtml\Wysiwyg;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class Images extends \Magento\Backend\App\Action
{
    /**
     * Magento Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init storage
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->getStorage();
        return $this;
    }

    /**
     * Register storage model and return it
     *
     * @return \Magento\Cms\Model\Wysiwyg\Images\Storage
     */
    public function getStorage()
    {
        if (!$this->_coreRegistry->registry('storage')) {
            $storage = $this->_objectManager->create(\Magento\Cms\Model\Wysiwyg\Images\Storage::class);
            $this->_coreRegistry->register('storage', $storage);
        }
        return $this->_coreRegistry->registry('storage');
    }
}
