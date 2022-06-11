<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Models\kyc\Document;
use App\Models\kyc\UserKyc;
use App\Models\Users\User;
use Illuminate\Http\Request;

class UserKycController extends Controller
{
    public function __construct()
    {
    }

    /*
    |--------------------------------------------------------------------------
    | To get user list with their address and kyc documents
    |--------------------------------------------------------------------------
    */
    public function filter(Request $request)
    {
        $request->validate([
            'filter' => 'nullable|string',
            'search' => 'nullable|string',
            'status_search' => 'nullable|string'
        ]);

        $perPage = $request->per_page ?? 20;
        $filter = $request->filter;
        $search = explode(',', $request->search);
        $statusSearch = explode(',', $request->status_search);

        try {

            $query = User::query();

            if ($filter) {
                if ($filter == 'level' && $search != '' && !in_array('all', $search)) {
                    $query->whereHas('userKyc', function ($query) use ($search) {
                        $query->whereIn('level', $search);
                    });
                }
                
                if ($filter == 'status' && $statusSearch != '') {
                    $query->whereHas('userKyc', function ($query) use ($statusSearch) {
                        $query->whereIn('kyc_status', $statusSearch);
                    });
                }
            }

            $query->whereHas('userKyc', function ($q) {
                $q->where('level', '!=', 0);
            });

            return $query->with('userKyc', 'userDocs')->paginate($perPage);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To store the documents
    |--------------------------------------------------------------------------
    */
    public function storeDocument(Request $request)
    {
        $request->validate([
            'note' => 'nullable|string|max:250',
            'document' => 'nullable|string|in:Identity,Proof of Address,Proof of Funds,Other',
            'user_id' => 'integer'
        ]);

        try {
            $document = new Document();
            $document->user_id = $request->user_id;
            $document->notes = $request->note;
            $document->type = $request->document;

            if ($request->hasFile('file')) {
                $document->mime = $request->file('file')->getClientOriginalExtension();
                $document->name = $request->file('file')->getClientOriginalName();
                $document->path = $request->file('file')->store('documents', 'public');
            }

            $document->save();

            $this->storeKycNotes($request->note, $request->user_id);

            return Document::where('user_id', $request->user_id)->get();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function storeKycNotes(String $note, Int $userId)
    {
        $query = UserKyc::where('user_id', $userId)->first();
        if ($query) {
            $query->notes = $note;
            $query->save();
        } else {
            $userKyc = new UserKyc();
            $userKyc->user_id = $userId;
            $userKyc->notes = $note;
            $userKyc->save();
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Get KYC Documents
    |--------------------------------------------------------------------------
    */
    public function documentList($user)
    {
        try {
            return Document::where('user_id', $user)->get();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To remove the KYC documents
    |--------------------------------------------------------------------------
    */
    public function deleteDocument(Request $request)
    {
        $request->validate([
            'id' => 'integer',
            'userId' => 'integer'
        ]);

        try {
            $query = Document::find($request->id);
            $query->delete();

            $this->updateLevel($request->userId);

            return $this->documentList($request->userId);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function checkDockLevel($userId, $level)
    {
        return Document::where(['status' => $level, 'user_id' => $userId])->first();
    }

    public function updateKycLevel($userId, $level)
    {
        $query = UserKyc::where('user_id', $userId)->first();
        $query->level = $level;

        if ($level == 2)
            $query->kyc_status_three = 0;

        if ($level == 1)
            $query->kyc_status_two = 0;

        $query->save();
    }

    public function updateLevel($userId)
    {
        if (!$this->checkDockLevel($userId, 3)) {
            $this->updateKycLevel($userId, 2);
        }

        if (!$this->checkDockLevel($userId, 2)) {
            $this->updateKycLevel($userId, 1);
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | To update status approve or deny
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'uid' => 'integer',
            'status' => 'integer',
            'level' => 'integer'
        ]);

        try {
            $query = UserKyc::where('user_id', $request->uid)->first();
            if ($query) {

                $status = $request->status ? $request->status : 0;

                if ($request->level == 2) {
                    $query->kyc_status_two = $status;
                }
                if ($request->level == 3) {
                    $query->kyc_status_three = $status;
                }

                $query->save();
            }
            return 1;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
