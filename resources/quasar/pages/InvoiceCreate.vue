<template>
  <q-page class="page-invoices page-invoice-single page-invoice-create">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/invoices" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Invoice') }) }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.invoice')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-btn flat round dense icon="arrow_back" to="/invoices" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Invoice') }) }}
      </q-toolbar-title>

      <div class="sep" />


      <q-btn
        v-if="formInvoice.invoice_no"
        flat
        padding="sm"
        class="q-btn-lg btn-page-invoices-create-settings"
        icon="settings"
        :disable="isLoading"
        @click="onPreview"
      >
        <q-tooltip>
          {{ $t('Template Settings') }}
        </q-tooltip>
      </q-btn>

      <q-btn
        v-if="formInvoice.invoice_no"
        color="default"
        class="q-btn-lg q-ml-sm btn-page-invoices-create-preview"
        icon="plagiarism"
        :disable="isLoading"
        @click="onPreview"
      >
        <span class="q-ml-xs">{{ $t('Preview') }}</span>
      </q-btn>
      <q-btn
        v-if="formInvoice.invoice_no"
        color="primary"
        class="q-btn-lg q-ml-sm btn-page-invoices-create-save"
        icon="check"
        :disable="isLoading"
        @click="onSave"
      >
        <span class="q-ml-xs">{{ $t('Save') }}</span>
      </q-btn>
    </div>

    <div class="page-body">
      <div class="row q-mb-lg">
        <div class="col-xs-12">
          <div class="page-action q-pa-sm q-gutter-md">

            <div class="page-action-customer">
              <div class="page-action-customer-inner">
                <autocomplete-distribution-center
                  v-show="customerToFind === 'distribution_center'"
                  v-model="entry.distribution_center_id"
                  :readonly="isLoading"
                  filled
                  :label="$t('Select {entity}', { entity: $t('distribution center') })"
                />
                <autocomplete-franchise
                  v-show="customerToFind === 'franchise'"
                  v-model="entry.franchise_id"
                  :readonly="isLoading"
                  filled
                  :label="$t('Select {entity}', { entity: $t('franchise') })"
                />

                <q-option-group
                  v-model="customerToFind"
                  :options="[{ label: $t('Distribution Center'), value: 'distribution_center' }, { label: $t('Franchise'), value: 'franchise' }]"
                  color="primary"
                  inline
                />
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12">

          <div v-if="isLoading" class="empty-distribution-center">
            <q-spinner
              color="primary"
              size="3em"
            />
          </div>
          <div v-else-if="formInvoice.invoice_no">
            <div>
              <form-invoice
                ref="formInvoice"
                :entry="formInvoice"
                :editable="isEditable"
                @success="onSuccess"
                @updated="onFormInvoiceUpdated"
              />
            </div>
            <div>
              <form-receipt
                ref="formReceipt"
                :entry="formReceipt"
                :invoice="formInvoiceUpdated"
                :editable="isEditable"
                @success="onSuccess"
                @updated="onFormReceiptUpdated"
              />
            </div>

          </div>
          <div v-else class="empty-distribution-center">
            <template v-if="customerToFind === 'distribution_center'">
            {{ $t('Please select a distribution center to continue') }}
            </template>
            <template v-else>
              {{ $t('Please select a franchise to continue') }}
            </template>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { date } from 'quasar'
import FormInvoice from './Invoice/FormInvoice'
import FormReceipt from './Invoice/FormReceipt'

export default {
  name: 'PageInvoiceCreate',
  components: {
    FormInvoice,
    FormReceipt
  },
  meta() {
    return {
      title: this.$t('Create New {entity}', { entity: this.$t('Invoice') }) + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Invoices'),  to: '/invoices' },
      { text: this.$t('New {entity}', { entity: this.$t('Invoice') }) },
    ]
  },
  data() {
    return {
      isLoading: false,
      entry: {
        distribution_center_id: null,
        franchise_id: null
      },
      formInvoice: {
        invoice_no: null
      },
      formReceipt: {
        receipt_no: null
      },
      formInvoiceUpdated: {},
      customerToFind: 'distribution_center',
      isEditable: true
    }
  },
  watch: {
    'entry.distribution_center_id'(n, o) {
      if (n !== o) {
        this.rebuildInvoice()
      }
    },
    'entry.franchise_id'(n, o) {
      if (n !== o) {
        this.rebuildInvoice()
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    if (this.$refs.formInvoice && this.$refs.formInvoice.isDirty()) {
      const answer = window.confirm(this.$t('There are unsaved changes. Do you want to discard them?'))

      if (answer) {
        next()
      } else {
        next(false)
      }
    } else {
      next()
    }
  },
  methods: {
    async rebuildInvoice() {
      if (this.isLoading) {
        return
      }

      this.isLoading = true

      const params = this.entry

      try {
        const { data } = await this.$api.get('/v1/invoices/template', { params })

        if (data.status === 'success') {
          this.formInvoice = data.data.invoice
          this.formReceipt = data.data.invoice
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    onSuccess(entry) {
      if (this.$route.query.customer_id) {
        this.$router.replace(`/customers/${this.$route.query.customer_id}`)
      } else {
        this.$router.push(`/invoices/${entry.id}`)
      }
    },
    onPreview() {

    },
    formatDateBeforeSubmit(d) {
      if (d) {
        return date.formatDate(date.extractDate(d, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }
    },
    async onSave() {
      let isValid = true
      let errMsg = await this.$refs.formInvoice.validate()

      if (typeof errMsg === 'string') {
        this.$q.notify({ message: errMsg })
        isValid = false
      }

      let receiptErrMsg = await this.$refs.formReceipt.validate()

      if (isValid && typeof receiptErrMsg === 'string') {
        this.$q.notify({ message: receiptErrMsg })
        isValid = false
      }

      if (!isValid) {
        return
      }


      if (this.isLoading) {
        return;
      }


      const formInvoice = { ...this.formInvoiceUpdated }
      const entry = {
        distribution_center_id: formInvoice.distribution_center_id,
        franchise_id: formInvoice.franchise_id,
        published_at: this.formatDateBeforeSubmit(formInvoice.published_at),
        offering_letter_reference_number: formInvoice.offering_letter_reference_number,
        fo_offering_letter_reference_number: formInvoice.fo_offering_letter_reference_number,
        approval_date: this.formatDateBeforeSubmit(formInvoice.approval_date),
        fo_approval_date: this.formatDateBeforeSubmit(formInvoice.fo_approval_date),
        issuance_number: formInvoice.issuance_number,
        fo_issuance_number: formInvoice.fo_issuance_number,
        sub_total: formInvoice.sub_total,
        ppn_percentage: formInvoice.ppn_percentage,
        ppn_total: formInvoice.ppn_total || formInvoice.ppn,
        stamp_duty: formInvoice.stamp_duty,
        total: formInvoice.total || formInvoice.grand_total,
        due_at: this.formatDateBeforeSubmit(this.formInvoice.due_at),
        note: formInvoice.note,
        signatory_name: formInvoice.signatory_name,
        signatory_position: formInvoice.signatory_position,
        customer_name: formInvoice.customer_name,
        customer_address: formInvoice.customer_address,
        receipt_remark: this.formReceipt.receipt_remark,
        invoice_services: formInvoice.services || []
      }

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/invoices/${entry.id}` : '/v1/invoices';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.$router.replace(`/invoices/${data.data.invoice.id}`)
          this.$emit('success', data.data.invoice);
          this.isEditable = false
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Store') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('store') })
          }
        }
      } catch (err) {
        this.$q.notify(err);
      }

      this.isLoading = false;
    },
    onFormInvoiceUpdated(value) {
      this.formInvoiceUpdated = value
    },
    onFormReceiptUpdated(value) {
      this.formReceipt = value
    }
  }
}
</script>

<style lang="scss">
.page-invoice-create {
  padding-bottom: 3rem;

  .empty-distribution-center {
    font-style: italic;
    text-align: center;
    opacity: 0.5;
    min-height: 30vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .page-action {
    display: flex;
    align-items: center;
    position: relative;

    @media (min-width: $breakpoint-lg-min) {
      margin-top: calc(-50px + -2rem);
    }

    .page-action-customer {
      width: 210mm;
      margin-left: auto;
      margin-right: auto;
    }

    .page-action-customer-inner {
      margin-left: 5mm;
    }

    .btn-page-invoices-create-preview {
      // position: absolute;
      // right: 0;
      // margin: 0;
      // margin-top: -1.5rem;
    }
  }
}
</style>
