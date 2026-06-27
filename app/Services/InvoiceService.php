<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate invoice number like INV-20260623-00042
     */
    public function generateInvoiceNumber(Order $order): string
    {
        $date    = now()->format('Ymd');
        $counter = str_pad($order->id, 5, '0', STR_PAD_LEFT);
        return "INV-{$date}-{$counter}";
    }

    /**
     * Assign invoice number to order if not already set.
     */
    public function assignInvoiceNumber(Order $order): string
    {
        if (! $order->invoice_number) {
            $number = $this->generateInvoiceNumber($order);
            $order->update([
                'invoice_number'      => $number,
                'invoice_generated_at' => now(),
            ]);
        }

        return $order->invoice_number;
    }

    /**
     * Generate PDF and return the Dompdf instance.
     */
    public function generatePdf(Order $order): \Barryvdh\DomPDF\PDF
    {
        $this->assignInvoiceNumber($order);
        $order->loadMissing(['items.product', 'user', 'coupon']);

        $gst = $this->calculateGst($order);

        return Pdf::loadView('pdf.invoice', [
            'order'          => $order,
            'gst'            => $gst,
            'appName'        => config('app.name'),
            'invoiceDate'    => ($order->paid_at ?? $order->created_at)->format('d M Y'),
        ])
        ->setPaper('a4', 'portrait');
    }

    /**
     * Download the invoice as a response.
     */
    public function download(Order $order): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        return $this->generatePdf($order)
            ->download("invoice-{$order->invoice_number}.pdf");
    }

    /**
     * Stream the invoice inline in the browser.
     */
    public function stream(Order $order): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        return $this->generatePdf($order)
            ->stream("invoice-{$order->invoice_number}.pdf");
    }

    /**
     * Save PDF to disk and return the relative path.
     */
    public function saveToDisk(Order $order): string
    {
        $pdf  = $this->generatePdf($order);
        $path = "invoices/{$order->invoice_number}.pdf";

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Calculate GST breakdown (18% inclusive split).
     */
    private function calculateGst(Order $order): array
    {
        $taxableAmount = (float) $order->subtotal - (float) ($order->coupon_discount ?? 0);
        $gstRate       = 0.18;
        $gstAmount     = round($taxableAmount * $gstRate / (1 + $gstRate), 2);
        $baseAmount    = round($taxableAmount - $gstAmount, 2);

        return [
            'rate'    => 18,
            'cgst'    => round($gstAmount / 2, 2),
            'sgst'    => round($gstAmount / 2, 2),
            'total'   => $gstAmount,
            'taxable' => $baseAmount,
        ];
    }
}
