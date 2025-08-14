<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Using your project's User model
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Display a list of all users.
     */
    // public function show_list()
    // {
    //     $usersData = User::orderBy('created_at', 'DESC')->paginate(15);
    //     return view('Admin.app-users-list', compact('usersData'));
    // }
    public function show_students()
    {
        $usersData = User::where('role', RoleEnum::STUDENT)
                     ->orderBy('created_at', 'DESC')
                     ->paginate(15);

        return view('Admin.app-users-list', compact('usersData')); // We can reuse the same view
    }

    // ✨ --- NEW: Function to show ONLY academics --- ✨
    public function show_academics()
    {
        $usersData = User::where('role', RoleEnum::ACADEMIC)
                 ->with('subjects')
                 ->orderBy('created_at', 'DESC')
                 ->paginate(15);
        return view('admin.app-academics-list', compact('usersData')); // We can reuse the same view
    }

    /**
     * Show the form for creating a new user.
     */
    public function show_create_form()
    {
        return view('Admin.app-users-add');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:15'],
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', Rule::in([RoleEnum::STUDENT->value, RoleEnum::ACADEMIC->value])],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'birth_date' => ['required', 'date'],
            'college' => ['required', 'string'],
            'major' => ['required', 'string'],
            'year' => ['required', 'integer'],
        ]);

        // REMOVED: Custom ID generation. Let the database handle this.
        User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), // FIXED: Password is now correctly hashed.
            'phone' => $validatedData['phone'],
            'role' => $validatedData['role'],
            'gender' => $validatedData['gender'],
            'birth_date' => $validatedData['birth_date'],
            'college' => $validatedData['college'],
            'major' => $validatedData['major'],
            'year' => $validatedData['year'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.list')->with('success', 'User created successfully!');
    }

    /**
     * Show a specific user's account page.
     */
    public function account(User $user) // CHANGED: Using Route Model Binding
    {
        
        return view('admin.account', ['userData' => $user]);
    }

    /**
     * Update the specified user's details.
     */
    public function update(Request $request, User $user) // CHANGED: Using Route Model Binding
    {
        $validatedData = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            // IMPROVED: Validation now ignores the user's own email
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        // MOVED: Password is not updated here for better security and user experience.
        $user->update($validatedData);

        return redirect()->route('admin.account', $user->id)->with('success', 'User updated successfully!');
    }

    /**
     * Delete the specified user.
     */
    public function delete(User $user) // CHANGED: Using Route Model Binding
    {
        // Add logic to delete profile picture if you have it
        $user->delete();

        return redirect()->route('admin.list')->with('success', 'User deleted successfully!');
    }
}
