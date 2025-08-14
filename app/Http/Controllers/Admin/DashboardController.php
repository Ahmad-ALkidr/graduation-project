<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Post;
use App\Models\Comment;
use App\Models\BookRequest;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with all relevant statistics.
     */
    public function index()
    {
        // User Statistics
        $studentCount = User::where('role', RoleEnum::STUDENT)->count();
        $academicCount = User::where('role', RoleEnum::ACADEMIC)->count();
        $totalUsers = $studentCount + $academicCount;

        // Gender Statistics
        $maleCount = User::where('gender', 'male')->count();
        $femaleCount = User::where('gender', 'female')->count();
        $malePercentage = $totalUsers > 0 ? round(($maleCount / $totalUsers) * 100) : 0;
        $femalePercentage = $totalUsers > 0 ? round(($femaleCount / $totalUsers) * 100) : 0;

        // Content Statistics
        $postCount = Post::count();
        $commentCount = Comment::count();
        $libraryFileCount = BookRequest::where('status', 'approved')->count();

        // Recent Activity
        $recentUsers = User::orderBy('created_at', 'DESC')->take(5)->get();

        // Pass all the data to the view
        return view('Admin.dashboard', compact(
            'studentCount',
            'academicCount',
            'totalUsers',
            'maleCount',
            'femaleCount',
            'malePercentage',
            'femalePercentage',
            'postCount',
            'commentCount',
            'libraryFileCount',
            'recentUsers'
        ));
    }
}
