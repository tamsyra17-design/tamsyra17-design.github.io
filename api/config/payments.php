<?php

return [

    /*
    | Account details shown to customers who choose bank transfer. Kept in the
    | environment rather than the public settings table so they are only
    | revealed alongside a placed order.
    */
    'bank_transfer' => [
        'bank_name' => env('BANK_TRANSFER_BANK_NAME'),
        'account_name' => env('BANK_TRANSFER_ACCOUNT_NAME'),
        'account_number' => env('BANK_TRANSFER_ACCOUNT_NUMBER'),
        'iban' => env('BANK_TRANSFER_IBAN'),
        'swift' => env('BANK_TRANSFER_SWIFT'),
    ],

];
