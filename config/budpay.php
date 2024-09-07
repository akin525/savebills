<?php
// config/budpay.php

return [
    'secret_key' => env('BUDPAY_SECRET_KEY'),  // Your BudPay API Secret Key
    'signature_hmac' => env('BUDPAY_HMAC_SIGNATURE'),  // Your HMAC signature for encryption
];
