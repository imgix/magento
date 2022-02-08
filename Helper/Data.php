<?php
declare(strict_types=1);
namespace Imgix\Magento\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Imgix\UrlBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Data extends AbstractHelper
{
    public const XPATH_FIELD_ENABLED = 'imgix/settings/enabled';
    public const XPATH_FIELD_LARGE = 'imgix/settings/default_options';
    public const XPATH_FIELD_SMALL = 'imgix/settings/small_options';
    public const XPATH_FIELD_THUMBNAIL = 'imgix/settings/thumbnail_options';
    public const XPATH_FIELD_API_KEY = 'imgix/settings/imgix_api_key';
    public const XPATH_FIELD_PAGINATION_RESULT_PER_PAGE = 'imgix/settings/result_per_page';
    
    /**
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param CurlFactory $curlFactory
     * @param Json $json
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        CurlFactory $curlFactory,
        Json $json
    ) {
        parent::__construct($context);
        $this->curlFactory = $curlFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_json = $json;
    }

    /**
     * Get Module status from admin configuration
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XPATH_FIELD_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) ?? false;
    }

    /**
     * Get imagix API key from admin configuration
     *
     * @return string|null
     */
    public function getSecureApiKey(): string
    {
        $secureApiKey =  $this->scopeConfig->getValue(
            self::XPATH_FIELD_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return isset($secureApiKey) ? (string) $secureApiKey : '';
    }

    /**
     * Get Default Image Options from admin configuration
     *
     * @return string|null
     */
    public function getDefaultImageOptions(): string
    {
        $defaultImageOption = $this->scopeConfig->getValue(
            self::XPATH_FIELD_LARGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return isset($defaultImageOption) ? (string) $defaultImageOption : '';
    }

    /**
     * Get Small Image Options from admin configuration
     *
     * @return string|null
     */
    public function getSmallImageOptions(): string
    {
        $smallImageOption =  $this->scopeConfig->getValue(
            self::XPATH_FIELD_SMALL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return isset($smallImageOption) ? (string) $smallImageOption : '';
    }

    /**
     * Get Thumbnail Image Options from admin configuration
     *
     * @return string|null
     */
    public function getThumbnailImageOptions(): string
    {
        $thumbnailImageOption = $this->scopeConfig->getValue(
            self::XPATH_FIELD_THUMBNAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return isset($thumbnailImageOption) ? (string) $thumbnailImageOption : '';
    }

    /**
     * Get list of assets from selected source id
     *
     * @param string $sourceId
     * @param string $cursor
     * @return array
     */
    public function getImgixAssets($sourceId, $cursor): array
    {
        $requestUrl = "https://api.imgix.com/api/v1/sources/".$sourceId."/assets";
        /* query parameter for POST BODY */
        if (isset($cursor)) {
            $params = [
                'page[cursor]' => $cursor,
                'filter[media_kind]'=> "Image"
            ];
            $requestUrl = $requestUrl . '?' . http_build_query($params);
        }
        $setHeader = [
            'Accept: application/vnd.api+json',
            'Authorization: Bearer '.$this->getSecureApiKey(),
            'Content-Type: application/vnd.api+json'
        ];
        /* Create curl factory */
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(
            \Zend_Http_Client::GET,
            $requestUrl,
            '1.1',
            $setHeader
        );
        $result = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($result);
        /* convert JSON to Array */
        $response = $this->jsonHelper->jsonDecode($body);
        
        $assets = [];
        $errorMessage = 'imgix error: ';

        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $errorMessage .= $error['detail']." ";
            }
            $assets['errors'] = $errorMessage;
            return $assets;
        }

        $imgix_sub_domain = null;
        $imgix_sub_domain = $this->getSingleSource($sourceId);

        if ($response['data'] && !empty($imgix_sub_domain) && $imgix_sub_domain !==null) {
            if (isset($response['meta']['cursor'])) {
                $assets['cursor'] = $response['meta']['cursor'];
            }
            foreach ($response['data'] as $key => $value) {
                $builder = new UrlBuilder($imgix_sub_domain, true, "", false);
                $url = $builder->createURL($value['attributes']['origin_path']);
                $assets['data'][$key]['name'] = $value['attributes']['origin_path'];
                $assets['data'][$key]['url'] = $url;
                $assets['data'][$key]['srcset'] = $url."?fit=crop&w=135.406&h=109&dpr=1&q=75&auto=format%2Ccompress";
            }
        }
        return $assets;
    }

    /**
     * Get list of assets from selected source id and search keyword
     *
     * @param string $sourceId
     * @param string $keyword
     * @param string $cursor
     * @return array
     */
    public function getImgixAssetsBySourceIdAndSearchKeyword($sourceId, $keyword, $cursor): array
    {
        $requestUrl = "https://api.imgix.com/api/v1/sources/".$sourceId."/assets";
        /* query parameter for POST BODY */
        if (isset($cursor)) {
            $params = [
                'filter[or:categories]' => $keyword,
                'filter[or:keywords]' => $keyword,
                'filter[or:origin_path]' => $keyword,
                'page[cursor]' => $cursor,
                'filter[media_kind]'=> "Image"
            ];
            $requestUrl = $requestUrl . '?' . http_build_query($params);
        }
        $setHeader = [
            'Accept: application/vnd.api+json',
            'Authorization: Bearer '.$this->getSecureApiKey(),
            'Content-Type: application/vnd.api+json'
        ];
        /* Create curl factory */
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(
            \Zend_Http_Client::GET,
            $requestUrl,
            '1.1',
            $setHeader
        );
        $result = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($result);
        /* convert JSON to Array */
        $response = $this->jsonHelper->jsonDecode($body);

        $assets = [];
        $errorMessage = 'imgix error: ';

        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $errorMessage .= $error['detail']." ";
            }
            $assets['errors'] = $errorMessage;
            return $assets;
        }
        
        $imgix_sub_domain = null;
        $imgix_sub_domain = $this->getSingleSource($sourceId);

        if (isset($response['data']) && !empty($imgix_sub_domain) && $imgix_sub_domain !==null) {
            if (isset($response['meta']['cursor'])) {
                $assets['cursor'] = $response['meta']['cursor'];
            }
            foreach ($response['data'] as $key => $value) {
                $builder = new UrlBuilder($imgix_sub_domain, true, "", false);
                $url = $builder->createURL($value['attributes']['origin_path']);
                $assets['data'][$key]['name'] = $value['attributes']['origin_path'];
                $assets['data'][$key]['url'] = $url;
                $assets['data'][$key]['srcset'] = $url."?fit=crop&w=135.406&h=109&dpr=1&q=75&auto=format%2Ccompress";
            }
        }
        return $assets;
    }

    /**
     * Get list of sources which is created on imagix API
     *
     * Also source is enabled and deployment_status is deployed
     *
     * @return array
     */
    public function getEnabledImgixSources(): array
    {
        $requestUrl = "https://api.imgix.com/api/v1/sources?filter[enabled]=true";
        $setHeader = [
            'Accept: application/vnd.api+json',
            'Authorization: Bearer '.$this->getSecureApiKey(),
            'Content-Type: application/vnd.api+json'
        ];
        /* Create curl factory */
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(
            \Zend_Http_Client::GET,
            $requestUrl,
            '1.1',
            $setHeader
        );
        $result = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($result);
        /* convert JSON to Array */
        $response = $this->jsonHelper->jsonDecode($body);

        $sources = [];
        $errorMessage = 'imgix error: ';
        
        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $errorMessage .= $error['detail']." ";
            }
            $sources['errors'] = $errorMessage;
            return $sources;
        }
        
        $allowed_deployment_types = ["s3","azure","gcs"];
        $deploymentType = null;

        if ($response['data']) {
            foreach ($response['data'] as $value) {
                if (in_array($value['attributes']['deployment']['type'], $allowed_deployment_types)) {
                    if ($value['attributes']['deployment']['type'] == "s3") {
                        $deploymentType = "Amazon S3";
                    } elseif ($value['attributes']['deployment']['type'] == "azure") {
                        $deploymentType = "Azure";
                    } elseif ($value['attributes']['deployment']['type'] == "gcs") {
                        $deploymentType = "Google Cloud";
                    }
                    $sources[] = [
                        'id' => $value['id'],
                        'name' => $value['attributes']['name'],
                        'deployment_status' => $value['attributes']['deployment_status'],
                        'deployment_type' => $deploymentType,
                        'imgix_subdomains' => $value['attributes']['deployment']['imgix_subdomains'][0]
                    ];
                }
            }
        }
        return $sources;
    }

    /**
     * Get imgix sub domain
     *
     * @param string $sourceId
     * @return array
     */
    public function getSingleSource($sourceId): string
    {
        $requestUrl = "https://api.imgix.com/api/v1/sources/".$sourceId;
        $setHeader = [
            'Accept: application/vnd.api+json',
            'Authorization: Bearer '.$this->getSecureApiKey(),
            'Content-Type: application/vnd.api+json'
        ];
        /* Create curl factory */
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(
            \Zend_Http_Client::GET,
            $requestUrl,
            '1.1',
            $setHeader
        );
        $result = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($result);
        /* convert JSON to Array */
        $response = $this->jsonHelper->jsonDecode($body);

        $imgix_sub_domain = null;

        if ($response['data']['attributes']['deployment']['imgix_subdomains'][0]) {
            $imgix_sub_domain =  $response['data']['attributes']['deployment']['imgix_subdomains'][0];
            $imgix_sub_domain = $imgix_sub_domain.".imgix.net";
        }
        return $imgix_sub_domain;
    }

    /**
     * GetImgixImageHtmlDeclaration
     *
     * @param string $imgixImageUrl
     * @param bool $renderAsTag
     * @return string
     */
    public function getImgixImageHtmlDeclaration($imgixImageUrl, $renderAsTag = false)
    {
        if ($renderAsTag) {
            $html = sprintf('<img src="%s" alt="" />', $imgixImageUrl);
        } else {
            $html = $imgixImageUrl;
        }
        return $html;
    }
    
    /**
     * Check imgix api key is valid or not
     *
     * @param string $apiKey
     * @return array
     */
    public function getImgixApiKeyValidation($apiKey): array
    {
        $requestUrl = "https://api.imgix.com/api/v1/sources?page[size]=1";
        $setHeader = [
            'Accept: application/vnd.api+json',
            'Authorization: Bearer '.$apiKey,
            'Content-Type: application/vnd.api+json'
        ];
        /* Create curl factory */
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(
            \Zend_Http_Client::GET,
            $requestUrl,
            '1.1',
            $setHeader
        );
        $result = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($result);
        /* convert JSON to Array */
        $response = $this->jsonHelper->jsonDecode($body);

        $results = [];
        $errorMessage = 'imgix error: ';
        
        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $errorMessage .= $error['detail']." ";
            }
            $results['message'] = $errorMessage;
            $results['authorized'] = 0 ;
        }
        if ($response['meta']['authentication']['authorized']==1) {
            $results['message'] = "API key is authorized.";
            $results['authorized'] = 1;
        }
        return $results;
    }

    /**
     * Get jsonencode
     *
     * @param mixed $data
     * @return bool|false|string
     */
    public function getJsonEncode($data)
    {
        return $this->_json->serialize($data); // it's same as like json_encode
    }

    /**
     * Get JsonDecode
     *
     * @param mixed $data
     * @return array|bool|float|int|mixed|string|null
     */
    public function getJsonDecode($data)
    {
        return $this->_json->unserialize($data); // it's same as like json_decode
    }

    /**
     * For catalog create srcset
     *
     * @param string $defaultOptions
     * @param string $url
     * @return void
     */
    public function createSrcset($defaultOptions, $url)
    {
        $srcset = null;
        if (strpos($url, 'imgix.net') !== false && $this->isEnabled()) {
            // Remove https:// from url
            $imgix_subdomain = substr($url, 8, strrpos($url, 'imgix.net/')+1);
       
            $origin_path = substr($url, strpos($url, "imgix.net/") + 9);
       
            $builder = new UrlBuilder($imgix_subdomain, true, "", false);

            $optionVal =  $options = $params = [] ;
            if (!empty($defaultOptions)) {
                $options = explode("&", $defaultOptions);
                if (!empty($options) && is_array($options) && isset($options)) {
                    foreach ($options as $key => $value) {
                        $optionVal = explode("=", $value);
                        if (!empty($optionVal) && is_array($optionVal) && isset($optionVal[1])) {
                            $params [$optionVal[0]] = $optionVal[1] ;
                        }
                    }
                }
            }
            if ((!empty($params)) && (is_array($params)) && (!empty($defaultOptions))) {
                $srcset = $builder->createSrcSet($origin_path, $params);
            } else {
                $srcset = $builder->createSrcSet($origin_path);
            }
        }
        return $srcset;
    }
}
