<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Post;
use App\Models\Comment;
use App\Models\BookRequest;
use DB;

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
        $postCount = Post::count();
        $commentCount = Comment::count();
        $recentUsers = User::orderBy('created_at', 'DESC')->take(5)->get();
        $topPosts = User::withSum('posts', 'likes_count')->orderBy('posts_sum_likes_count', 'DESC')->take(3)->get();

        $postChartLabels = [];
        $postChartData = [];

        // تحسين: استخدام استعلام واحد بدلاً من حلقة
        $postStats = Post::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays(6), now()])
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // ملء البيانات للأيام السبعة الماضية
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $postChartLabels[] = $date->format('D');
            $postChartData[] = $postStats[$date->format('Y-m-d')] ?? 0;
        }

        // Convert the arrays to JSON to be easily used by JavaScript
        $postChartLabels = json_encode($postChartLabels);
        $postChartData = json_encode($postChartData);
        // Content Statistics
         // 3. For "Most Active Academics"
        $mostActiveAcademics = User::where('role', RoleEnum::ACADEMIC)
                                   ->withCount('posts')
                                   ->orderBy('posts_count', 'DESC')
                                   ->take(3)
                                   ->get();
        // 1. For "Recent Library Submissions"
        $recentSubmissions = BookRequest::with(['user', 'course.subject'])
                                        ->latest()
                                        ->take(5)
                                        ->get();
        $libraryFileCount = BookRequest::where('status', 'approved')->count();

        // Recent Activity
        $topLikers = User::select('users.*', DB::raw('SUM(posts.likes_count) as total_likes_count'))
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->where('posts.likes_count', '>', 0) // Ensure we only get users with likes
            ->groupBy('users.id')
            ->orderBy('total_likes_count', 'DESC')
            ->take(3)
            ->get();

        $totalTopLikes = $topLikers->sum('total_likes_count');
        // 1. User Growth Rate (Last 4 Weeks)
        $userGrowthLabels = [];
        $userGrowthData = [];

// Get the start of the period (4 weeks ago) and the end (now)
$startDate = now()->subWeeks(3)->startOfWeek();
$endDate = now()->endOfWeek();


// ✨ --- This is the corrected query for PostgreSQL --- ✨
$userGrowthStats = User::select(DB::raw("to_char(created_at, 'YYYY-IW') as week, COUNT(*) as count"))
    ->whereBetween('created_at', [$startDate, $endDate])
    ->groupBy('week')
    ->orderBy('week')
    ->pluck('count', 'week');

        for ($i = 3; $i >= 0; $i--) {
            $weekStartDate = now()->subWeeks($i)->startOfWeek();
            $weekKey = $weekStartDate->format('YW');
            $userGrowthLabels[] = 'Week ' . $weekStartDate->format('W');
            $userGrowthData[] = $userGrowthStats[$weekKey] ?? 0;
        }

        $userGrowthLabels = json_encode($userGrowthLabels);
        $userGrowthData = json_encode($userGrowthData);
        // 2. Active Users
        $dailyActiveUsers = User::where('updated_at', '>=', now()->subDay())->count();
        $monthlyActiveUsers = User::where('updated_at', '>=', now()->subMonth())->count();
        // 3. Top Content Creators
        $topCreators = User::withCount('posts')->orderBy('posts_count', 'DESC')->take(3)->get();
        $newUsersCount = User::where('created_at', '>=', now()->subDays(30))->count();

        // 4. Library Contribution Stats
        $libraryStats = BookRequest::select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status');
        $libraryChartLabels = json_encode($libraryStats->keys());
        $libraryChartSeries = json_encode($libraryStats->values());
        // Prepare data for the radial chart
        $firstLikerLikes = $topLikers->first()->total_likes_count ?? 0;
        $chartScale = 100; // Default scale
        if ($firstLikerLikes > 1000) {
            $chartScale = 10000;
        } elseif ($firstLikerLikes > 100) {
            $chartScale = 1000;
        }
        $chartPercentage = $chartScale > 0 ? round(($firstLikerLikes / $chartScale) * 100) : 0;
        // Pass all the data to the view
        return view('Admin.dashboard', compact('studentCount', 'academicCount', 'totalUsers', 'maleCount', 'femaleCount', 'malePercentage', 'femalePercentage', 'postCount', 'commentCount', 'libraryFileCount', 'recentUsers', 'topLikers', 'postChartLabels', 'postChartData', 'totalTopLikes', 'chartPercentage', 'chartScale', 'topPosts', 'userGrowthLabels', 'userGrowthData', 'dailyActiveUsers', 'monthlyActiveUsers', 'topCreators', 'libraryChartLabels', 'libraryChartSeries', 'mostActiveAcademics', 'recentSubmissions', 'newUsersCount'));
    }
}
