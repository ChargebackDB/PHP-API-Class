<?php

include_once("api.class.php");

$apiClass = new ChargeBackAPI(array("apiKey"=>"12345ABC", "debug"=>true));

echo "Email:<BR>";
echo $apiClass->searchDatabaseEmail("email@example.com");
echo "<hr>IP";
echo $apiClass->searchDatabaseIP("127.0.10.15");
echo "<hr>Username";
echo $apiClass->searchDatabaseUsername("userExample");
echo "<hr>PayPal Payer ID";
echo $apiClass->searchDatabasePayPalID("USD4444");
echo "<hr>";
echo "<hr>Txn ID";
echo $apiClass->searchDatabaseTxnID("0866_57744");
echo "<hr>";
echo "<br>Submitting: . . . <br>";
$submitArray = array(
        "amt"           => 199.90,
        "currency"      => "USD",
        "processor"     => "pp",
        "username"      => "userNameExample",
        "email"         => "email@example.com", // This is the email the user uses on your website.
        "pp_email"      => "email@example.com", // Email that is associated with the paypal. This is included in the IPN call
        "ip"            => "127.0.10.15",
        "pp_PayerID"    => "pp_Hdgh4555HGs",
        "notes"         => "User chargedback 2 days after purchase", // This note is for your use, but will show up in reports. Recommend stating what type of chargeback
        "timestamp"     => time() // This time should be the time of the initial purchase. If it is not, report may be removed as invalid
    );
echo $apiClass->submitReport($submitArray);
echo "<hr>";


?>
