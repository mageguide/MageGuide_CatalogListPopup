# MageGuide CatalogListPopup
Tested on: ```2.3.x```

## Description
Allows users to see stock info and next restock dates from the Catalog pages

## Functionalities
- Module reads data from custom product attributes and returns next resupply dates according to requested quantities
- Returns child product from selected configurable options

## Installation
- Upload module files in ``app/code/MageGuide/CatalogListPopup``
- Install module
```sh
        $ php bin/magento module:enable MageGuide_CatalogListPopup
        $ php bin/magento setup:upgrade
        $ php bin/magento setup:di:compile
```