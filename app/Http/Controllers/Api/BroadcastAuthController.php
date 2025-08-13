<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\HttpFoundation\Response;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $raw = Broadcast::auth($request);

        if ($raw instanceof Response) {
            $payload = json_decode($raw->getContent(), true);
        } elseif (is_array($raw)) {
            $payload = $raw;
        } else {
            $payload = json_decode((string) $raw, true) ?? [];
        }

        if (!array_key_exists('shared_secret', $payload)) {
            $payload['shared_secret'] = '';
        }

        return response()->json($payload);
    }
}
