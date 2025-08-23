<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CollegeController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     $colleges = cache()->remember('all_colleges', 3600, function () {
    //         return College::with('departments')->get();
    //     });

    //     return response()->json($colleges);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     if (!Gate::allows('is-admin')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $validated = $request->validate([
    //         'name' => 'required|string|unique:colleges,name|max:255',
    //     ]);

    //     $college = College::create($validated);

    //     return response()->json($college, 201); // 201 Created
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(College $college)
    // {
    //     $college->load('departments');
    //     return response()->json($college);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, College $college)
    // {
    //     if (!Gate::allows('is-admin')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $validated = $request->validate([
    //         'name' => 'required|string|unique:colleges,name,' . $college->id . '|max:255',
    //     ]);

    //     $college->update($validated);

    //     return response()->json($college);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(College $college)
    // {
    //     if (!Gate::allows('is-admin')) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $college->delete();

    //     return response()->json(null, 204); // 204 No Content
    // }
    /**
     * Display a listing of all colleges.
     */
    public function index()
    {
        $colleges = College::orderBy('id')->paginate(15);
        return view('Admin.manage_collage.app-colleges-list', compact('colleges'));
    }

    /**
     * Store a newly created college.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:colleges,name']);
        College::create(['name' => $request->name]);
        return redirect()->back()->with('success', 'College added successfully!');
    }

    /**
     * Remove the specified college.
     */
    public function destroy(College $college)
    {
        // You might add logic here to check if any users are assigned to this college before deleting
        $college->delete();
        return redirect()->back()->with('success', 'College deleted successfully!');
    }
}
