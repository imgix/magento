<?php
declare(strict_types=1);
namespace Imgix\Magento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class ChangeImgixTemplateObserver implements ObserverInterface
{
    /**
     * Change imgix template
     *
     * @param mixed $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(Observer $observer)
    {
        $observer->getBlock()->setTemplate('Imgix_Magento::helper/gallery.phtml');
    }
}
