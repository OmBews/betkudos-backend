<?php

namespace App\Http\Controllers;

use App\Blockchain\Jobs\ProcessIPNJob;
use App\Services\IPNService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IPNController extends Controller
{
    public function payment(Request $request, string $txid)
    {
        $ip = $request->ip();

        Log::info("{$ip}");
        
        ProcessIPNJob::dispatch($txid);
    }
}
