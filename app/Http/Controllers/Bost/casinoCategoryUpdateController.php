<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Imports\ImportGames;
use App\Models\Casino\Providers\Provider;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class casinoCategoryUpdateController extends Controller
{
    public function __construct()
    {
    }

    public function updateCasinoCategory(Request $request)
    {
        if($request->hasFile('casinoCategory'))
        {
            /**
             * Key
             * 0 - Provider
             * 1 - Game Name
             * 2 - Category Name
             * 3 - Provider Name for Commission
             * 4 - Commission %
             */
            // $data = Excel::toArray([], $request->file('casinoCategory'));
            
            // foreach ($data as $row) {
            //     dd($row[0][0]);
            // }            
            
            Excel::import(new ImportGames, $request->file('casinoCategory'));
            return 1;
        }
    }

    public function blockProvider ()
    {
        $providers = [
            'Spinmatic',
            'ReelNRG',
            'PragmaticPlay',
            'GoldenRace',
            'HollywoodTV',
            'Betradar',
            'EurasianGamingBingo',
            'Pragmatic Play'
        ];

        try {
            foreach ($providers as $value) {
                $provider = Provider::where('name', $value)->first();
                if($provider) {
                    $provider->status = 1;
                    $provider->save();
                }
            }
            return 'Done';
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
