<?php
declare(strict_types=1);
namespace Imgix\Magento\Controller\Adminhtml\Wysiwyg;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Imgix\Magento\Helper\Data;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Filesystem\Io\File as FileSystemIo;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Imgiximage extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Context $context
     * @param Data $helperData
     * @param JsonHelper $jsonHelper
     * @param FileSystemIo $fileSystemIo
     */
    public function __construct(
        Context $context,
        Data $helperData,
        JsonHelper $jsonHelper,
        FileSystemIo $fileSystemIo
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        $this->fileSystemIo = $fileSystemIo;
        parent::__construct($context);
    }

    /**
     * Create json response
     *
     * @param mixed $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * Get imgix images
     *
     * @return void
     */
    public function execute()
    {
        $sourceId = $keyword = $errorMessage = $next = $current = $cursor = null;
        $isError = $hasMore = $isNoImages =false;
        $html = '';

        $sourceId = $this->getRequest()->getParam('sourceId');
        $keyword = $this->getRequest()->getParam('keyword');
        $cursor = $this->getRequest()->getParam('cursor');

        $apiKey = $this->helperData->getSecureApiKey();
        $apiKeyValidation = $this->helperData->getImgixApiKeyValidation($apiKey);
        $isEnable = $this->helperData->isEnabled();
        if ($isEnable == 1) {
            if ($apiKeyValidation['authorized'] == 1) {
                // if sourceId is 0
                if ($sourceId == 0) {
                    $datasaved  = false;
                    $isNoImages = true;
                    $sources = $this->helperData->getEnabledImgixSources();
                    if (isset($sources['errors'])) {
                        $isError = true;
                        $errorMessage = "<div class ='message message-error error'>".$sources['errors']."</div>";
                    }
                    $html .= '<div class="empty">'. __("The source contains no images.").'</div>';
                    $data = [
                        "hasMore" => $hasMore,
                        "next" => $next,
                        "current" => $current,
                        "isError" => $isError,
                        "isNoImages" => $isNoImages,
                        "errorMessage" => $errorMessage,
                        "success" => $datasaved,
                        "html" => $html
                    ];
                    return $this->jsonResponse($data);
                }

                if ($keyword !=null && !empty($keyword)) {
                    $imageAssets = $this->helperData->getImgixAssetsBySourceIdAndSearchKeyword(
                        $sourceId,
                        $keyword,
                        $cursor
                    );
                } else {
                    $imageAssets = $this->helperData->getImgixAssets($sourceId, $cursor);
                }
                
                if (isset($imageAssets['data'])) {
                    if (isset($imageAssets['cursor']['hasMore'])) {
                        if (!empty($imageAssets['cursor']['hasMore'])) {
                            $hasMore = true;
                        } else {
                            $hasMore = false;
                        }
                        
                    }
                    if (isset($imageAssets['cursor']['next'])) {
                        $next = $imageAssets['cursor']['next'];
                    }
                    if (isset($imageAssets['cursor']['current'])) {
                        $current = $imageAssets['cursor']['current'];
                    }

                    foreach ($imageAssets['data'] as $imagePath) {
                        $filename = $id = null;
                        
                        // Get image name
                        $filename = $imagePath['name'];
                        
                        // Generate id
                        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $charactersLength = strlen((string) $characters);
                        $id = '';
                        
                        $fileInfo = $this->fileSystemIo->getPathInfo($filename);
                        $basename = $fileInfo['basename'];

                        for ($i = 0; $i < 15; $i++) {
                            $id .= $characters[rand(0, $charactersLength - 1)];
                        }
                        $html .= '<div data-row="file" class="custom imgix-asset" id="'.$id.'"
                                    data-filename="'.$filename.'"
                                    data-url="'.$imagePath['url'].'">
                                        <div class="imgix-asset-img">
                                            <img src="'. $imagePath['url'].'" 
                                                alt="'.$filename.'" 
                                                title="'.$filename.'" 
                                                srcset="'. $imagePath['srcset'].'"/>
                                        </div>
                                        <figcaption class="nm">' . $basename . '</figcaption>
                                </div>';
                    }
                    $datasaved = true;
                } else {
                    $isNoImages = true;
                    if (isset($imageAssets['errors'])) {
                        $isError = true;
                        $errorMessage = "<div class ='message message-error error'>".$imageAssets['errors']."</div>";
                    }
                    $datasaved =  false;
                    $html .= '<div class="empty">'. __("The source contains no images.").'</div>';
                }
                $data = [
                    "hasMore" => $hasMore,
                    "next" => $next,
                    "current" => $current,
                    "isError" => $isError,
                    "isNoImages" => $isNoImages,
                    "errorMessage" => $errorMessage,
                    "success" => $datasaved,
                    "html" => $html
                ];
                return $this->jsonResponse($data);

            } elseif ($apiKeyValidation['authorized'] == 0) {
                $datasaved  = false;
                $isNoImages = true;
                $isError = true;
                $errorMessage = "<div class ='message message-error error'>".$apiKeyValidation['message']."</div>";
                $html .= '<div class="empty"></div>';
                $data = [
                    "hasMore" => $hasMore,
                    "next" => $next,
                    "current" => $current,
                    "isError" => $isError,
                    "isNoImages" => $isNoImages,
                    "errorMessage" => $errorMessage,
                    "success" => $datasaved,
                    "html" => $html
                ];
                return $this->jsonResponse($data);
            }
        } else {
            $datasaved  = false;
            $isNoImages = true;
            $isError = true;
            $errorMessage = "<div class ='message message-error error'>
                                Please enable imgix module from system configuration.
                            </div>";
            $html .= '<div class="empty"></div>';
            $data = [
                "hasMore" => $hasMore,
                "next" => $next,
                "current" => $current,
                "isError" => $isError,
                "isNoImages" => $isNoImages,
                "errorMessage" => $errorMessage,
                "success" => $datasaved,
                "html" => $html
            ];
            return $this->jsonResponse($data);
        }
    }
}
