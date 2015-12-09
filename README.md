# PayUbiz API for PHP

Simple library for accepting payments via [PayUbiz](https://www.payu.in/).

## Installation

To add this library to your project, simply add a dependency on `v3labs/payubiz` to your project's `composer.json` file. Here is a minimal example of a composer.json file:

    {
        "require": {
            "v3labs/payubiz": "^0.3"
        }
    }
    
## Usage

You'll find a minimal usage example below.

### Initialize purchase

```php
<?php
// purchase.php

use V3labs\PayUbiz\PayUbiz;

require 'vendor/autoload.php';

$payubiz = new PayUbiz(array(
    'merchantId' => 'YOUR_MERCHANT_ID',
    'secretKey'  => 'YOUR_SECRET_KEY',
    'testMode'   => true
));

// All of these parameters are required!
$params = [
    'txnid'       => 'A_UNIQUE_TRANSACTION_ID',
    'amount'      => 10.50,
    'productinfo' => 'A book',
    'firstname'   => 'Peter',
    'email'       => 'abc@example.com',
    'phone'       => '1234567890',
    'surl'        => 'http://localhost/payubiz-php/return.php',
    'furl'        => 'http://localhost/payubiz-php/return.php',
];

// Redirects to PayUbiz
$client->initializePurchase($params)->send();
```

### Finalize purchase

```php
<?php
// return.php

use V3labs\PayUbiz\PayUbiz;

require 'vendor/autoload.php';

$payubiz = new PayUbiz([
    'merchantId' => 'YOUR_MERCHANT_ID',
    'secretKey'  => 'YOUR_SECRET_KEY',
    'testMode'   => true
]);

$result = $payubiz->completePurchase($_POST);

if ($result->checksumIsValid() && $result->getStatus() === PayUbiz::STATUS_COMPLETED) {
  print 'Payment was successful.';
} else {
  print 'Payment was not successful.';
}
```

The `PurchaseResult` has a few more methods that might be useful:

```php
$result = $payubiz->completePurchase($_POST);

// Returns Complete, Pending, Failed or Tampered
$result->getStatus(); 

// Returns an array of all the parameters of the transaction
$result->getParams();

// Returns the ID of the transaction
$result->getTransactionId();

// Returns true if the checksum is correct
$result->checksumIsValid();
```
