# laravel-Budpay

[![Latest Stable Version](https://poser.pugx.org/savebills/laravel-Budpay/v/stable.svg)](https://packagist.org/packages/savebills/laravel-Budpay)
[![License](https://poser.pugx.org/savebills/laravel-Budpay/license.svg)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/savebills/laravel-Budpay.svg)](https://travis-ci.org/savebills/laravel-Budpay)
[![Quality Score](https://img.shields.io/scrutinizer/g/savebills/laravel-Budpay.svg?style=flat-square)](https://scrutinizer-ci.com/g/savebills/laravel-Budpay)
[![Total Downloads](https://img.shields.io/packagist/dt/savebills/laravel-Budpay.svg?style=flat-square)](https://packagist.org/packages/savebills/laravel-Budpay)

> A Laravel Package for working with Budpay seamlessly

## Installation

[PHP](https://php.net) 5.4+ or [HHVM](http://hhvm.com) 3.3+, and [Composer](https://getcomposer.org) are required.

To get the latest version of Laravel Budpay, simply require it

```bash
composer require savebills/laravel-budpay:dev-master
```

Or add the following line to the require block of your `composer.json` file.

```
"savebills/laravel-budpay": "dev-master"
```

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.



Once Laravel Budpay is installed, you need to register the service provider. Open up `config/app.php` and add the following to the `providers` key.

```php
'providers' => [
    ...
    savebills\Budpay\BudpayServiceProvider::class,
    ...
]
```

> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/savebills/laravel-Budpay#configuration)

* `savebills\Budpay\BudpayServiceProvider::class`

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Budpay' => savebills\Budpay\Facades\Budpay::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="BudPay\BudPayServiceProvider"
```

A configuration-file named `Budpay.php` with some sensible defaults will be placed in your `config` directory:

```<php

return [
    'secret_key' => env('BUDPAY_SECRET_KEY'),  // Your BudPay API Secret Key
    'signature_hmac' => env('BUDPAY_HMAC_SIGNATURE'),  // Your HMAC signature for encryption
];

```


## General payment flow

Though there are multiple ways to pay an order, most payment gateways expect you to follow the following flow in your checkout process:

### 1. The customer is redirected to the payment provider
After the customer has gone through the checkout process and is ready to pay, the customer must be redirected to the site of the payment provider.

The redirection is accomplished by submitting a form with some hidden fields. The form must send a POST request to the site of the payment provider. The hidden fields minimally specify the amount that must be paid, the order id and a hash.

The hash is calculated using the hidden form fields and a non-public secret. The hash used by the payment provider to verify if the request is valid.


### 2. The customer pays on the site of the payment provider
The customer arrives on the site of the payment provider and gets to choose a payment method. All steps necessary to pay the order are taken care of by the payment provider.

### 3. The customer gets redirected back to your site
After having paid the order the customer is redirected back. In the redirection request to the shop-site some values are returned. The values are usually the order id, a payment result and a hash.

The hash is calculated out of some of the fields returned and a secret non-public value. This hash is used to verify if the request is valid and comes from the payment provider. It is paramount that this hash is thoroughly checked.


## Usage

Open your .env file and add your public key, secret key, merchant email and payment url like so:

```php
secret_key=xxxxxxxxxxxxx
signature_hmac=xxxxxxxxxxxxx
```
*If you are using a hosting service like heroku, ensure to add the above details to your configuration variables.*

Set up routes and controller methods like so:

Note: Make sure you have `/payment/callback` registered in Budpay Dashboard [https://dashboard.Budpay.co/#/settings/developer](https://dashboard.Budpay.co/#/settings/developer) like so:

![payment-callback](https://cloud.githubusercontent.com/assets/2946769/12746754/9bd383fc-c9a0-11e5-94f1-64433fc6a965.png)

```php
// Laravel 5.1.17 and above
Route::post('/pay', 'PaymentController@redirectToGateway')->name('pay');
```

OR

```php
Route::post('/pay', [
    'uses' => 'PaymentController@redirectToGateway',
    'as' => 'pay'
]);
```
OR

```php
// Laravel 8 & 9
Route::post('/pay', [App\Http\Controllers\PaymentController::class, 'redirectToGateway'])->name('pay');
```


```php
Route::get('/payment/callback', 'PaymentController@handleGatewayCallback');
```

OR

```php
// Laravel 5.0
Route::get('payment/callback', [
    'uses' => 'PaymentController@handleGatewayCallback'
]);
```

OR

```php
// Laravel 8 & 9
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'handleGatewayCallback']);
```

```php
<?php

namespace App\Http\Controllers;
use BudPay\BudPayService;
use Illuminate\Http\Request;

class PaymentController
{
    protected $budPay;

    public function __construct(BudPayService $budPay)
    {
        $this->budPay = $budPay;
    }

    public function initializePayment(Request $request)
    {
        $amount = $request->input('amount');
        $callbackUrl = route('payment.callback');
        $customerName = $request->input('name');
        $customerEmail = $request->input('email');

        $response = $this->budPay->processPayment($amount, $callbackUrl, $customerName, $customerEmail);

        return response()->json($response);
    }

    public function createPaymentLink(Request $request)
    {
        $amount = $request->input('amount');
        $currency = 'NGN';
        $name = $request->input('name');
        $description = 'Payment for service';
        $redirectUrl = route('payment.success');

        $response = $this->budPay->createBudPayPaymentLink($amount, $currency, $name, $description, $redirectUrl);

        return response()->json($response);
    }
}

```

```php
/**
 *  In the case where you need to pass the data from your 
 *  controller instead of a form
 *  Make sure to send:
 *  required: email, amount, reference, orderID(probably)
 *  optionally: currency, description, metadata
 *  e.g:
 *  
 */

```

Let me explain the fluent methods this package provides a bit here.
```php
/**
 * To Make Payment
 */
Budpay::processPayment($amount, $callback, $customerName, $customerEmail);

/**
 * Verify all Payment
 */
Budpay::verifyPayment($transactionId);



/**
 * Create Payment Link
 */
Budpay::createBudPayPaymentLink($amount, $currency, $name, $description, $redirectUrl);

/**
 * Create Bulk Payment
 */
Budpay::bulkBankTransfe(array $transfers);


/**
 * Fetch Payment Status
 */
Budpay::fetchPayoutStatus($reference);


/**
 * Get Wallet Balance
 */
Budpay::fetchWalletBalance($currency);


/**
 * Single Payout
 */
Budpay::singlePayout($data);



/**
 * Fetch Bank List
 */
Budpay::fetchBankList();


/**
 * Verify Account Number
 */
Budpay::fetchVerifyaccount(($bankcode,$accountnumber);



/**
 * To Fetch Airtime Payment
 */
Budpay::fetchAirtimeList();


/**
 * To Topup Airtime Payment
 */
Budpay::processAirtime($provider, $number, $amount, $reference);


/**
 * Get Internet
 */
Budpay::fetchDataList();



/**
 * Get Internet provider
 */
Budpay::fetchDataList($provider);



/**
 * Get Buy Internet
 */
Budpay::processdata($provider, $number, $planId, $reference);


/**
 * Get Tv
 */
Budpay::fetchTvList();


/**
 * Get Tv Provider
 */
Budpay::fetchTvroviderList($provider)


/**
 * Get PayTv
 */
Budpay::processPaytv($provider, $number, $code, $reference)



```

```html
<form method="POST" action="{{ route('pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
    <div class="row" style="margin-bottom:40px;">
        <div class="col-md-8 col-md-offset-2">
            <p>
                <div>
                    Lagos Eyo Print Tee Shirt
                    ₦ 2,950
                </div>
            </p>
            <input type="hidden" name="email" value="otemuyiwa@gmail.com"> {{-- required --}}
            <input type="hidden" name="orderID" value="345">
            <input type="hidden" name="amount" value="800"> {{-- required in kobo --}}
            <input type="hidden" name="quantity" value="3">
            <input type="hidden" name="currency" value="NGN">
            <input type="hidden" name="metadata" value="{{ json_encode($array = ['key_name' => 'value',]) }}" > {{-- For other necessary things you want to add to your payload. it is optional though --}}
            <input type="hidden" name="reference" value="{{ Budpay::genTranxRef() }}"> {{-- required --}}
            
            <input type="hidden" name="split_code" value="SPL_EgunGUnBeCareful"> {{-- to support transaction split. more details https://Budpay.com/docs/payments/multi-split-payments/#using-transaction-splits-with-payments --}}
            <input type="hidden" name="split" value="{{ json_encode($split) }}"> {{-- to support dynamic transaction split. More details https://Budpay.com/docs/payments/multi-split-payments/#dynamic-splits --}}
            {{ csrf_field() }} {{-- works only when using laravel 5.1, 5.2 --}}

            <input type="hidden" name="_token" value="{{ csrf_token() }}"> {{-- employ this in place of csrf_field only in laravel 5.0 --}}

            <p>
                <button class="btn btn-success btn-lg btn-block" type="submit" value="Pay Now!">
                    <i class="fa fa-plus-circle fa-lg"></i> Pay Now!
                </button>
            </p>
        </div>
    </div>
</form>
```

When clicking the submit button the customer gets redirected to the Budpay site.

So now we've redirected the customer to Budpay. The customer did some actions there (hopefully he or she paid the order) and now gets redirected back to our shop site.

Budpay will redirect the customer to the url of the route that is specified in the Callback URL of the Web Hooks section on Budpay dashboard.

We must validate if the redirect to our site is a valid request (we don't want imposters to wrongfully place non-paid order).

In the controller that handles the request coming from the payment provider, we have

`Budpay::getPaymentData()` - This function calls the verification methods and ensure it is a valid transaction else it throws an exception.

You can test with these details

```bash
Card Number: 4123450131001381
Expiry Date: any date in the future
CVV: 883
```

## Todo

* Charge Returning Customers
* Add Comprehensive Tests
* Implement Transaction Dashboard to see all of the transactions in your laravel app

## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## How can I thank you?

Why not star the github repo? I'd love the attention! Why not share the link for this repository on Twitter or HackerNews? Spread the word!

Don't forget to [follow me on twitter](https://twitter.com/savebills)!

Thanks!
Prosper Otemuyiwa.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
#   s a v e b i l l s 
 
 
