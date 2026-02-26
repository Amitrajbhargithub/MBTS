<?php

return [
    'user' => env('SMS_USER', 'mbts'),
    'password' => env('SMS_PASSWORD', '3ICNUD47'),
    'url' => env('SMS_URL', 'http://103.10.234.154/vendorsms/pushsms.aspx'),
    'sid' => env('SMS_SID', 'MSGMOB'),
    'lead' => env('SMS_LEAD_TEMPLATE', 'Hi, Welcome to MBTS Broadband!. Your OTP for secure login is: 32433. Regards MBTS Broadband Pvt. Ltd MSG'),
    'payment' => env('SMS_PAYMENT_TEMPLATE', 'Dear {customer_name}, we have received the amount of Rs.{amount}/- for internet and balance is Rs.{amount}/-'),
];
