<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Contracts\Service\Attribute\Required;

class SelfxController extends Controller
{
    public function __construct()
    {
    }

    public function updateSelfx(Request $request, $id)
    {
        $request->validate([
            'selfx' => 'boolean|required'
        ]);

        try {
            $user = User::find($id);

            if($request->selfx) {
                $user->self_x = 1;
            }else{
                $user->self_x = 0;
            }

            $user->save();

            return 'Self-X Updated Successfully';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
