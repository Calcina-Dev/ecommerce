<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SaleTicketController extends Controller
{
    public function download(Sale $sale)
    {
        $sale->load(['items.product', 'payments.paymentMethod', 'customer', 'user', 'warehouse']);

        $pdf = Pdf::loadView('sales.ticket', compact('sale'));
        
        // Formato para ticketera térmica de 80mm
        // 80mm = ~226.77 pt (ancho). El alto lo dejamos largo para que el contenido decida.
        $pdf->setPaper(array(0,0,226.77,800), 'portrait');

        return $pdf->stream('ticket-' . ($sale->document_number ?? $sale->id) . '.pdf');
    }
}
