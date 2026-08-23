<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse;
use Symfony\Component\HttpFoundation\Response;

class PendingAccountRegistrationResponse implements RegisterResponse
{
    public function toResponse($request): Response
    {
        Auth::guard(config('fortify.guard', 'web'))->logout();

        if ($request instanceof Request && $request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $message = 'Tài khoản đã được ghi nhận và đang chờ Hội phê duyệt. Anh/chị sẽ nhận được quyền đăng nhập sau khi được duyệt.';

        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 202);
        }

        return redirect()->route('login')->with('status', $message);
    }
}
