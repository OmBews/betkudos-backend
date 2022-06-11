<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $user = $this->getUser($request);

        if ($user && $user->is2FAEnabled()) {
            return $this->sendUse2FAResponse($user);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    private function sendUse2FAResponse(User $requester)
    {
        $username = $requester->username;
        $email = $requester->email;

        $data = [
            'message' => trans('passwords.reset_2fa', [ 'username' => $username ]),
            'requester' => [
                'email' => $email,
                'username' => $username
            ]
        ];

        return response()->json($data, 403);
    }

    private function getUser(Request $request)
    {
        return User::where('email', $request->email)->first();
    }

    /**
     * @param Request $request
     * @param $response
     * @return JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json([ 'message' => trans($response) ]);
    }

    /**
     * @param Request $request
     * @param $response
     * @return JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json([ 'message' => trans($response) ], 400);
    }
}
