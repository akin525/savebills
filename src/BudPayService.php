<?php

namespace BudPay;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class BudPayService
{
    protected $secretKey;
    protected $signatureHMACSHA512;

    public function __construct()
    {
        $this->secretKey = config('budpay.secret_key');
        $this->signatureHMACSHA512 = config('budpay.signature_hmac');
    }

    public function processPayment($amount, $callback, $customerName, $customerEmail)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/transaction/initialize';

        $data = [
            'amount' => $amount,
            'callback' => $callback,
            'name' => $customerName,
            'email' => $customerEmail,
        ];

        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function createBudPayPaymentLink($amount, $currency, $name, $description, $redirectUrl)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/create_payment_link';

        $postData = [
            'amount' => $amount,
            'currency' => $currency,
            'name' => $name,
            'description' => $description,
            'redirect' => $redirectUrl,
        ];

        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $postData,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function bulkBankTransfer(array $transfers)
    {
        $url = 'https://api.budpay.com/api/v2/bulk_bank_transfer';

        $data = [
            'currency' => 'NGN',
            'transfers' => $transfers,
        ];

        return $this->makeRequest('POST', $url, $data);
    }

    public function fetchPayoutStatus($reference)
    {
        $url = 'https://api.budpay.com/api/v2/payout/' . $reference;
        return $this->makeRequest('GET', $url);
    }

    public function fetchWalletBalance($currency)
    {
        $url = 'https://api.budpay.com/api/v2/wallet_balance/' . $currency;
        return $this->makeRequest('GET', $url);
    }

    public function singlePayout($data)
    {
        $url = 'https://api.budpay.com/api/v2/bank_transfer';
        return $this->makeRequest('POST', $url, $data);
    }

    public function fetchBankList()
    {
        $url = 'https://api.budpay.com/api/v2/bank_list/NGN';
        return $this->makeRequest('GET', $url);
    }

    public function fetchVerifyTv($provider, $number)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/tv/validate';
        $data = [
            'provider' => $provider,
            'number' => $number,
        ];


        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function fetchAirtimeList()
    {
        $url = 'https://api.budpay.com/api/v2/airtime';
        return $this->makeRequest('GET', $url);
    }

    public function processAirtime($provider, $number, $amount, $reference)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/airtime/topup';

        $data = [
            'provider' => $provider,
            'number' => $number,
            'amount' => $amount,
            'reference' => $reference,
        ];

        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Encryption: ' . $this->signatureHMACSHA512,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function fetchDataList()
    {
        $url = 'https://api.budpay.com/api/v2/internet';
        return $this->makeRequest('GET', $url);
    }
    public function fetchDataproviderList($provider)
    {
        $url = 'https://api.budpay.com/api/v2/internet/plans/' . urlencode($provider);
        return $this->makeRequest('GET', $url);
    }

    public function processdata($provider, $number, $planId, $reference)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/internet/data';

        $data = [
            'provider' => $provider,
            'number' => $number,
            'plan_id' => $planId,
            'reference' => $reference,
        ];

        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Encryption: ' . $this->signatureHMACSHA512,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function fetchTvList()
    {
        $url = 'https://api.budpay.com/api/v2/tv';
        return $this->makeRequest('GET', $url);
    }

    public function fetchTvroviderList($provider)
    {
        $url = 'https://api.budpay.com/api/v2/tv/packages/' . urlencode($provider);
        return $this->makeRequest('GET', $url);
    }
    public function verifyPayment($transactionId)
    {
        $url = 'https://api.budpay.com/api/v2/transaction/verify/' . $transactionId;
        return $this->makeRequest('GET', $url);
    }
    public function fetchVerifyaccount($bankcode,$accountnumber)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/account_name_verify';
        $data = [
            'bank_code' => $bankcode,
            'account_number' => $accountnumber,
        ];


        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function processPaytv($provider, $number, $code, $reference)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/tv/pay';

        $data = [
            'provider' => $provider,
            'number' => $number,
            'code' => $code,
            'reference' => $reference
        ];

        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Encryption: ' . $this->signatureHMACSHA512,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function fetchElectricity()
    {
        $url = 'https://api.budpay.com/api/v2/electricity';
        return $this->makeRequest('GET', $url);
    }

    public function fetchVerifyElectricity($provider, $number, $type)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/electricity/validate';
        $data = [
            'provider' => $provider,
            'number' => $number,
            'type' => $type
        ];


        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function processPayElectricity($provider, $number, $amount, $reference, $type)
    {
        $apiUrl = 'https://api.budpay.com/api/v2/electricity/recharge';

        $data =  [
            'provider' => $provider,
            'number' => $number,
            'amount' => $amount,
            'reference' => $reference,
            'type' => $type
        ];

        try {
            $client = new Client();
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->secretKey,
                    'Encryption: ' . $this->signatureHMACSHA512,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    protected function makeRequest($method, $url, $data = [])
    {
        // Use conditionals instead of dynamic method invocation
        if ($method === 'POST') {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Encryption' => $this->signatureHMACSHA512,
                'Content-Type' => 'application/json',
            ])->post($url, $data);
        } else {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Encryption' => $this->signatureHMACSHA512,
                'Content-Type' => 'application/json',
            ])->get($url);
        }

        if ($response->successful()) {
            return $response->json();
        }

        return $response->throw()->json();
    }
}
