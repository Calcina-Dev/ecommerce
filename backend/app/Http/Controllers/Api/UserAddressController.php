<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;

class UserAddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($addresses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'alias' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|digits:9',
            'department' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();

        // Límite de 10 direcciones
        if ($user->addresses()->count() >= 10) {
            return response()->json([
                'message' => 'Has alcanzado el límite máximo de 10 direcciones guardadas.'
            ], 400);
        }

        $isDefault = $request->boolean('is_default');
        
        // Si es la primera dirección, hacerla predeterminada automáticamente
        if ($user->addresses()->count() === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'alias' => $request->alias ?: 'Casa',
            'recipient_name' => $request->recipient_name,
            'phone' => $request->phone,
            'department' => $request->department,
            'province' => $request->province,
            'district' => $request->district,
            'address' => $request->address,
            'reference' => $request->reference,
            'postal_code' => $request->postal_code,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'message' => 'Dirección guardada correctamente.',
            'address' => $address,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'alias' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|digits:9',
            'department' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        $isDefault = $request->boolean('is_default');

        if ($isDefault && !$address->is_default) {
            $user->addresses()->update(['is_default' => false]);
        } elseif (!$isDefault && $address->is_default && $user->addresses()->count() > 1) {
            // No se puede quitar el default si es el único o si no se asigna otro; elegimos otro
            $firstOther = $user->addresses()->where('id', '!=', $address->id)->first();
            if ($firstOther) {
                $firstOther->update(['is_default' => true]);
            }
        }

        $address->update([
            'alias' => $request->alias ?: 'Casa',
            'recipient_name' => $request->recipient_name,
            'phone' => $request->phone,
            'department' => $request->department,
            'province' => $request->province,
            'district' => $request->district,
            'address' => $request->address,
            'reference' => $request->reference,
            'postal_code' => $request->postal_code,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'message' => 'Dirección actualizada correctamente.',
            'address' => $address,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault && $user->addresses()->count() > 0) {
            $user->addresses()->latest()->first()->update(['is_default' => true]);
        }

        return response()->json(['message' => 'Dirección eliminada correctamente.']);
    }

    public function setDefault(Request $request, $id)
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'message' => 'Dirección predeterminada actualizada.',
            'address' => $address,
        ]);
    }
}
