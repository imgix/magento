# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.2](https://github.com/imgix/magento/compare/v1.1.1...v1.1.2) (2022-04-12)

- Update that adds in internal analytics to assist with future product improvements

## [1.1.1](https://github.com/imgix/magento/compare/v1.1.0...v1.1.1) (2022-03-25)

### Fixes

- Reduce number of calls to `sources` endpoint
- Show 100 sources on dropdown
- Added scrollbar to source dropdown

## [1.1.0](https://github.com/imgix/magento/compare/v1.0.5...v1.1.0) (2022-03-08)

### Features

- Dispaly 21 images per page by default

### Fixes

- HTML warning gets shown when saving image as template
- Admin product thumbnails are display the origin image (full size)
- update default parameter options to not include `crop` parameters

## [1.0.5](https://github.com/imgix/magento/compare/v1.0.4...v1.0.5) (2022-02-14)

### Fixes

- Undefined index: settings, addresses "Something went wrong while saving this configuration" error
- Unable to enable imgix module in admin configuration

### Chore

- Remove `"minimum-stability": "dev"` from composer.json
- Upgrade module version `1.0.0` to `1.0.5` in module.xml

## [1.0.4](https://github.com/imgix/magento/compare/v1.0.3...v1.0.4) (2022-02-11)

### Chore

- remove composer.lock
- update imgix/imgix-php to ^3.3.1

## [1.0.3](https://github.com/imgix/magento/compare/v1.0.2...v1.0.3) (2022-02-10)

### Fixes

- the use of helpers in templates is discouraged. Use ViewModel instead
- function's nesting level (7) exceeds 5; consider refactoring the function

## [1.0.2](https://github.com/imgix/magento/compare/v1.0.1...v1.0.2) (2022-02-09)

### Fixes

- unable to save system configuration if module is disabled
- admin catalog product view broken on first time installation

### Documentation

- add README with installation instructions

## [1.0.1](https://github.com/imgix/magento/compare/v1.0.0...v1.0.1) (2022-02-04)

### Fixes

- Remove extraneous `vendor` directory from project.

## [1.0.0](https://github.com/imgix/magento/compare/7f3ce203846b1592bba0ba7471573ede61d0b997...v1.0.0) (2022-02-03)

### Features

Initial release includes:
- Ability to configure the extension with an imgix API key
- Ability to pre-configure image options for each of the three image sizes (small, large, default)
- A modal from which imgix images can be selected from and associated to products
- A modal from which imgix images can be selected from and associated to Pages/Blocks
  - The ability to modify parameters for a specific image within Pages/Blocks
