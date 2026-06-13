<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeruConsultService
{
    /**
     * Consulta un DNI en apis.net.pe v1
     * 
     * @param string $dni
     * @return array|null [ 'name' => string ]
     */
    public function consultDni(string $dni): ?array
    {
        if (strlen($dni) !== 8) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("https://api.apis.net.pe/v1/dni", [
                'numero' => $dni
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['nombre'])) {
                    return [
                        'name' => $data['nombre'],
                        'raw' => $data
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Error consultando DNI {$dni}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Consulta un RUC en apis.net.pe v1
     * 
     * @param string $ruc
     * @return array|null [ 'name' => string ]
     */
    public function consultRuc(string $ruc): ?array
    {
        if (strlen($ruc) !== 11) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("https://api.apis.net.pe/v1/ruc", [
                'numero' => $ruc
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['nombre'])) {
                    return [
                        'name' => $data['nombre'],
                        'address' => $data['direccion'] ?? null,
                        'status' => $data['estado'] ?? null,
                        'condition' => $data['condicion'] ?? null,
                        'raw' => $data
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Error consultando RUC {$ruc}: " . $e->getMessage());
        }

        return null;
    }
}
