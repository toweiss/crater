<?php

namespace Crater\Http\Requests;

use Crater\Models\CompanySetting;
use Crater\Models\Customer;
use Crater\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class InvoicesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.s
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'invoice_date' => [
                'required',
            ],
            'due_date' => [
                'nullable',
            ],
            'customer_id' => [
                'required',
            ],
            'is_credit_note' => [
                'sometimes',
                'boolean',
            ],
            'original_invoice_id' => [
                Rule::requiredIf($this->boolean('is_credit_note')),
                'nullable',
                Rule::exists('invoices', 'id')->where('company_id', $this->header('company')),
            ],
            'invoice_number' => [
                'required',
                Rule::unique('invoices')->where('company_id', $this->header('company'))
            ],
            'exchange_rate' => [
                'nullable'
            ],
            'discount' => [
                'required',
            ],
            'discount_val' => [
                'required',
            ],
            'sub_total' => [
                'required',
            ],
            'total' => [
                'required',
            ],
            'tax' => [
                'required',
            ],
            'template_name' => [
                'required'
            ],
            'items' => [
                'required',
                'array',
            ],
            'items.*' => [
                'required',
                'max:255',
            ],
            'items.*.description' => [
                'nullable',
            ],
            'items.*.name' => [
                'required',
            ],
            'items.*.quantity' => [
                'required',
            ],
            'items.*.price' => [
                'required',
            ],
        ];

        $companyCurrency = CompanySetting::getSetting('currency', $this->header('company'));

        $customer = Customer::find($this->customer_id);

        if ($customer && $companyCurrency) {
            if ((string)$customer->currency_id !== $companyCurrency) {
                $rules['exchange_rate'] = [
                    'required',
                ];
            };
        }

        if ($this->isMethod('PUT')) {
            $rules['invoice_number'] = [
                'required',
                Rule::unique('invoices')
                    ->ignore($this->route('invoice')->id)
                    ->where('company_id', $this->header('company')),
            ];
        }

        return $rules;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $invoice = $this->route('invoice');
            $isCreditNote = $this->boolean('is_credit_note');

            if ($invoice && $invoice->is_credit_note && ! $isCreditNote) {
                $validator->errors()->add('is_credit_note', 'A credit note cannot be changed into an invoice.');

                return;
            }

            if (! $isCreditNote || ! $this->original_invoice_id) {
                if (! $isCreditNote && $this->filled('original_invoice_id')) {
                    $validator->errors()->add('original_invoice_id', 'Only credit notes may reference an invoice.');
                }

                return;
            }

            if ((int) $this->total <= 0) {
                $validator->errors()->add('total', 'The credit note amount must be greater than zero.');

                return;
            }

            $originalInvoice = Invoice::where('company_id', $this->header('company'))
                ->whereKey($this->original_invoice_id)
                ->where('customer_id', $this->customer_id)
                ->where('is_credit_note', false)
                ->first();

            if (! $originalInvoice) {
                $validator->errors()->add('original_invoice_id', 'Select an invoice for the same customer.');

                return;
            }

            $availableAmount = (int) $originalInvoice->due_amount;
            if ($invoice && $invoice->is_credit_note && $invoice->original_invoice_id == $originalInvoice->id) {
                $availableAmount += (int) $invoice->total;
            }

            if ((int) $this->total > $availableAmount) {
                $validator->errors()->add('total', 'The credit note exceeds the available amount of the original invoice.');
            }
        });
    }

    public function getInvoicePayload()
    {
        $company_currency = CompanySetting::getSetting('currency', $this->header('company'));
        $current_currency = $this->currency_id;
        $exchange_rate = $company_currency != $current_currency ? $this->exchange_rate : 1;
        $currency = Customer::find($this->customer_id)->currency_id;

        return collect($this->except('items', 'taxes', 'original_invoice'))
            ->merge([
                'creator_id' => $this->user()->id ?? null,
                'status' => $this->has('invoiceSend') ? Invoice::STATUS_SENT : Invoice::STATUS_DRAFT,
                'paid_status' => Invoice::STATUS_UNPAID,
                'company_id' => $this->header('company'),
                'tax_per_item' => CompanySetting::getSetting('tax_per_item', $this->header('company')) ?? 'NO ',
                'discount_per_item' => CompanySetting::getSetting('discount_per_item', $this->header('company')) ?? 'NO',
                'due_amount' => $this->total,
                'exchange_rate' => $exchange_rate,
                'base_total' => $this->total * $exchange_rate,
                'base_discount_val' => $this->discount_val * $exchange_rate,
                'base_sub_total' => $this->sub_total * $exchange_rate,
                'base_tax' => $this->tax * $exchange_rate,
                'base_due_amount' => $this->total * $exchange_rate,
                'currency_id' => $currency,
                'is_credit_note' => $this->boolean('is_credit_note'),
                'original_invoice_id' => $this->boolean('is_credit_note') ? $this->original_invoice_id : null,
            ])
            ->toArray();
    }
}
