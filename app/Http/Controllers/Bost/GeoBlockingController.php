<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeoBlockingController extends Controller
{
    public function getCountryList(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer|in:20,50,200',
            'filter' => 'nullable|string',
            'search' => 'nullable|string'
        ]);

        $query = DB::table('countries');

        $denyCountry = ['01', '02', '03', '04'];  //'gs', 'bq', 'fr', 'nl', 'gb'
        $perPage = $request->per_page ?? 20;
        $filter = $request->filter;
        $search = $request->search;

        if ($filter) {
            if ($filter === 'country') {
                $query->where('name', 'LIKE', '%' . $search . '%');
            }
        }

        $data = $query->whereNotIn('code', $denyCountry)->orderBy('name')->get();
        return array_chunk($data->toArray(), (ceil(count($data)/3)));
    }

    public function changeCountryStatus(Request $request)
    {
        $request->validate([
            'cid' => 'integer|required',
            'status' => 'integer|required',
        ]);

        try {
            $country = DB::table('countries')
                ->where('id', $request->cid)
                ->update(['block_status' => $request->status]);

            if($country) {
                return 1;
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *  GeoIp - $ipInfo->country;, $ipInfo->continent;, $ipInfo->currency;
     */
    public function checkGeoLocation(Request $request)
    {
        try {
            $ip = $request->ip(); // To get ip address
            $ipInfo = geoip()->getLocation($ip);
            $iso_code = strtolower($ipInfo->iso_code);
            
            $country = DB::table('countries')
                ->where(['code' => $iso_code, 'block_status' => 1])->count();
            if ($country > 0) {
                return 1;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getMaintenance()
    {
        return SiteSetting::first();
    }
}
