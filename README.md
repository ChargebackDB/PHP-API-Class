# PHP-API-Class
API Class for Chargeback Database API

This is an API class for easy implementation of the Chargeback Database API. 

https://chargebackdb.com/

The purpose for this project is to help sellers be more proactive in protecting themselves against users who might pose a risk. Sellers can use this API to search our database for users who are trying to join their services, and use our "Risk Score"to determine if the payment should process. For more details, visit https://chargebackdb.com/

## Getting Started

To get started is very simple. All that is needed is to include the API class file in your project to be able to use it.

### Installing

Simply include the API class file to your project like below:

```
<?php
  include_once("/inc/api.class.php");
  ...
?>
```

## Usage

Below find some of the simple functions and how to use them.

### Initiate Class

```
<?php
  $apiClass = new ChargeBackAPI("12345ABC"); // Simple with only an api String
  $apiClass = new ChargeBackAPI(array("apiKey"=>"12345ABC")); // Simple with only an api in array
  $apiClass = new ChargeBackAPI(array("apiKey"=>"12345ABC", "debug" => false, "advDebug" => false, "timeout" => 10, "apiVersion" => 1.0)); // Complex with all options
?>
```

### Search Database

```
<?php
  $apiClass->searchDatabaseEmail("example@example.com"); // Searches for email
  $apiClass->searchDatabaseIP("127.0.10.15"); // Searches for IP
  $apiClass->searchDatabaseUsername("username"); // Searches for Username
  $apiClass->searchDatabasePayPalID("PayPalPayerIDNum"); // Searches for PayPal Payer ID
  $apiClass->searchDatabaseTxnID("INVOICE_00006"); // Searches for Invoice number (only owner of report will get results)
?>
```
