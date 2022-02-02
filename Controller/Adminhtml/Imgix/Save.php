<?php
declare(strict_types=1);
namespace Imgix\Magento\Controller\Adminhtml\Imgix;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action;
use Imgix\Magento\Helper\Data;
use Magento\Backend\Model\Session;

class Save extends Action
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Save imgix image constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helperData
     * @param Session $session
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helperData,
        Session $session
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        $this->session = $session;
    }

    /**
     * Save imgix image constructor.
     *
     * @return void
     */
    public function execute()
    {
        $images = $this->getRequest()->getParam('images');
        $this->session->setImgixImages($images);
        $html = 'done';

        $result = $this->resultJsonFactory->create();
        return $result->setData($html);
    }
}
