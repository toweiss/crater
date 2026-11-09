<template>
  <div class="grid grid-cols-12 gap-8 mt-6 mb-8">
    <BaseCustomerSelectPopup
      v-model="invoiceStore.newInvoice.customer"
      :valid="v.customer_id"
      :content-loading="isLoading"
      type="invoice"
      class="col-span-12 lg:col-span-5 pr-0"
    />

    <BaseInputGrid class="col-span-12 lg:col-span-7">
      <BaseInputGroup
        :label="invoiceStore.newInvoice.is_credit_note ? $t('invoices.credit_note_date') : $t('invoices.invoice_date')"
        :content-loading="isLoading"
        required
        :error="v.invoice_date.$error && v.invoice_date.$errors[0].$message"
      >
        <BaseDatePicker
          v-model="invoiceStore.newInvoice.invoice_date"
          :content-loading="isLoading"
          :calendar-button="true"
          calendar-button-icon="calendar"
        />
      </BaseInputGroup>

      <BaseInputGroup
        v-if="invoiceStore.newInvoice.is_credit_note"
        :label="$t('invoices.original_invoice')"
        :content-loading="isLoading"
        required
      >
        <BaseMultiselect
          v-model="invoiceStore.newInvoice.original_invoice_id"
          value-prop="id"
          track-by="invoice_number"
          label="invoice_number"
          :options="sourceInvoices"
          :placeholder="$t('invoices.select_original_invoice')"
          :can-deselect="false"
        >
          <template #option="{ option }">
            {{ option.invoice_number }} -
            <BaseFormatMoney
              :amount="
                Number(option.due_amount) +
                (option.id === invoiceStore.newInvoice.original_invoice_id
                  ? Number(invoiceStore.newInvoice.total)
                  : 0)
              "
              :currency="option.customer.currency"
            />
          </template>
        </BaseMultiselect>
      </BaseInputGroup>

      <BaseInputGroup
        v-if="!invoiceStore.newInvoice.is_credit_note"
        :label="$t('invoices.due_date')"
        :content-loading="isLoading"
      >
        <BaseDatePicker
          v-model="invoiceStore.newInvoice.due_date"
          :content-loading="isLoading"
          :calendar-button="true"
          calendar-button-icon="calendar"
        />
      </BaseInputGroup>

      <BaseInputGroup
        :label="invoiceStore.newInvoice.is_credit_note ? $t('invoices.credit_note_number') : $t('invoices.invoice_number')"
        :content-loading="isLoading"
        :error="v.invoice_number.$error && v.invoice_number.$errors[0].$message"
        required
      >
        <BaseInput
          v-model="invoiceStore.newInvoice.invoice_number"
          :content-loading="isLoading"
          @input="v.invoice_number.$touch()"
        />
      </BaseInputGroup>

      <ExchangeRateConverter
        :store="invoiceStore"
        store-prop="newInvoice"
        :v="v"
        :is-loading="isLoading"
        :is-edit="isEdit"
        :customer-currency="invoiceStore.newInvoice.currency_id"
      />
    </BaseInputGrid>
  </div>
</template>

<script setup>
import ExchangeRateConverter from '@/scripts/admin/components/estimate-invoice-common/ExchangeRateConverter.vue'
import { useInvoiceStore } from '@/scripts/admin/stores/invoice'

const props = defineProps({
  v: {
    type: Object,
    default: null,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
  sourceInvoices: {
    type: Array,
    default: () => [],
  },
})

const invoiceStore = useInvoiceStore()
</script>
