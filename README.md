<!-- ix-docs-ignore -->
![imgix logo](https://assets.imgix.net/sdk-imgix-logo.svg)

Use the `imgix/magento` extension to power your Adobe Commerce (Magento) images. Once enabled, you can search, select, and serve product and CMS images through [imgix](https://www.imgix.com/). Already a customer? Download the extension and enable with an API key from your [imgix dashboard](https://dashboard.imgix.com/api-keys). New customer? [Create an account](https://dashboard.imgix.com/sign-up).

[![Version](https://img.shields.io/packagist/v/imgix/magento.svg)](https://packagist.org/packages/imgix/magento)
[![Downloads](https://img.shields.io/packagist/dt/imgix/magento)](https://packagist.org/packages/imgix/magento)
[![License](https://img.shields.io/github/license/imgix/magento)](https://github.com/imgix/magento/blob/main/LICENSE)

---
<!-- /ix-docs-ignore -->

<!-- NB: Run `npx markdown-toc README.md --maxdepth 4 | sed -e 's/[[:space:]]\{2\}/    /g'` to generate TOC, and copy the result from the terminal to replace the TOC below :) -->

<!-- prettier-ignore-start -->

<!-- toc -->

- [Installation](#installation)
- [Configuration](#configuration)
  - [Generating a New API Key](#generating-a-new-api-key)
  - [Add Image Parameters](#add-image-parameters)
- [Adding Images in Products](#adding-images-in-products)
- [Add Images to Pages](#add-images-to-pages)
  - [Customizing an Image](#customizing-an-image)

<!-- tocstop -->

<!-- prettier-ignore-end -->

# Installation

You can install the extension with composer by running the following commands in your root directory:

```bash
composer require imgix/magento
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento indexer:reindex
php bin/magento cache:clean
php bin/magento cache:flush
```

# Configuration

Upon installation of the imgix extension, users will need to configure the extension with an imgix API key and parameter presets. From the storefront’s admin panel, select the **Stores** tab and select **Configuration** under the Settings section. 

![Initial configuration](https://assets.imgix.net/sdk/magento/01-configuration.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

Once on the configuration page, open the imgix tab and enable the extension by selecting the Yes option from the Enabled field. In order to operate properly, this extension requires a valid API key, which can be generated from a user’s imgix account.

## Generating a New API Key

First, navigate to the [imgix dashboard](https://dashboard.imgix.com/api-keys) and sign into your account. If you are not an existing imgix user, follow the steps to [setup your account](https://dashboard.imgix.com/sign-up) and [deploy your first source](https://docs.imgix.com/setup/quick-start). Once signed in, open the dropdown menu in the top right corner and click on **API Keys**. 

![API keys menu](https://assets.imgix.net/sdk/magento/02-api_key.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

From this page, users can create an API key specifically for their Magento instance. Press the **Generate New Key** button, enter in the key name, and select the necessary permissions (Sources and Image Manager Browse). Once done, select the **Generate New API Key** button and copy the generated key.

![Generate an API key](https://assets.imgix.net/sdk/magento/03-generate_api_key.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

Return to the Magento admin panel, paste this new key into the **imgix API key** field, as shown below.

![Enter your API key in Magento's Admin Panel](https://assets.imgix.net/sdk/magento/05-api_key_example.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

![Example of what an imgix API key looks like](https://assets.imgix.net/sdk/magento/04-enter_api_key.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

## Add Image Parameters

Once the API key is added, users can specify any number of imgix [rendering parameters](https://docs.imgix.com/apis/rendering) to be applied to various versions of their Product Images. For an introduction to specifying and structuring these parameters, please refer to our [Serving Images](https://docs.imgix.com/setup/serving-assets#applying-parameters) documentation. Users may also want to leverage the imgix [Sandbox](https://sandbox.imgix.com/create), which allows for quick and live testing of parameters.

Users may choose to specify parameters for any/all of the following image sizes:

- **Default Images**: Corresponds to the base image size of a product
- **Small Images**: Corresponds to the small image size of a product
- **Thumbnail Images**: Corresponds to the thumbnail image size of a product

These fields are pre-configured with strong defaults that imgix believes will apply to most users. However, users can still change these values based on their desired outcomes. After all parameters have been set, press the **Select Config** button to finalize installation.

# Adding Images in Products

Once the configuration steps have been completed, the extension can be used to add images to Products stored in Magento. Navigate to the Products page (Catalog > Products on the admin sidebar) and either create a new product with the Add Product button or by selecting the Edit button of an existing product.

![Adding images to a product](https://assets.imgix.net/sdk/magento/06-adding_images.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

Once on the Product detail page, scroll down to the **Images and Videos** attribute section. Here, there should now be an **Add imgix Image** button present. Note: if no such button appears, please ensure that the installation steps were completed correctly.

![This is the button for adding an image](https://assets.imgix.net/sdk/magento/07-add_image_button.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

Selecting the **Add imgix Image** button will open a modal that displays images from the user’s imgix sources. Users can switch between sources by pressing the source dropdown in the top left corner. Users may choose to browse more images from the current source by selecting the **Load More** button towards the bottom of the modal.Conversely, users may also search across all assets by using the search bar at the top of the modal.

![Image selection](https://assets.imgix.net/sdk/magento/08-image_selection.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

Users may select one or more images from this view by clicking on it. Users will notice a light blue highlight around images that have been selected. To deselect an image, click on it once more until the highlight disappears. Once done, press the Add Image button to insert the selected image(s) to the product. 

![Almost finished adding an image](https://assets.imgix.net/sdk/magento/09-image_added.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

At this point, users may select Save to finalize these changes.

# Add Images to Pages

In addition to Product images, the imgix extension can also be used to add images to Pages and/or Blocks. Navigate to either the Page or Block editors via the Content tab on the admin sidebar, creating a new page/block or editing an existing one as desired.

**Note:** Because this extension is created in part for us on Page Builder, users should ensure that they are using this extension for Magento version 2.4.3 and later.

![The page view in MAgento](https://assets.imgix.net/sdk/magento/09-image_added.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

To edit an existing page, press the **Select** action and then the **Edit** option. Under the **Content** section, select the **Edit with Page Builder** button. 

![Editing with Page Builder](https://assets.imgix.net/sdk/magento/11-edit_page.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

From the **Magento Page Builder**, users can create a new row/column component (located under Layout section) by dragging it onto the page in the desired location. This is where an imgix image can be added. After, users can click and drag the imgix Image component to the desired row or column.

![Editing with Page Builder](https://assets.imgix.net/sdk/magento/12-editing_page_builder.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

From this point, users may follow the same steps as in the Product section to browse, search, and insert an image into this component. Note: in the context of the Page Builder, only one image can be added per component.

![Browsing images in Page Builder](https://assets.imgix.net/sdk/magento/13-browsing_images.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

![Selecting an image in Page Builder](https://assets.imgix.net/sdk/magento/14-selecting_an_image_in_page_builder.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

At this point, the image can be saved to the Page/Block by closing the Page Builder and pressing the **Save** button at the top right of the screen. See next section (Customizing an Image) to learn how to transform a selected image before saving.

## Customizing an Image

Users may modify the selected image within the component by selecting the Edit button (represented by the gear symbol) when hovering over the image. Under the “imgix image configurations” section, users may elect to customize any of the five parameters shown:

- [Width](https://docs.imgix.com/apis/rendering/size/w): Sets the width of the image
- [Height](https://docs.imgix.com/apis/rendering/size/h): Sets the height of the image
- [Format](https://docs.imgix.com/apis/rendering/format/fm): Converts the image to the specified format
- [Auto](https://docs.imgix.com/apis/rendering/auto/auto): Performs some baseline optimizations to the image
- [Crop](https://docs.imgix.com/apis/rendering/size/crop): Controls how the image should be cropped
- 
Once any number of these configurations are entered, users can accept these changes by pressing the **Save** button on the top left.

![Saving your rendering API configuration](https://assets.imgix.net/sdk/magento/15-saving_configuration.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)

![Example output of a saved image in Page Builder](https://assets.imgix.net/sdk/magento/16-page_builder_saved_example.png?pad=40&w=1520&mask-bg=E8F0F4&mask=corners&corner-radius=12&bg=E8F0F4&auto=compress,format)
