<template>
  <SelectTemplateModal />
  <ItemModal />
  <TaxTypeModal />
  <SalesTax
    v-if="salesTaxEnabled && (!isLoadingContent || route.query.customer)"
    :store="invoiceStore"
    :is-edit="isEdit"
    store-prop="newInvoice"
    :customer="invoiceStore.newInvoice.customer"
  />

  <BasePage class="relative invoice-create-page">
    <form @submit.prevent="submitForm">
      <BasePageHeader :title="pageTitle">
        <BaseBreadcrumb>
          <BaseBreadcrumbItem
            :title="$t('general.home')"
            to="/admin/dashboard"
          />
          <BaseBreadcrumbItem
            :title="$tc('invoices.invoice', 2)"
            to="/admin/invoices"
          />
          <BaseBreadcrumbItem
            v-if="$route.name === 'invoices.edit'"
            :title="isCreditNote ? $t('invoices.edit_credit_note') : $t('invoices.edit_invoice')"
            to="#"
            active
          />
          <BaseBreadcrumbItem
            v-else
            :title="isCreditNote ? $t('invoices.new_credit_note') : $t('invoices.new_invoice')"
            to="#"
            active
          />
        </BaseBreadcrumb>

        <template #actions>
          <router-link
            v-if="$route.name === 'invoices.edit'"
            :to="`/invoices/pdf/${invoiceStore.newInvoice.unique_hash}`"
            target="_blank"
          >
            <BaseButton class="mr-3" variant="primary-outline" type="button">
              <span class="flex">
                {{ $t('general.view_pdf') }}
              </span>
            </BaseButton>
          </router-link>

          <BaseButton
            :loading="isSaving"
            :disabled="isSaving"
            variant="primary"
            type="submit"
          >
            <template #left="slotProps">
              <BaseIcon
                v-if="!isSaving"
                name="SaveIcon"
                :class="slotProps.class"
              />
            </template>
            {{ isCreditNote ? $t('invoices.save_credit_note') : $t('invoices.save_invoice') }}
          </BaseButton>
        </template>
      </BasePageHeader>

      <!-- Select Customer & Basic Fields  -->
      <InvoiceBasicFields
        :v="v$"
        :is-loading="isLoadingContent"
        :is-edit="isEdit"
        :source-invoices="sourceInvoices"
      />

      <BaseScrollPane>
        <!-- Invoice Items -->
        <InvoiceItems
          :currency="invoiceStore.newInvoice.selectedCurrency"
          :is-loading="isLoadingContent"
          :item-validation-scope="invoiceValidationScope"
          :store="invoiceStore"
          store-prop="newInvoice"
        />

        <!-- Invoice Footer Section -->
        <div
          class="
            block
            mt-10
            invoice-foot
            lg:flex lg:justify-between lg:items-start
          "
        >
          <div class="relative w-full lg:w-1/2 lg:mr-4">
            <!-- Invoice Custom Notes -->
            <NoteFields
              :store="invoiceStore"
              store-prop="newInvoice"
              :fields="invoiceNoteFieldList"
              type="Invoice"
            />

            <!-- Invoice Custom Fields -->
            <InvoiceCustomFields
              type="Invoice"
              :is-edit="isEdit"
              :is-loading="isLoadingContent"
              :store="invoiceStore"
              store-prop="newInvoice"
              :custom-field-scope="invoiceValidationScope"
              class="mb-6"
            />

            <!-- Invoice Template Button-->
            <SelectTemplate
              :store="invoiceStore"
              store-prop="newInvoice"
              component-name="InvoiceTemplate"
              :is-mark-as-default="isMarkAsDefault"
            />
          </div>

          <InvoiceTotal
            :currency="invoiceStore.newInvoice.selectedCurrency"
            :is-loading="isLoadingContent"
            :store="invoiceStore"
            store-prop="newInvoice"
            tax-popup-type="invoice"
          />
        </div>
      </BaseScrollPane>
    </form>
  </BasePage>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  required,
  maxLength,
  helpers,
  requiredIf,
  decimal,
} from '@vuelidate/validators'
import useVuelidate from '@vuelidate/core'

import { useInvoiceStore } from '@/scripts/admin/stores/invoice'
import { useModuleStore } from '@/scripts/admin/stores/module'
import { useCompanyStore } from '@/scripts/admin/stores/company'
import { useCustomFieldStore } from '@/scripts/admin/stores/custom-field'

import InvoiceItems from '@/scripts/admin/components/estimate-invoice-common/CreateItems.vue'
import InvoiceTotal from '@/scripts/admin/components/estimate-invoice-common/CreateTotal.vue'
import SelectTemplate from '@/scripts/admin/components/estimate-invoice-common/SelectTemplateButton.vue'
import InvoiceBasicFields from './InvoiceCreateBasicFields.vue'
import InvoiceCustomFields from '@/scripts/admin/components/custom-fields/CreateCustomFields.vue'
import NoteFields from '@/scripts/admin/components/estimate-invoice-common/CreateNotesField.vue'
import SelectTemplateModal from '@/scripts/admin/components/modal-components/SelectTemplateModal.vue'
import TaxTypeModal from '@/scripts/admin/components/modal-components/TaxTypeModal.vue'
import ItemModal from '@/scripts/admin/components/modal-components/ItemModal.vue'
import SalesTax from '@/scripts/admin/components/estimate-invoice-common/SalesTax.vue'

const invoiceStore = useInvoiceStore()
const companyStore = useCompanyStore()
const customFieldStore = useCustomFieldStore()
const moduleStore = useModuleStore()
const { t } = useI18n()
let route = useRoute()
let router = useRouter()

const invoiceValidationScope = 'newInvoice'
let isSaving = ref(false)
const isMarkAsDefault = ref(false)
const sourceInvoices = ref([])

const invoiceNoteFieldList = ref([
  'customer',
  'company',
  'customerCustom',
  'invoice',
  'invoiceCustom',
])

let isLoadingContent = computed(
  () => invoiceStore.isFetchingInvoice || invoiceStore.isFetchingInitialSettings
)

let pageTitle = computed(() =>
  isCreditNote.value
    ? isEdit.value
      ? t('invoices.edit_credit_note')
      : t('invoices.new_credit_note')
    : isEdit.value
      ? t('invoices.edit_invoice')
      : t('invoices.new_invoice')
)

const salesTaxEnabled = computed(() => {
  return (
    companyStore.selectedCompanySettings.sales_tax_us_enabled === 'YES' &&
    moduleStore.salesTaxUSEnabled
  )
})

let isEdit = computed(() => route.name === 'invoices.edit')
let isCreditNote = computed(() => invoiceStore.newInvoice.is_credit_note)

const rules = {
  invoice_date: {
    required: helpers.withMessage(t('validation.required'), required),
  },
  reference_number: {
    maxLength: helpers.withMessage(
      t('validation.price_maxlength'),
      maxLength(255)
    ),
  },
  customer_id: {
    required: helpers.withMessage(t('validation.required'), required),
  },
  original_invoice_id: {
    required: helpers.withMessage(
      t('validation.required'),
      requiredIf(() => isCreditNote.value)
    ),
  },
  invoice_number: {
    required: helpers.withMessage(t('validation.required'), required),
  },
  exchange_rate: {
    required: requiredIf(function () {
      helpers.withMessage(t('validation.required'), required)
      return invoiceStore.showExchangeRate
    }),
    decimal: helpers.withMessage(t('validation.valid_exchange_rate'), decimal),
  },
}

const v$ = useVuelidate(
  rules,
  computed(() => invoiceStore.newInvoice),
  { $scope: invoiceValidationScope }
)

customFieldStore.resetCustomFields()
v$.value.$reset
invoiceStore.resetCurrentInvoice()
invoiceStore.newInvoice.is_credit_note = route.query.credit_note === '1'
invoiceStore.fetchInvoiceInitialSettings(isEdit.value)

watch(
  () => invoiceStore.newInvoice.customer,
  (newVal) => {
    if (newVal && newVal.currency) {
      invoiceStore.newInvoice.selectedCurrency = newVal.currency
    } else {
      invoiceStore.newInvoice.selectedCurrency =
        companyStore.selectedCompanyCurrency
    }
  }
)

watch(
  () => invoiceStore.newInvoice.customer_id,
  async (customerId, previousCustomerId) => {
    if (!invoiceStore.newInvoice.is_credit_note) {
      return
    }

    if (previousCustomerId && customerId !== previousCustomerId) {
      invoiceStore.newInvoice.original_invoice_id = null
    }

    if (!customerId) {
      sourceInvoices.value = []
      return
    }

    try {
      const response = await invoiceStore.fetchInvoices({
        customer_id: customerId,
        is_credit_note: '0',
        limit: 'all',
      })
      sourceInvoices.value = response.data.data.filter(
        (invoice) =>
          Number(invoice.due_amount) > 0 ||
          invoice.id == invoiceStore.newInvoice.original_invoice_id
      )
    } catch (error) {
      console.error(error)
    }
  },
  { immediate: true }
)

async function submitForm() {
  v$.value.$touch()

  if (v$.value.$invalid) {
    return false
  }

  isSaving.value = true

  let data = {
    ...invoiceStore.newInvoice,
    sub_total: invoiceStore.getSubTotal,
    total: invoiceStore.getTotal,
    tax: invoiceStore.getTotalTax,
  }

  try {
    const action = isEdit.value
      ? invoiceStore.updateInvoice
      : invoiceStore.addInvoice

    const response = await action(data)

    router.push(`/admin/invoices/${response.data.data.id}/view`)
  } catch (err) {
    console.error(err)
  }

  isSaving.value = false
}
</script>
