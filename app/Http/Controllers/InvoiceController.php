<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created invoice for a lead.
     */
    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_address' => 'required|string',
            'invoice_date' => 'required|date',
            'invoice_no' => 'required|string|max:255',
            'order_no' => 'nullable|string|max:255',
            'submission_no' => 'nullable|string|max:255',
            'po_no' => 'nullable|string|max:255',
            'line_items' => 'required|array|min:1',
            'line_items.*.details' => 'required|string',
            'line_items.*.qty' => 'required|numeric|min:0',
            'line_items.*.price' => 'required|numeric',
            'due_date' => 'nullable|date',
        ]);

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['line_items'] as $item) {
            $qty = floatval($item['qty']);
            $price = floatval($item['price']);
            $itemTotal = $qty * $price;
            $subtotal += $itemTotal;
        }

        $vatRate = 0.20; // 20% VAT
        $vatAmount = $subtotal * $vatRate;
        $total = $subtotal + $vatAmount;

        // Create invoice
        $invoice = Invoice::create([
            'lead_id' => $lead->id,
            'buyer_name' => $validated['buyer_name'],
            'buyer_address' => $validated['buyer_address'],
            'invoice_date' => $validated['invoice_date'],
            'invoice_no' => $validated['invoice_no'],
            'order_no' => $validated['order_no'] ?? null,
            'submission_no' => $validated['submission_no'] ?? null,
            'po_no' => $validated['po_no'] ?? null,
            'line_items' => $validated['line_items'],
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => $total,
            'due_date' => $validated['due_date'] ?? $validated['invoice_date'],
            'created_by' => Auth::id(),
        ]);

        Log::info('Invoice Created', [
            'invoice_id' => $invoice->id,
            'lead_id' => $lead->id,
            'invoice_no' => $invoice->invoice_no,
        ]);

        return back()->with('success', 'Invoice created successfully!');
    }

    /**
     * Download invoice as PDF.
     */
    public function download(Invoice $invoice)
    {
        // Load relationships
        $invoice->load('lead', 'creator');

        // Generate PDF using the service container (more reliable than facade)
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('invoices.pdf', compact('invoice'));

        // Set filename
        $filename = 'Invoice_' . $invoice->invoice_no . '_' . $invoice->invoice_date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Invoice $invoice)
    {
        Log::info('Invoice Deleted', [
            'invoice_id' => $invoice->id,
            'lead_id' => $invoice->lead_id,
            'invoice_no' => $invoice->invoice_no,
            'deleted_by' => Auth::id(),
        ]);

        $leadId = $invoice->lead_id;
        $invoice->delete();

        return redirect()->route('leads.show', $leadId)
            ->with('success', 'Invoice deleted successfully!');
    }
}
