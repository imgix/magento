<?php
declare(strict_types=1);
namespace Imgix\Magento\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Imgix\Magento\Model\Wysiwyg\Images\GetInsertImgixImageContent;

class OnInsert extends Images implements HttpPostActionInterface
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var GetInsertImgixImageContent
     */
    private $getInsertImgixImageContent;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param RawFactory $resultRawFactory
     * @param GetInsertImgixImageContent $getInsertImgixImageContent
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RawFactory $resultRawFactory,
        ?GetInsertImgixImageContent $getInsertImgixImageContent = null
    ) {
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $coreRegistry);
        $this->getInsertImgixImageContent = $getInsertImgixImageContent ?: $this->_objectManager
            ->get(GetInsertImgixImageContent::class);
    }

    /**
     * Return a content (just a link or an html block) for inserting image to the content
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        return $this->resultRawFactory->create()->setContents(
            $this->getInsertImgixImageContent->execute(
                $data['imageurl'],
                $data['force_static_path'],
                $data['as_is'],
                isset($data['store']) ? (int) $data['store'] : null
            )
        );
    }
}
