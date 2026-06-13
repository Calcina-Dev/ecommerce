<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Buscar o crear usuario
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => explode('@', $email)[0], 'password' => bcrypt(str()->random(16))]
        );

        // Generar OTP de 6 dígitos
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar en Redis por 5 minutos (300 seg)
        Redis::setex("otp:{$email}", 300, $otp);

        // Enviar notificación por email
        $user->notify(new OtpNotification($otp));
        
        // Log extra para desarrollo local
        Log::info("OTP generado para {$email}: {$otp}");

        return response()->json(['message' => 'Código enviado correctamente.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $code = $request->code;

        $storedOtp = Redis::get("otp:{$email}");

        if (!$storedOtp || $storedOtp !== $code) {
            // Master code bypass solo para entorno local si hay fallas con Redis o logs
            if (app()->environment('local') && $code === '123456') {
                // OK
            } else {
                return response()->json(['message' => 'Código inválido o expirado.'], 401);
            }
        }

        $user = User::where('email', $email)->firstOrFail();

        // Limpiar código
        Redis::del("otp:{$email}");

        // Emitir token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Autenticado correctamente.',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'customer' // Asignar rol por defecto
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Autenticado correctamente',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'credential' => 'required|string',
        ]);

        // Verificar token con Google
        $response = \Illuminate\Support\Facades\Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->credential
        ]);

        if (!$response->successful()) {
            return response()->json(['message' => 'Token de Google inválido.'], 401);
        }

        $googleUser = $response->json();

        // Validar que el token sea para nuestro cliente (Opcional pero recomendado si tienes client_id)
        // if ($googleUser['aud'] !== env('GOOGLE_CLIENT_ID')) { ... }

        $user = User::where('email', $googleUser['email'])->first();

        if ($user) {
            // Actualizar si ya existía pero no tenía google_id
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser['sub'],
                    'avatar' => $googleUser['picture'] ?? null,
                ]);
            }
        } else {
            // Crear nuevo usuario
            $user = User::create([
                'name' => $googleUser['name'],
                'email' => $googleUser['email'],
                'google_id' => $googleUser['sub'],
                'avatar' => $googleUser['picture'] ?? null,
                'role' => 'customer',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Autenticado con Google correctamente',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión terminada.']);
    }
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        
        $user->update([
            'name' => $request->name,
            'dni' => $request->dni,
            'phone' => $request->phone,
        ]);

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user' => $user
        ]);
    }
}
