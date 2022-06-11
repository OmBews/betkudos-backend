<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Countries\Country;
use App\Models\kyc\Document;
use App\Models\kyc\UserKyc;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KycController extends Controller
{

    public function getCountry()
    {
        // Block some country code '01', '02', '03', '04', 'ax', 'ai', 'ci', 'gs', 'st', 're', 'cw', 'bl', 'bq', 'fr', 'nl', 'gb'
        $denyCountry = ['01', '02', '03', '04'];
        return Country::whereNotIn('code', $denyCountry)->orderBy('name')->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Level one details
    |--------------------------------------------------------------------------
    */
    public function getLevelOne(Request $request)
    {
        $request->validate([
            'email' => 'String|required'
        ]);

        try {
            $userId = $this->getUserId($request->email);
            $levelOne = UserKyc::where('user_id', $userId)->first();
            if (!$levelOne) {
                $query = new UserKyc();
                $query->user_id = $userId;
                $query->save();
                return $query->first();
            }
            return $levelOne;
        } catch (\Exception $e) {
            $failed = ['message' => "Unable to get kyc level one information"];
            return response()->json($failed, 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To store KYC Level One data
    |--------------------------------------------------------------------------
    */
    public function storeLevelOne(Request $request)
    {
        $request->validate([
            'fname' => 'nullable|string',
            'lname' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|string',
            'username' => 'nullable|string|required',
        ]);

        try {
            $userId = $this->getUserId($request->email);
            $kyc = UserKyc::where('user_id', $userId)->first();
            $kyc->level = 1;
            $kyc->kyc_status = 1;
            $kyc->fname = $request->fname;
            $kyc->lname = $request->lname;
            $kyc->year = $request->year;
            $kyc->month = $request->month;
            $kyc->date = $request->date;
            $kyc->city = $request->city;
            $kyc->pin = $request->pin;
            $kyc->address = $request->address;
            $kyc->county = $request->country['code'];
            $kyc->country = $request->country['name'];
            $kyc->save();

            return response()->json(['status' => $kyc->level, 'message' => 'DETAILS UPDATED!'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error! Please try with correct data', 'error' => $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To store KYC Level Two data
    |--------------------------------------------------------------------------
    */
    public function storeLevelTwo(Request $request)
    {
        $request->validate([
            'email' => 'string|nullable'
        ]);

        try {
            $userId = $this->getUserId($request->email);

            if ($request->hasFile('id_front')) {
                $idFront = new Document();
                $idFront->user_id = $userId;
                $idFront->type = 'Identity';
                $idFront->mime = $request->file('id_front')->getClientOriginalExtension();
                $idFront->name = $request->file('id_front')->getClientOriginalName();
                $idFront->path = $request->file('id_front')->store('identity', 'public');
                $idFront->status = 2;
                $idFront->save();
            }

            if ($request->hasFile('id_back')) {
                $idBack = new Document();
                $idBack->user_id = $userId;
                $idBack->type = 'Identity Back';
                $idBack->mime = $request->file('id_back')->getClientOriginalExtension();
                $idBack->name = $request->file('id_back')->getClientOriginalName();
                $idBack->path = $request->file('id_back')->store('identity', 'public');
                $idBack->status = 2;
                $idBack->save();
            }

            if ($request->hasFile('address')) {
                $address = new Document();
                $address->user_id = $userId;
                $address->type = 'Proof of Address';
                $address->mime = $request->file('address')->getClientOriginalExtension();
                $address->name = $request->file('address')->getClientOriginalName();
                $address->path = $request->file('address')->store('address', 'public');
                $address->status = 2;
                $address->save();
            }

            // Update level two
            $this->updateLevel($userId, 2);

            return response()->json(['status' => 1, 'message' => 'Documents uploaded!'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error! Please try again with valid documents', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUserId(string $email): int
    {
        $user = User::where('email', $email)->first();
        return $user->id;
    }

    public function updateLevel(int $id, int $level)
    {
        $query = UserKyc::where('user_id', $id)->first();
        $query->level = $level;
        $query->save();
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | To store KYC Level Three data
    |--------------------------------------------------------------------------
    */
    public function storeLevelThree(Request $request)
    {
        $request->validate([
            'email' => 'string|nullable'
        ]);
        $userId = $this->getUserId($request->email);
        try {
            if ($request->hasFile('address')) {
                $document = new Document();
                $document->user_id = $userId;
                $document->mime = $request->file('address')->getClientOriginalExtension();
                $document->name = $request->file('address')->getClientOriginalName();
                $document->path = $request->file('address')->store('funds', 'public');
                $document->type = 'Proof of Funds';
                $document->status = 3;
                $document->save();

                $this->updateLevel($userId, 3);

                return response()->json(['status' => 1, 'message' => 'Documents uploaded!'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error! Please try again with valid documents', 'error' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To get list of document with user details
    |--------------------------------------------------------------------------
    */
    public function getDocuments(Request $request)
    {
        $request->validate([
            'email' => 'string|nullable|required'
        ]);

        $email = $request->email;

        try {
            $query = User::query();
            $query->where('email', $email);
            $query->withCount(['userDocs as levelTwo' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(status),0)'));
                $q->where('type', '!=', 'Proof of Funds');
            }]);
            $query->withCount(['userDocs as levelThree' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(status),0)'));
                $q->where('type', 'Proof of Funds');
            }]);
            return $query->with('userKyc', 'userDocs')->first();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
