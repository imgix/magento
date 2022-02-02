<?php
declare(strict_types=1);
namespace Imgix\Magento\Plugin;

use Psr\Log\LoggerInterface as Logger;
use Imgix\Magento\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Config\Model\Config;

class ConfigPlugin
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     *
     * @var Data
     */
    protected $helperData;

    /**
     *  Save configuration constructor
     *
     * @param Logger $logger
     * @param Data $helperData
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Logger $logger,
        Data $helperData,
        ManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
    }

    /**
     * Save configuration
     *
     * @param Config $subject
     * @param \Closure $proceed
     * @return void
     */
    public function aroundSave(
        Config $subject,
        \Closure $proceed
    ) {

        $data = $subject->getData();
        if (isset($data['groups']['settings']['fields']['imgix_api_key'])) {
            $apiKey = $data['groups']['settings']['fields']['imgix_api_key']['value'];

            $apiKeyValidation = $this->helperData->getImgixApiKeyValidation($apiKey);

            if ($apiKeyValidation['authorized']== 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__($apiKeyValidation['message']));
            }
            if ($apiKeyValidation['authorized']== 1) {
                $this->messageManager->addSuccessMessage($apiKeyValidation['message']);
                return $proceed();
            }
        }
    }
}
