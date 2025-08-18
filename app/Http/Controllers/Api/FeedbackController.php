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
        // 1. Validate the incoming data
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['suggestion', 'complaint'])],
            'content' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        // 2. Create the feedback record, linking it to the authenticated user
        $request->user()->feedback()->create([
            'type' => $validated['type'],
            'content' => $validated['content'],
        ]);

        // 3. Create a dynamic success message based on the type
        $responseMessage = $validated['type'] === 'suggestion'
            ? 'Your suggestion has been submitted successfully. Thank you!'
            : 'Your complaint has been registered. We will look into it shortly.';

        // 4. Return the success response
        return response()->json(['message' => $responseMessage], 201);
    }
}
