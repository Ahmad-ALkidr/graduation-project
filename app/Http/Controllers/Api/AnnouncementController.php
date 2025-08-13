<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    //
     public function index()
    {
        $announcements = Announcement::latest()->get();

        return AnnouncementResource::collection($announcements);
    }
}
