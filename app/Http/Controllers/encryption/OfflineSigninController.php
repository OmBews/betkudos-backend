<?php

namespace App\Http\Controllers\encryption;

use App\Http\Controllers\Controller;
use App\Models\encryption\OfflineSignin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class OfflineSigninController extends Controller
{
    public function __construct()
    {
        
    }

    public function encrypt(Request $request)
    {
        $transKey = 'Hello';
        $amount = 200.00;

        $transactionHex = Crypt::encryptString($transKey);
        
        $encrypt = new OfflineSignin();
        $encrypt->encrypted_hex = $transactionHex;
        $encrypt->amount = $amount;
        $encrypt->save();
        return $transactionHex; 
    }

    public function decrypt(Request $request)
    {   
        $transactionHex = 'eyJpdiI6IkYzZ2daOGxJMjloSXUzQU1RRytvakE9PSIsInZhbHVlIjoiTkl0enJqVVBCZHBzSjJUZjdLVVk4Zz09IiwibWFjIjoiNTIyNGQyM2E4ZmNmOTZhM2UzZjYyNzM3MmE1OWVhYjg0ODkwODZmOTI3YjM4NGEzYzA2Zjg2NWFlMzdlYjNlMSIsInRhZyI6IiJ9';
        $transaction = Crypt::decryptString($transactionHex);
        return $transaction; 
    }
}
