<?php

namespace App\Http\Controllers\Admin\manage_users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminAuthController extends Controller
{
    // public function show_list()
    // {
    //     $users = User::orderBy('created_at', 'DESC')->paginate(15);
    //     return view('Admin.app-users-list', compact('users'));
    // }

    public function account($id)
    {
        $studentCount = User::where('role', RoleEnum::STUDENT)->count();
        // Get the count of users where the role is 'academic'
        $academicCount = User::where('role', RoleEnum::ACADEMIC)->count();
        $adminData = User::findOrFail($id);
        return view('Admin.admin-account', compact('adminData','academicCount','studentCount'));
    }

    public function show_create_form()
    {
        return view('Admin.app-users-add');
    }

    // public function create(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'first_name' => ['required', 'string', 'max:100'],
    //         'last_name' => ['required', 'string', 'max:100'],
    //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    //         'phone' => ['nullable', 'string', 'max:15'],
    //         'password' => ['required', 'string', Password::min(8)],
    //         'role' => ['required', Rule::in([RoleEnum::STUDENT->value, RoleEnum::ACADEMIC->value])],
    //         'gender' => ['required', Rule::in(['male', 'female'])],
    //     ]);

    //     User::create([
    //         'first_name' => $validatedData['first_name'],
    //         'last_name' => $validatedData['last_name'],
    //         'email' => $validatedData['email'],
    //         'password' => Hash::make($validatedData['password']),
    //         'phone' => $validatedData['phone'],
    //         'role' => $validatedData['role'],
    //         'gender' => $validatedData['gender'],
    //         'email_verified_at' => now(),
    //     ]);

    //     return redirect()->route('admin.list')->with('success', 'User created successfully!');
    // }

    public function edit(Request $request)
    {
        // نجلب المستخدم بناءً على الـ id المرسل من الفورم
        $user = User::findOrFail($request->input('id'));

        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:15'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'status' => ['required', 'boolean'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        // إذا تم رفع صورة جديدة
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validatedData['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // تحديث بيانات المستخدم
        $user->update($validatedData);

        return redirect()->route('admin.account', $user->id)->with('success', 'User updated successfully!');
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        $user = User::findOrFail($request->user_id);
        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.account', $user->id)->with('success', 'Password changed successfully!');
    }

    public function delete(User $user)
    {
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();
        return redirect()->route('admin.list')->with('success', 'User deleted successfully!');
    }
}
