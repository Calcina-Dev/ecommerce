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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión terminada.']);
    }
}
