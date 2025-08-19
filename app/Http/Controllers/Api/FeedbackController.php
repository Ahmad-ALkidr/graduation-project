<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * Store a new suggestion or complaint.
     */
    public function store(Request $request)
{
    // 1. ✨ Validation updated to include all four types
    $validated = $request->validate([
        'type' => ['required', 'string', Rule::in(['suggestion', 'complaint', 'bug', 'question'])],
        'content' => ['required', 'string', 'min:5', 'max:5000'],
    ]);

    // 2. Create the feedback record (this part is already correct)
    $request->user()->feedback()->create([
        'type' => $validated['type'],
        'content' => $validated['content'],
    ]);

    // 3. ✨ Dynamic success message using a 'match' expression
    $responseMessage = match ($validated['type']) {
        'suggestion' => 'Your suggestion has been submitted successfully. Thank you!',
        'complaint'  => 'Your complaint has been registered. We will look into it shortly.',
        'bug'        => 'Thank you for reporting the bug. Our team will investigate.',
        'question'   => 'Your question has been received. We will get back to you soon.',
    };

    // 4. Return the new success response
    return response()->json(['message' => $responseMessage], 201);
}
}
