<?php

namespace App\Http\Controllers;

use App\Models\MedicalHistory;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReceiptController extends Controller
{
    public function generateReceipt($medicalHistoryId)
    {
        $medicalHistory = MedicalHistory::with([
            'patient',
            'payments' => function ($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ])->findOrFail($medicalHistoryId);

        if ($medicalHistory->payments->isEmpty()) {
            abort(404, 'To\'lovlar topilmadi');
        }

        // Xarajatlar tafsiloti
        $costs = $medicalHistory->calculateTotalCost();
        $totalPaid = $medicalHistory->getTotalPaidAmount();
        $remaining = $costs['total_cost'] - $totalPaid;

        $data = [
            'medicalHistory' => $medicalHistory,
            'patient' => $medicalHistory->patient,
            'payments' => $medicalHistory->payments,
            'costs' => $costs,
            'totalPaid' => $totalPaid,
            'remaining' => $remaining,
            'generatedAt' => now(),
        ];

        $pdf = Pdf::loadView('receipts.payment-receipt', $data);
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'kvitansiya_' . $medicalHistory->patient->first_name . '_' . $medicalHistory->patient->last_name . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($fileName);
    }

    public function viewReceipt($medicalHistoryId)
    {
        $medicalHistory = MedicalHistory::with([
            'patient',
            'payments' => function ($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ])->findOrFail($medicalHistoryId);

        if ($medicalHistory->payments->isEmpty()) {
            abort(404, 'To\'lovlar topilmadi');
        }

        // Xarajatlar tafsiloti
        $costs = $medicalHistory->calculateTotalCost();
        $totalPaid = $medicalHistory->getTotalPaidAmount();
        $remaining = $costs['total_cost'] - $totalPaid;

        return view('receipts.payment-receipt', [
            'medicalHistory' => $medicalHistory,
            'patient' => $medicalHistory->patient,
            'payments' => $medicalHistory->payments,
            'costs' => $costs,
            'totalPaid' => $totalPaid,
            'remaining' => $remaining,
            'generatedAt' => now(),
        ]);
    }

    public function viewPaymentLog($record)
    {
        $payment = Payment::findOrFail($record);
        $labDetails = $payment->labTestPayments
            ->flatMap->labTestPaymentDetails
            ->map(function ($detail) {
                return [
                    'name' => $detail->labTest->name ?? '-',
                    'price' => $detail->price,
                    'sessions' => $detail->sessions,
                    'total' => $detail->price * $detail->sessions,
                ];
            })->values()->all();
        $procedureDetails = $payment->procedurePayments
            ->flatMap->procedurePaymentDetails
            ->map(function ($detail) {
                return [
                    'name' => $detail->procedure->name ?? '-',
                    'price' => $detail->price,
                    'sessions' => $detail->sessions,
                    'total' => $detail->price * $detail->sessions,
                ];
            })->values()->all();
        $accommodationDetails = [
            'main' => [],
            'partner' => [],
        ];

        foreach ($payment->accommodationPayments as $acc) {
            $data = [
                'tariff_price' => $acc->tariff_price,
                'ward_day' => $acc->ward_day,
                'meal_price' => $acc->meal_price,
                'meal_day' => $acc->meal_day,
                'total' => ($acc->tariff_price * $acc->ward_day) + ($acc->meal_price * $acc->meal_day),
            ];

            if (!empty($acc->medical_history_id)) {
                $accommodationDetails['main'][] = $data;
            } else {
                $accommodationDetails['partner'][] = $data;
            }
        }
        
        return view('receipts.view-payment-log-resource', [
            'payment'=>$payment,
            'labDetails' => $labDetails,
            'procedureDetails' => $procedureDetails,
            'accommodationDetails' => $accommodationDetails,
        ]);
    }
}