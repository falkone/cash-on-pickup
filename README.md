# Cash on Pickup
Magento 2 Module to provide new shipping and payment method for cash payment and in store pickup

## Installation
* add git repository to composer 
```
composer config repositories.cash-on-pickup git git@github.com:falkone/cash-on-pickup.git
```
* require module
```
composer require falkone/cash-on-pickup
```
* clear cache, run magento setup

## Usage
* Configuration under Stores -> Configuration: 
  *  => Sales -> Shipping Methods
    * activate Cash-on-Pickup Method
  *  => Sales -> Payments Methods
    * activate Cash-on-Pickup Method
* **Important:** Cash-on-Pickup Payment Method is only available in checkout when Cash-on-Pickup Shipment is selected. 
But Customer can use Cash-on-Pickup Shipment in combination with other payment methods.

### Compatibly
* Magento 2.3.x
* tested on Open Source Edition (Community)
* not tested with MSI

### Contribute
Feel free to fork project and create Pull-Requests. 
Also please report bugs as issue here in GitHub. 