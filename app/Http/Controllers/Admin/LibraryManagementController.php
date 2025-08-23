<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use Illuminate\Http\Request;
use Storage;

class LibraryManagementController extends Controller
{
    //
    public function showPendingFiles()
    {
        // Fetch all files that have a 'pending' status
        $pendingFiles = BookRequest::where('status', 'pending')
            ->with('user', 'course.subject') // Eager load relationships
            ->orderBy('id')
            ->paginate(20);

        return view('Admin.manage_library.app-library_padding-list', compact('pendingFiles'));
    }

public function showApprovedFiles()
{
    // Fetch all files that have an 'approved' status
    $approvedFiles = BookRequest::where('status', 'approved')
                               ->with('user', 'course.subject') // Eager load relationships
                               ->orderBy('id')
                               ->paginate(20);

    // We'll create a new view for this
    return view('Admin.manage_library.app-library-approved-list', compact('approvedFiles'));
}
    // ✨ --- دالة جديدة للموافقة على الملف --- ✨
    public function approve(BookRequest $file)
    {
        $file->update(['status' => 'approved']);

        // يمكنك هنا إرسال إشعار للطالب بأن ملفه تمت الموافقة عليه
        // $file->user->notify(new YourFileWasApprovedNotification($file));

        return redirect()->back()->with('success', 'File approved successfully!');
    }

    // ✨ --- دالة جديدة لحذف الملف --- ✨
    public function destroy(BookRequest $file)
    {
        // حذف الملف من مجلد التخزين أولاً
        if ($file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }

        // ثم حذف السجل من قاعدة البيانات
        $file->delete();

        return redirect()->back()->with('success', 'File deleted successfully!');
    }
    /**
     * ✨ --- NEW: Function to VIEW a file in the browser --- ✨
     */
public function viewFile(BookRequest $file)
{
    if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {

        // ✨ --- This is the new logic --- ✨

        // Get the full, public URL to the file in your storage
        $fileUrl = Storage::disk('public')->url($file->file_path);

        // Return a new view and pass the file URL and title to it
        return view('Admin.manage_library.file-viewer', [
            'fileUrl' => $fileUrl,
            'fileTitle' => $file->title
        ]);
    }

    return redirect()->back()->with('error', 'File not found.');
}

    /**
     * ✨ --- MODIFIED: Function to FORCE a download with the correct name --- ✨
     */
    public function downloadFile(BookRequest $file)
    {
        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            // We now pass the file's title as the second argument
            // 1. Get the file's extension (e.g., 'pdf') from the stored path
            $extension = pathinfo($file->file_path, PATHINFO_EXTENSION);
                    // 2. Create the full, desired filename by combining the title and the extension
            $filename = $file->title . '.' . $extension;
            // This sets the name of the file that the user downloads.
            return Storage::disk('public')->download($file->file_path, $filename);
        }
        return redirect()->back()->with('error', 'File not found.');
    }
}
