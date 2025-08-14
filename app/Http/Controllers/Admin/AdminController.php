<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // عرض صفحة تسجيل الدخول
    public function create()
    {
        return view('Admin.login');
    }

    // معالجة تسجيل الدخول
    // in AdminController.php

public function store(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // Attempt to log the user in
    if (Auth::guard('admin')->attempt($credentials)) {

        // ✨ This is the new, critical security check ✨
        $user = Auth::guard('admin')->user();
        if ($user->role !== \App\Enums\RoleEnum::ADMIN) {
            // If the user is not an admin, log them out immediately and show an error
            Auth::guard('admin')->logout();
            return back()->withErrors(['email' => 'You do not have permission to access the dashboard.']);
        }

        // If they are an admin, proceed
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}

    // تسجيل الخروج
    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
