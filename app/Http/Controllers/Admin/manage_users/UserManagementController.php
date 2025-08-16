<?php

namespace App\Http\Controllers\Admin\manage_users;

use App\Http\Controllers\Controller;
use App\Models\User; // Using your project's User model
use App\Enums\RoleEnum;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Storage;

class UserManagementController extends Controller
{
    public function show_students()
    {
        $usersData = User::where('role', RoleEnum::STUDENT)->orderBy('created_at', 'DESC')->paginate(15);

        return view('Admin.manage_users.app-users-list', compact('usersData')); // We can reuse the same view
    }

    // ✨ --- NEW: Function to show ONLY academics --- ✨
    public function show_academics()
    {
        $usersData = User::where('role', RoleEnum::ACADEMIC)->with('subjects')->orderBy('created_at', 'DESC')->paginate(15);
        return view('Admin.manage_users.app-academics-list', compact('usersData')); // We can reuse the same view
    }

    /**
     * Show the form for creating a new user.
     */
    public function show_create_form()
    {
        return view('Admin.manage_users.app-users-add');
    }

    /**
     * Store a newly created user.
     */
    // in UserManagementController.php

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', Rule::in([RoleEnum::STUDENT->value, RoleEnum::ACADEMIC->value])],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'birth_date' => ['required', 'date'],

            // ✨ These fields are only required if the selected role is 'student'
            'college' => ['required_if:role,' . RoleEnum::STUDENT->value, 'nullable', 'string'],
            'major' => ['required_if:role,' . RoleEnum::STUDENT->value, 'nullable', 'string'],
            'year' => ['required_if:role,' . RoleEnum::STUDENT->value, 'nullable', 'integer'],
        ]);

        User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'gender' => $validatedData['gender'],
            'birth_date' => $validatedData['birth_date'],
            'college' => $validatedData['college'],
            'major' => $validatedData['major'],
            'year' => $validatedData['year'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.list.academics')->with('success', 'User created successfully!');
    }

    /**
     * Delete the specified user.
     */
    public function delete(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully!');
    }
}
