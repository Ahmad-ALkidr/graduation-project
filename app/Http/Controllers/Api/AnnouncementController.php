<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    //
     public function index(Request $request)
    {
        $perPage = min($request->input('per_page', 20), 50);

        $announcements = Announcement::latest()->paginate($perPage);

        return AnnouncementResource::collection($announcements);
    }
}
