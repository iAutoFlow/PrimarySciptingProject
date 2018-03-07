<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/30/2017
 * Time: 2:15 PM
 */

$results = [];

//BLOCKLY START CODE
//Shopify API stuff
$API_KEY = 'da3d76df3dbc00f3d2ea45c52cf578bd';
$PASSWORD = '35c9b25030258bb045578de683a31c81';
$STORE_URL = 'luxion.myshopify.com';

$getFullProductInformation = function($API_KEY, $PASSWORD, $STORE_URL, $orderID) {
    //get full information of product
    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/products/' . $orderID. '.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $productFullInfo = json_decode(curl_exec($session));
    curl_close($session);
    ///
    return $productFullInfo;
};

try {
    //stop running OrdedPaid script on 90 sec, to prevent conflicts between OrderCreate and OrderPaid
    sleep(90);

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    $result = json_decode($triggerData);
    $orderID = $result->id;

    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/orders/' . $orderID . '.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);

    $order_json = json_decode($response);

    $orderObj = json_encode($order_json->order);

    $webhookContent = $orderObj .= "ENDOFID";

//    $ret = file_put_contents('orderData.txt', " Order Paid Hook: ".date('Y-m-d H:i:s')." - ".$webhookContent, FILE_APPEND | LOCK_EX);

///$data = file_get_contents ("orderData.txt");
    $arr = explode('ENDOFID', $webhookContent);

    for ($i = 0; $i < sizeof($arr); $i++) {
        if (strpos($arr[$i], 'email') == true) {
            $json = $arr[$i] . $arr[$i + 1];
            break;
        }
    }
    $result = json_decode($json);

    // define("USERNAME", "stephanie@luxion.com");
    // define("PASSWORD", "luxion9000");
    // define("SECURITY_TOKEN", "Pj8rDT98GtyhzUciLthGFFKf");

    define("USERNAME", "theresa@luxion.com");
    define("PASSWORD", "Lady1985!");
    define("SECURITY_TOKEN", "DxgUhKHulesdh5qGYUVClKnh");

    require_once(base_path() . '/public/shopifySalesForce/soapclient/SforceEnterpriseClient.php');

    $mySforceConnection = new SforceEnterpriseClient();
    $mySforceConnection->createConnection(base_path() . "/public/shopifySalesForce/soapclient/enterprise.wsdl.xml");
    $mySforceConnection->login(USERNAME, PASSWORD . SECURITY_TOKEN);

    $webhookArr = $result;

    $AccountName = null;

    if (isset($webhookArr->payment_gateway_names[0]))
        $paymentType = $webhookArr->payment_gateway_names[0];
    $paymentStatus = $webhookArr->financial_status;
    //Change Account Name
    if (!empty($webhookArr->billing_address)) {
        $AccountName = $webhookArr->billing_address->company;
    } else if (!empty($webhookArr->shipping_address)) {
        $AccountName = $webhookArr->shipping_address->company;
    } else {
        $AccountName = $webhookArr->default_address->company;
    }
    //remove ' symbol from Account Name
    $AccountName = str_replace("'", "", $AccountName);

    //Change Opportunity Name
    if (!empty($webhookArr->billing_address->company) && empty($webhookArr->shipping_address->company)) {
        $OpportunityName = $webhookArr->billing_address->company;
    } else if (!empty($webhookArr->shipping_address->company) && empty($webhookArr->billing_address->company)) {
        $OpportunityName = $webhookArr->shipping_address->company;
    } else if (!empty($webhookArr->shipping_address->company) && !empty($webhookArr->billing_address->company) && (strtolower($webhookArr->shipping_address->company) == strtolower($webhookArr->billing_address->company))) {
        $OpportunityName = $webhookArr->billing_address->company;
    } else if (!empty($webhookArr->shipping_address->company) && !empty($webhookArr->billing_address->company) && (strtolower($webhookArr->shipping_address->company) != strtolower($webhookArr->billing_address->company))) {
        $OpportunityName = $webhookArr->billing_address->company . " - " . $webhookArr->shipping_address->company;
    } else {
        $OpportunityName = $AccountName;
    }
//    $ret = file_put_contents('testinglog.txt', "ORDER PAID - Opp Name: ".$OpportunityName.", Account Name: ".$AccountName, FILE_APPEND | LOCK_EX);

    $customerID = $webhookArr->customer->id;
    $SF_AccountID = null;
    $SalesForceAccountID = null;
    $SalesForceAccountName = null;
    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/customers/' . $customerID . '/metafields.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);
    $customer_json = json_decode($response);

    if ($customer_json->metafields->key == "account_id") {
        $SalesForceAccountID = $customer_json->metafields->value;
    }
    if ($customer_json->metafields->key == "account_name") {
        $SalesForceAccountName = $customer_json->metafields->value;
    }

    if ($SalesForceAccountID) {
        $isNewAccount = false;
        $SF_AccountID = $SalesForceAccountID;
    }

    if (!$SF_AccountID) {
        if (empty($AccountName)) {
            Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Order #" . $webhookArr->order_number . ". Unable to Determine the Account Name.  Billing Address Company, Shipping Address Company, and Customer Default Address Company are all Blank.");
            throw new \RuntimeException("Order #" . $webhookArr->order_number . ". Unable to Determine the Account Name.  Billing Address Company, Shipping Address Company, and Customer Default Address Company are all Blank.");
        }
    }

    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/orders/' . $webhookArr->id . '.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);
    $order_json = json_decode($response);


    $isNFR = false;
    $saleIsSuccess = false;

    $purchasedToMain = array();
    $i = 0;
    $totalAmount = 0.0;
    foreach ($order_json->order->line_items as $line_item) {
        $productFullInfo = $getFullProductInformation($API_KEY, $PASSWORD, $STORE_URL, $line_item->product_id);

        if(strpos($productFullInfo->product->tags, "NFR") !== false) {
            $isNFR = true;
        }
        // if (isset($productFullInfo->product->product_type) && ($productFullInfo->product->product_type == "Add-on" || $productFullInfo->product->product_type == "Product" || $productFullInfo->product->product_type == "Upgrade")) {
        if (isset($productFullInfo->product->product_type)) {
            $totalAmount += ($line_item->quantity * $line_item->price);
        }
        // if ($line_item->sku == "6-9000-NFR") {
        //     $isNFR = true;
        // }
        $query = "Select Id, Name, Shopify_ID__c, ProductCode, Description from product2 where ProductCode = '" . $line_item->sku . "' and isActive = true";
        $response = $mySforceConnection->query($query);

        $purchasedToMain[$i] = array("variantID" => $line_item->variant_id, "mainProductID" => "");


        $purchasedToMain[$i]['mainProductID'] = $response->records[0]->Id;

        $i++;
    }

    if ($order_json->order->total_price <= 0) {

        if ($isNFR) {

            $saleIsSuccess = true;

            $paymentType = "Shopify - NFR";

        } elseif (!$isNFR) {

            $paymentType = "Free Order";
            return "Warning: Payment Type for Order Id " . $webhookArr->order_number . ", is type " . $paymentType;
        }

    }

    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/orders/' . $webhookArr->id . '/transactions.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);
    $txn_json = json_decode($response);

    foreach ($txn_json->transactions as $txn) {

        if (strtolower($txn->kind) == "sale") {

            if ($txn->status == "Success" || $txn->message == "Transaction approved" || $txn->status == "success" ||  $txn->message == "This transaction has been approved") {
                $saleIsSuccess = true;
            }

            if ($txn->gateway == "shopify_payments") {
                $paymentType = "Shopify - Credit Card";
            } else {
                $paymentType = "Shopify - " . $txn->gateway;
            }

            if ($paymentType == "Shopify - " && $order_json->order->total_price <= 0 && $isNFR) {
                $paymentType = "Shopify - NFR";
            }
            $txnMessage = $txn->message;

        }

    }

    // if (!($paymentType != "Shopify - Purchase Order" && $paymentType != "Shopify - Bank Transfer" && $paymentType != "Shopify - Bank Deposit" && $paymentType != "Shopify - NFR" && $paymentType != "Free Order")) {
    //     Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Warning: Payment Type for Order Id " . $webhookArr->order_number . ", is type " . $paymentType . ".  Stopping Processing for On Order Paid.  Purchase Order, Bank Transfers, Bank Deposits, Free Orders, and NFR orders are not applicable for Order Paid.");
    //     throw new \RuntimeException("Warning: Payment Type for Order Id " . $webhookArr->order_number . ", is type " . $paymentType . ".  Stopping Processing for On Order Paid.  Purchase Order, Bank Transfers, Bank Deposits, Free Orders, and NFR orders are not applicable for Order Paid.");
    // }

    if ($paymentType == "Shopify - Credit Card" && !$saleIsSuccess) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Failed Credit Card Transaction for Order " . $webhookArr->order_number . ", " . $webhookArr->order_name . ".  Message: " . $txnMessage);
        throw new \RuntimeException("Failed Credit Card Transaction for Order " . $webhookArr->order_number . ", " . $webhookArr->order_name . ".  Message: " . $txnMessage);
    }

    $sfForecastCat = "Closed";
    $shopifyEmail = $webhookArr->customer->email;
    $salesforcePricebook2ID = "01sA0000000QTHnIAO";
    $SF_ContactID = "";

    if (!$shopifyEmail) {
        $shopifyEmail = $webhookArr->customer->email;
    }
    $salesForceDiscountPercentage = 0.0;
    if ($order_json->order->total_discounts > 0 && $totalAmount > 0) {
        $salesForceDiscountPercentage = (($order_json->order->total_discounts / $totalAmount) * 100);
    }

    if ($order_json->order->discount_codes) {
        $discountCode = $order_json->order->discount_codes[0]->code;
    }

    if ($SalesForceAccountID) {
        $isNewAccount = false;
        $SF_AccountID = $SalesForceAccountID;
    }

    if (!empty($AccountName)) {
        $AccountName = trim($AccountName);
    }

    if (!$SF_AccountID) {
        if (empty($AccountName)) {
            Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Unable to Determine the Account Name.  Billing Address Company, Shipping Address Company, and Customer Default Address Company are all Blank.");
            throw new \RuntimeException("Unable to Determine the Account Name.  Billing Address Company, Shipping Address Company, and Customer Default Address Company are all Blank.");
        }


        $query = "SELECT id, Name, BillingState, ShippingState from Account where Name = '" . $AccountName . "' ORDER BY CreatedDate desc";
        $response = $mySforceConnection->query($query);
        $textResponse = print_r($response, true);

        $isNewAccount = false;

        if (!$response) {
            Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
            throw new \RuntimeException("Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
        }

        if ($response->size == 0) {
            $isNewAccount = true;
        }
        if ($response->size >= 1) {
            $isNewAccount = false;
            $SF_AccountID = $response->records[0]->Id;
            if (!$SF_AccountID) {
                Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Unable to find Account ID");
                throw new \RuntimeException("Unable to find Account ID");
            }
            //updating strange symbols in Account Billing|Shipping State
            $account[0] = new stdclass();
            $account[0]->Id = $SF_AccountID;
            $account[0]->fieldsToNull = [];
            //if (strlen($response->records[0]->BillingState) != mb_strlen($response->records[0]->BillingState, 'utf-8') || ($response->records[0]->BillingState != $order_json->order->billing_address->city)) {
            if (strlen($response->records[0]->BillingState) != mb_strlen($response->records[0]->BillingState, 'utf-8')) {
                $account[0]->fieldsToNull[] = "BillingStateCode";
            }
            //if (strlen($response->records[0]->ShippingState) != mb_strlen($response->records[0]->ShippingState, 'utf-8') || ($response->records[0]->ShippingState != $order_json->order->shipping_address->city)) {
            if (strlen($response->records[0]->ShippingState) != mb_strlen($response->records[0]->ShippingState, 'utf-8')) {
                $account[0]->fieldsToNull[] = "ShippingStateCode";
            }
            $mySforceConnection->update($account, 'Account');
        }
        // if ($response->size > 1) {
        //     Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error on Account Lookup found " . $response->size . " Accounts using SOQL=" . $query);
        //     throw new \RuntimeException("Error on Account Lookup found " . $response->size . " Accounts using SOQL=" . $query);
        // }
    }

    if (!$isNewAccount) {
        $query = "SELECT id, Name, BillingState, ShippingState from Account where id = '" . $SF_AccountID . "'";
        $responseA1 = $mySforceConnection->query($query);

        if (!$response) {
            Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
            throw new \RuntimeException("Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
        }
        //updating strange symbols in Account Billing|Shipping State
        $account[0] = new stdclass();
        $account[0]->Id = $SF_AccountID;
        $account[0]->fieldsToNull = [];

        //if (strlen($response->records[0]->BillingState) != mb_strlen($response->records[0]->BillingState, 'utf-8') || ($response->records[0]->BillingState != $order_json->order->billing_address->city)) {
        if (strlen($response->records[0]->BillingState) != mb_strlen($response->records[0]->BillingState, 'utf-8')) {
            $account[0]->fieldsToNull[] = 'BillingStateCode';
        }
        //if (strlen($response->records[0]->ShippingState) != mb_strlen($response->records[0]->ShippingState, 'utf-8') || ($response->records[0]->ShippingState != $order_json->order->shipping_address->city)) {
        if (strlen($response->records[0]->ShippingState) != mb_strlen($response->records[0]->ShippingState, 'utf-8')) {
            $account[0]->fieldsToNull[] =  'ShippingStateCode';
        }
        $mySforceConnection->update($account, 'Account');
    }

    if ($isNewAccount) {
        //Create Account
        $account = array();
        $account[0] = new stdclass();
        $account[0]->Name = $AccountName;
        $account[0]->Description = "Order Name: " . $order_json->order->customer->first_name . " " . $order_json->order->customer->last_name . "\nNote: " . $order_json->order->note;
        $account[0]->Phone = $webhookArr->customer->default_address->phone;
        $account[0]->BillingStreet = $order_json->order->shipping_address->address1;
        $account[0]->BillingCity = $order_json->order->shipping_address->city;
        if ($order_json->order->shipping_address->country == "Australia" || $order_json->order->shipping_address->country == "Brazil" || $order_json->order->shipping_address->country == "Canada" || $order_json->order->shipping_address->country == "India" || $order_json->order->shipping_address->country == "United States") {
            $account[0]->BillingState = $order_json->order->shipping_address->province;
        }
        $account[0]->BillingCountry = $order_json->order->shipping_address->country;
        $account[0]->BillingPostalCode = $order_json->order->shipping_address->zip;
        $account[0]->ShippingStreet = $order_json->order->shipping_address->address1;
        $account[0]->ShippingCity = $order_json->order->shipping_address->city;
        if ($order_json->order->shipping_address->country == "Australia" || $order_json->order->shipping_address->country == "Brazil" || $order_json->order->shipping_address->country == "Canada" || $order_json->order->shipping_address->country == "India" || $order_json->order->shipping_address->country == "United States") {
            $account[0]->ShippingState = $order_json->order->shipping_address->province;
        }
        $account[0]->ShippingCountry = $order_json->order->shipping_address->country;
        $account[0]->ShippingPostalCode = $order_json->order->shipping_address->zip;
        $account[0]->fieldsToNull = [];
        //updating strange symbols in Account Billing|Shipping State
        if (strlen($account[0]->BillingState) != mb_strlen($account[0]->BillingState, 'utf-8')) {
            $account[0]->fieldsToNull[] = "BillingStateCode";
        }
        if (strlen($account[0]->ShippingState) != mb_strlen($account[0]->ShippingState, 'utf-8')) {
            $account[0]->fieldsToNull[] = "ShippingStateCode";
        }
        $responseA = $mySforceConnection->create($account, 'Account');
        $SF_AccountID = $responseA[0]->id;
    }

    if ($shopifyEmail) {
        $query = "SELECT id, Name from Contact where email = '" . $shopifyEmail . "'";
        if (!$isNewAccount) {
            $query .= " and AccountID = '" . $SF_AccountID . "'";
        }
        $response = $mySforceConnection->query($query);

        if (!$response) {
            Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
            throw new \RuntimeException("Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
        }

        if ($response->size == 0) {
            $isNewContact = true;
        }
        if ($response->size == 1) {
            $isNewContact = false;
            $SF_ContactID = $response->records[0]->Id;
        }

        if (empty($SF_ContactID)) {
            $query = "select id, Name, Email, FirstName, LastName from Contact where Email = '" . $shopifyEmail . "' and FirstName = '" . str_replace("'", "\'", $order_json->order->customer->first_name) . "' and LastName = '" . str_replace("'", "\'", $order_json->order->customer->last_name) . "' ORDER BY CreatedDate desc";
            $response = $mySforceConnection->query($query);
            if (!$response) {
                Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
                throw new \RuntimeException("Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
            }

            if ($response->size == 0) {
                $isNewContact = true;
            }
            if ($response->size >= 1) {
                $isNewContact = false;
                $SF_ContactID = $response->records[0]->Id;
            }
            // if ($response->size > 1) {
            //     Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error on Contact Lookup, found " . $response->size . " Contacts using SOQL=" . $query);
            //     throw new \RuntimeException("Error on Contact Lookup, found " . $response->size . " Contacts using SOQL=" . $query);
            // }
        }

        $query = "select id, Name, Email, FirstName, LastName from Contact where Email = '" . $shopifyEmail . "' and FirstName = '" . str_replace("'", "\'", $order_json->order->customer->first_name) . "' and LastName = '" . str_replace("'", "\'", $order_json->order->customer->last_name) . "'";
        if (!$isNewAccount) {
            $query .= " and AccountID = '" . $SF_AccountID . "'";
        }
        $query .= " ORDER BY CreatedDate desc";
        if (!$response) {
            Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
            throw new \RuntimeException("Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
        }

        if ($response->size == 0) {
            $isNewContact = true;
        }
        if ($response->size >= 1) {
            $isNewContact = false;
            $SF_ContactID = $response->records[0]->Id;
        }
        // if ($response->size > 1) {
        //     Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error on Contact Lookup, found " . $response->size . " Contacts using SOQL=" . $query);
        //     throw new \RuntimeException("Error on Contact Lookup, found " . $response->size . " Contacts using SOQL=" . $query);
        // }

        if ($isNewContact) {
            $contact = array();
            $contact[0] = new stdClass();
            // $contact[0]->Name=$AccountName;
            $contact[0]->FirstName = $webhookArr->customer->first_name;
            $contact[0]->LastName = $webhookArr->customer->last_name;
            $contact[0]->AccountId = $SF_AccountID;
            $contact[0]->Description = "Shopify Customer Id: " . $webhookArr->customer->id;
            $contact[0]->Phone = $webhookArr->customer->default_address->phone;
            $contact[0]->Email = $shopifyEmail;
            $contact[0]->MailingStreet = $webhookArr->customer->default_address->address1 . "\n" . $webhookArr->customer->default_address->address2;
            $contact[0]->MailingCity = $webhookArr->customer->default_address->city;
            if ($webhookArr->customer->default_address->country == "Australia" || $webhookArr->customer->default_address->country == "Brazil" || $webhookArr->customer->default_address->country == "Canada" || $webhookArr->customer->default_address->country == "India" || $webhookArr->customer->default_address->country == "United States") {
                $contact[0]->MailingState = $webhookArr->customer->default_address->province;
            }
            $contact[0]->MailingPostalCode = $webhookArr->customer->default_address->zip;
            $contact[0]->MailingCountry = $webhookArr->customer->default_address->country;
            $contact[0]->KeyShot_2_Serial_Code__c = $webhookArr->customer->note;
            //updating strange symbols in Account Billing|Shipping State
            $account[0]->fieldsToNull = [];
            if (strlen($contact[0]->MailingState) != mb_strlen($contact[0]->MailingState, 'utf-8')) {
                $contact[0]->fieldsToNull[] =  "MailingStateCode";
            }
            $responseC = $mySforceConnection->create($contact, 'Contact');

            $SF_ContactID = $responseC[0]->id;
        }
    }

    $query = "select id, OwnerId, Type from account where id = '" . $SF_AccountID . "'";
    $ownerResponse = $mySforceConnection->query($query);

    if (!$ownerResponse) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
        throw new \RuntimeException("Error Calling SalesForce (SOQL: " . $query . ") - SalesForce Error");
    }

    //get existed opportunity
    $shopifyOrderName = $order_json->order->order_number;
    $query = "SELECT id, name from opportunity where Shopify_Order_Number__c='" . $shopifyOrderName . "'";
    $response = $mySforceConnection->query($query);
    if (!$response) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
        throw new \RuntimeException("Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
    }
    if ($shopifyOrderName && strpos($shopifyOrderName, "#")) {
        $shopifyOrderName = substr($shopifyOrderName, 1);
    }
    // if ($response->size > 0) {
    //     Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Warning: Opportunity Already Created for Shopify Order Id: " . $webhookArr->id . ", Order Name: " . $shopifyOrderName . " - Stopping Processing");
    //     throw new \RuntimeException("Warning: Opportunity Already Created for Shopify Order Id: " . $webhookArr->id . ", Order Name: " . $shopifyOrderName . " - Stopping Processing");
    // }
    $SF_OpportunityID = $response->records[0]->Id;

    $transactionID = $webhookArr->checkout_id && $paymentType == "Shopify - authorize_net" && $paymentStatus == 'paid' ? "c" . $webhookArr->checkout_id . "." . count($txn_json->transactions) : null;
    $SF_AccountOwnerID = $ownerResponse->records[0]->OwnerId;
    $AccountType = $ownerResponse->records[0]->Type;
    $records[0] = new stdClass();
    if ($SF_OpportunityID) {
        $records[0]->Id = $SF_OpportunityID;
    }

    if ($SF_AccountID) {
        $records[0]->AccountID = $SF_AccountID;
    }
    if ($webhookArr->processed_at) {
        $records[0]->CloseDate = $webhookArr->processed_at;
    } else {
        $records[0]->CloseDate = $webhookArr->updated_at;
    }
    if ($webhookArr->closed_at && $paymentStatus == 'paid') {
        $records[0]->Paid_Date__c = $webhookArr->closed_at;
    } else {
        $records[0]->Paid_Date__c = $webhookArr->updated_at;
    }
    $records[0]->StageName = "Closed Won";
    if ($webhookArr->total_price) {
        $records[0]->Amount = $webhookArr->total_price;
    }
    // if ($SF_AccountOwnerID) {
    //     $records[0]->OwnerID = $SF_AccountOwnerID;
    // }
    // //set for all resellers Owner Id 005F0000006G4U8IAK (Niels Sonne Andersen)
    // if ($AccountType == "Reseller" && $isNewAccount) {
    //     $records[0]->OwnerID = "005F0000006G4U8IAK";
    // }
    if ($sfForecastCat) {
        $records[0]->ForecastCategoryName = $sfForecastCat;
    }
    if ($webhookArr->id) {
        $records[0]->Shopify_Order_ID__c = $webhookArr->id;
    }
    if ($order_json->order->order_number) {
        $records[0]->Shopify_Order_Number__c = $order_json->order->order_number;
    }
    if ($paymentType) {
        $records[0]->Purchase_Method__c = $paymentType;
    }
    if (!empty($discountCode)) {
        $records[0]->Coupon_Code__c = $discountCode;
    }
    if ($webhookArr->note) {
        $records[0]->KeyShot_Serial_Code__c = $webhookArr->note;
    }
    if($transactionID) {
        $records[0]->Transaction_ID__c = $transactionID;
    }

    if ($SF_OpportunityID) {
        $responseOpp = $mySforceConnection->update($records, 'Opportunity');
    } else {
        $records[0]->Name = $OpportunityName;
        $responseOpp = $mySforceConnection->create($records, 'Opportunity');
    }

    if(count($responseOpp[0]->errors)){
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error in updating order #" . $order_json->order->order_number . ": " . $responseOpp[0]->errors[0]->message);
        throw new \RuntimeException("Error in updating order #" . $order_json->order->order_number . ": " . $responseOpp[0]->errors[0]->message);
    }
    $SF_OpportunityID = $responseOpp[0]->id;

    unset($getFullProductInformation);
    return [
        'success' => true,
        'response' => print_r($responseOpp)
    ];

} catch (Exception $e) {
    Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', $e->getMessage());
    throw $e;
    // $errorMessage = $e;
    // $curlLogger($errorMessage, $order_json->order->order_number);

//    $ret = file_put_contents('ErrorLog.txt', "Error: ".$e, FILE_APPEND | LOCK_EX);

    // return [
    //     'success' => false,
    //     'response' => print_r($e)
    // ];

}
//BLOCKLY END CODE

