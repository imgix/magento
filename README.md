<!-- ix-docs-ignore -->
![imgix logo](https://assets.imgix.net/sdk-imgix-logo.svg)

Use the `imgix/magento` extension to power your Adobe Commerce (Magento) images. Once enabled, you can search, select, and serve product and CMS images through [imgix](https://www.imgix.com/). Already a customer? Download the extension and enable with an API key from your [imgix dashboard](https://dashboard.imgix.com/api-keys). New customer? [Create an account](https://dashboard.imgix.com/sign-up).

[![Version](https://img.shields.io/packagist/v/imgix/magento.svg)](https://packagist.org/packages/imgix/magento)
[![Downloads](https://img.shields.io/packagist/dt/imgix/magento)](https://packagist.org/packages/imgix/magento)
[![License](https://img.shields.io/github/license/imgix/magento)](https://github.com/imgix/magento/blob/main/LICENSE)

---
<!-- /ix-docs-ignore -->

- [Installation](#installation)

# Installation

You can install the extension with composer by running the following commands in your root directory:

```
composer require imgix/magento
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento indexer:reindex
php bin/magento cache:clean
php bin/magento cache:flush
```
