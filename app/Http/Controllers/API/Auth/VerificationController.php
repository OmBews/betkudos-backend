<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Models\Users\User;
use App\Notifications\Auth\EmailVerification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use hisorange\BrowserDetect\Parser as Browser;

class VerificationController extends Controller
{
    private Agent $agent;

    public function __construct()
    {
        $this->middleware('auth:api')->only('resend');
        $this->middleware('throttle:6,1')->only('verify', 'resend');

        $this->agent = new Agent();
    }

    protected function redirectPath(ResendVerificationRequest $request, bool $expired = false)
    {
        $path = $request->product === 'mobile' ? config('app.frontend_url') : config('app.frontend_desktop');

        if ($expired) {
            if ($request->product !== 'mobile') {
                return $path .'/account/verification-expired';
            }

            return $path.'/verification-expired';
        }

        if ($request->product === 'mobile') {
            return $path.'/login?emailVerified=1';
        }

        return $path.'/account/login?emailVerified=1';
    }

    public function verify(ResendVerificationRequest $request)
    {
        if (! $request->hasValidSignature()) {
            return redirect($this->redirectPath($request, true));
        }

        $user = User::findOrFail($request->id);

        if (! hash_equals((string) $request->route('id'), (string) $user->getKey())) {
            throw new AuthorizationException();
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException();
        }

        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectPath($request));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect($this->redirectPath($request));
    }

    public function resend(ResendVerificationRequest $request)
    {
        $user = $request->user();

        $user->notify(new EmailVerification($request->product));

        return response()->json([
            'success' => true
        ]);
    }
}
