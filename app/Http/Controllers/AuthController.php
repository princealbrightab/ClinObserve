<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt([...$request->safe()->only(['email', 'password']), 'is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The credentials are incorrect or the account is inactive.']);
        }
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function password(): View
    {
        return view('auth.password');
    }

    public function updatePassword(PasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->password = $request->validated('password');
        $user->must_change_password = false;
        $user->remember_token = Str::random(60);
        $user->save();
        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Your password has been changed.');
    }

    public function forgot(): View
    {
        return view('auth.forgot');
    }

    public function emailReset(Request $request): RedirectResponse
    {
        abort_unless(config('clinobserve.password_recovery'), 404);
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        Password::sendResetLink($data);

        return back()->with('success', 'If that account exists, a password reset link has been sent.');
    }

    public function reset(Request $request, string $token): View
    {
        abort_unless(config('clinobserve.password_recovery'), 404);

        return view('auth.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function storeReset(Request $request): RedirectResponse
    {
        abort_unless(config('clinobserve.password_recovery'), 404);
        $data = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->numbers(), 'max:255']]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->password = $password;
            $user->must_change_password = false;
            $user->remember_token = Str::random(60);
            $user->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('success','Password reset. Sign in with your new password.');
    }
}
