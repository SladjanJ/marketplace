<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('ads.index');
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()
            ->route('ads.index')
            ->with('success', 'Email verified successfully. Welcome!');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('ads.index');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Could not send verification email. Please try again later.');
        }

        return back()->with('success', 'Verification link sent. Please check your email.');
    }
}
