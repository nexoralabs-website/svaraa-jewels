<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentHealthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationsExportController extends Controller
{
    public function __invoke(Request $request, PaymentHealthService $paymentHealthService): StreamedResponse
    {
        $metrics = $paymentHealthService->dashboardMetrics([
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'payment_status' => $request->query('payment_status'),
            'order_status' => $request->query('order_status'),
        ]);

        return response()->streamDownload(function () use ($metrics) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order', 'Customer', 'Email', 'Total', 'Payment Status', 'Order Status', 'Paid At', 'Created At']);

            foreach ($metrics['filtered_orders'] as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->total,
                    $order->payment_status,
                    $order->order_status?->value ?? $order->order_status,
                    $order->paid_at,
                    $order->created_at,
                ]);
            }

            fclose($handle);
        }, 'operations-orders.csv', ['Content-Type' => 'text/csv']);
    }
}
