<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\CancelCustomerPaymentRequest;
use App\Http\Requests\Sales\StoreCustomerPaymentRequest;
use App\Http\Requests\Sales\UpdateCustomerPaymentRequest;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Services\DocumentNumberService;
use App\Services\PaymentPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CustomerPaymentController extends Controller
{
    public function __construct(
        private DocumentNumberService $documentNumberService,
        private PaymentPostingService $postingService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('payments.view');

        $payments = CustomerPayment::query()
            ->with(['customer', 'creator'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('payment_method'), fn ($query) => $query->where('payment_method', $request->string('payment_method')))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('sales.payments.index', compact('payments', 'customers'));
    }

    public function create(): View
    {
        $this->authorize('payments.create');

        return view('sales.payments.create', $this->formData());
    }

    public function store(StoreCustomerPaymentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $receiptNumber = $this->documentNumberService->next(
            'payments.receipt_prefix',
            'payments.receipt_next',
        );

        $payment = CustomerPayment::create([
            'receipt_number' => $receiptNumber,
            'customer_id' => $validated['customer_id'],
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('sales.payments.show', $payment)
            ->with('success', 'Payment draft created successfully.');
    }

    public function show(CustomerPayment $payment): View
    {
        $this->authorize('payments.view');

        $payment->load(['customer', 'allocations.sale', 'creator', 'poster', 'canceller']);

        return view('sales.payments.show', compact('payment'));
    }

    public function edit(CustomerPayment $payment): View|RedirectResponse
    {
        $this->authorize('payments.create');

        if ($payment->status !== 'draft') {
            return redirect()
                ->route('sales.payments.show', $payment)
                ->with('error', 'Only draft payments can be edited.');
        }

        return view('sales.payments.edit', array_merge(
            $this->formData(),
            ['payment' => $payment],
        ));
    }

    public function update(UpdateCustomerPaymentRequest $request, CustomerPayment $payment): RedirectResponse
    {
        if ($payment->status !== 'draft') {
            return redirect()
                ->route('sales.payments.show', $payment)
                ->with('error', 'Only draft payments can be edited.');
        }

        $validated = $request->validated();

        $payment->update([
            'customer_id' => $validated['customer_id'],
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_no' => $validated['reference_no'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('sales.payments.show', $payment)
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('payments.create');

        if ($payment->status !== 'draft') {
            return back()->with('error', 'Only draft payments can be deleted.');
        }

        $payment->delete();

        return redirect()
            ->route('sales.payments.index')
            ->with('success', 'Draft payment deleted successfully.');
    }

    public function post(CustomerPayment $payment): RedirectResponse
    {
        $this->authorize('payments.post');

        try {
            $this->postingService->post($payment, auth()->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('sales.payments.show', $payment)
            ->with('success', 'Payment posted successfully.');
    }

    public function cancel(CancelCustomerPaymentRequest $request, CustomerPayment $payment): RedirectResponse
    {
        try {
            $this->postingService->cancel(
                $payment,
                $request->user(),
                $request->validated('cancellation_reason'),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('sales.payments.show', $payment)
            ->with('success', 'Payment cancelled successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
        ];
    }
}
