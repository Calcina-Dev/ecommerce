<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Mail\LeadNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'product_id' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
        ]);

        $lead = Lead::create($validated);

        try {
            Mail::to('hola@comprasaludable.com')->send(new LeadNotificationMail($lead));
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Log::error('Error sending lead email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Lead successfully created',
            'data' => $lead
        ], 201);
    }
}
