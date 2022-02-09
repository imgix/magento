# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
