<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function verifyKhalti(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'amount' => 'required|integer', // in paisa
        ]);

        $secretKey = config('payments.khalti.secret_key');

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $secretKey,
        ])->post(config('payments.khalti.verify_url'), [
            'token' => $validated['token'],
            'amount' => $validated['amount'],
        ]);

        if ($response->successful()) {
            $payload = $response->json();
            // TODO: persist order/payment record, mark as paid
            // $payload['idx'] contains transaction id; $payload['amount'] is in paisa
            return response()->json(['ok' => true, 'data' => $payload]);
        }

        return response()->json(['ok' => false, 'error' => $response->json()], 422);
    }
}