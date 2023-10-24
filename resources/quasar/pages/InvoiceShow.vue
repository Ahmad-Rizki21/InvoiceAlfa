<template>
  <q-page class="page-invoices page-invoice-single page-invoice-show">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/invoices" />
      <q-toolbar-title>
        <template v-if="isCreate">
          {{ $t('Create New {entity}', { entity: $t('Invoice') }) }}
        </template>
        <template v-else>
          {{ $t('Invoice') }}
        </template>
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
        <template v-if="isCreate">
          {{ $t('Create New {entity}', { entity: $t('Invoice') }) }}
        </template>
        <template v-else>
          {{ $t('Invoice') }}
        </template>
      </q-toolbar-title>

      <div class="sep" />


      <q-btn
        v-if="isCreate && formInvoice.invoice_no"
        flat
        padding="sm"
        class="q-btn-lg btn-page-invoices-single-settings"
        icon="settings"
        :disable="isLoading"
        @click="isFormTemplateSettingsVisible = true"
      >
        <q-tooltip>
          {{ $t('Template Settings') }}
        </q-tooltip>
      </q-btn>

      <q-btn
        v-if="isCreate && formInvoice.invoice_no"
        color="default"
        split
        class="q-btn-lg q-ml-sm btn-page-invoices-single-preview"
        icon="plagiarism"
        :disable="isLoading"
        @click="onPreview"
      >
        {{ $t('Preview') }}
      </q-btn>
      <q-btn-dropdown
        v-else-if="!isCreate && formInvoice.invoice_no"
        color="default"
        split
        class="q-btn-lg q-ml-sm btn-page-invoices-single-print"
        icon="print"
        :disable="isLoading || isSyncing"
        @click="onPrint"
      >
        <template #label>
          <span class="q-ml-xs">{{ $t('Print') }}</span>
        </template>

        <q-list>
          <q-item
            clickable
            v-close-popup
            class="justify-center items-center"
            :disable="isSyncing"
            @click="onPreview"
          >
            {{ $t('Preview') }}
          </q-item>
        </q-list>
      </q-btn-dropdown>
      <template v-if="formInvoice.invoice_no && isCreate">
        <q-btn
          color="primary"
          class="q-btn-lg q-ml-sm btn-page-invoices-single-save"
          icon="check"
          :disable="isLoading || isSyncing"
          @click="onSave"
        >
          <span class="q-ml-xs">{{ $t('Save') }}</span>
          {{ isSyncing ? '...' : '' }}
        </q-btn>
      </template>
      <template v-else-if="formInvoice.invoice_no">
        <q-btn-dropdown
          v-if="isEditable"
          split
          color="primary"
          class="q-btn-lg q-ml-sm btn-page-invoices-single-save"
          icon="check"
          :disable="isLoading || isSyncing"
          @click="onSave"
        >
          <template #label>
            <span class="q-ml-xs">{{ $t('Save') }}</span>
            {{ isSyncing ? '...' : '' }}
          </template>

          <q-list>
            <q-item
              clickable
              v-close-popup
              class="justify-center items-center"
              :disable="isSyncing"
              @click="isEditable = false"
            >
              {{ $t('Cancel Editing') }}
            </q-item>
          </q-list>
        </q-btn-dropdown>
        <q-btn
          v-else
          color="secondary"
          class="q-btn-lg q-ml-sm btn-page-invoices-single-edit"
          icon="edit"
          :disable="isLoading || isSyncing"
          @click="isEditable = true"
        >
          <span class="q-ml-xs">{{ $t('Edit') }}</span>
          {{ isSyncing ? '...' : '' }}
        </q-btn>

      </template>
    </div>

    <div class="page-body">
      <div v-if="isCreate" class="row q-mb-lg">
        <div class="col-xs-12">
          <div class="page-action q-pa-sm q-gutter-md">

            <div class="page-action-customer">
              <div class="page-action-customer-inner">
                <autocomplete-distribution-center
                  v-show="customerToFind === 'distribution_center'"
                  v-model="entry.distribution_center_id"
                  :readonly="isLoading || isSyncing"
                  filled
                  :label="$t('Select {entity}', { entity: $t('distribution center') })"
                />
                <autocomplete-franchise
                  v-show="customerToFind === 'franchise'"
                  v-model="entry.franchise_id"
                  :readonly="isLoading || isSyncing"
                  filled
                  :label="$t('Select {entity}', { entity: $t('franchise') })"
                />

                <q-option-group
                  v-model="customerToFind"
                  :options="[{ label: $t('Distribution Center'), value: 'distribution_center' }, { label: $t('Franchise'), value: 'franchise' }]"
                  color="primary"
                  :disable="isSyncing"
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
                :loading.sync="isLoading"
                :is-create="isCreate"
                :syncing.sync="isSyncing"
                :editable="isEditable"
                :template-settings="formTemplateSettings"
                @success="onSuccess"
                @updated="onFormInvoiceUpdated"
              />
            </div>
            <div>
              <form-receipt
                ref="formReceipt"
                :entry="formReceipt"
                :is-create="isCreate"
                :loading.sync="isLoading"
                :syncing.sync="isSyncing"
                :invoice="formInvoiceUpdated"
                :editable="isEditable"
                :template-settings="formTemplateSettings"
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

    <q-dialog
      v-model="isFormTemplateSettingsVisible"
      persistent
    >
      <form-template-settings
        :entry="formTemplateSettings"
        @success="onFormTemplateSettingsSuccess"
        @cancel="onFormTemplateSettingsCancel"
      />
    </q-dialog>

    <q-dialog
      v-model="isFormPrintPreviewVisible"
      maximized
      full-width
      full-height
      transition-show="slide-down"
      transition-hide="slide-down"
      content-class="dialog-print-preview"
    >
      <form-print-preview
        :invoice="formInvoice"
        :invoice-updated="formInvoiceUpdated"
        :receipt="formReceipt"
        :template-settings="formTemplateSettings"
        @close="isFormPrintPreviewVisible = false"
      />
    </q-dialog>

    <iframe v-if="printUrl" ref="printIframe" :src="printUrl" style="opacity: 0; visibility: hidden; width: 0; height: 0;"></iframe>
  </q-page>
</template>

<script>
import { date } from 'quasar'
import FormInvoice from './Invoice/FormInvoice'
import FormReceipt from './Invoice/FormReceipt'
import FormTemplateSettings from './Invoice/FormTemplateSettings'
import FormPrintPreview from './Invoice/FormPrintPreview'

export default {
  name: 'PageInvoiceShow',
  components: {
    FormInvoice,
    FormReceipt,
    FormTemplateSettings,
    FormPrintPreview
  },
  meta() {
    let title = this.$t('Create New {entity}', { entity: this.$t('Invoice') })

    if (!this.isCreate) {
      title = this.$t('Invoice')
    }

    return {
      title: title + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    const breadcrumbs = [
      'home',
      { text: this.$t('Invoices'),  to: '/invoices' },
    ]

    if (this.isCreate) {
      breadcrumbs.push({ text: this.$t('New {entity}', { entity: this.$t('Invoice') }) })
    } else if (this.formInvoice && this.formInvoice.invoice_no) {
      breadcrumbs.push({ text: this.formInvoice.invoice_no })
    }

    return breadcrumbs
  },
  data() {
    return {
      isSyncing: false,
      isLoading: false,
      isFormTemplateSettingsVisible: false,
      isFormPrintPreviewVisible: false,
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
      formTemplateSettings: {},
      customerToFind: 'distribution_center',
      isEditable: true,
      printUrl: null
    }
  },
  computed: {
    isCreate() {
      return !this.$route.params.id
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
  created() {
    this.requestSettings()
    if (!this.isCreate) {
      this.requestInvoice()
    }
  },
  mounted() {
    if (!this.isCreate) {
      this.isEditable = false
    }
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      if (!vm.isCreate) {
        vm.isEditable = false
      }
    })
  },
  // beforeRouteLeave(to, from, next) {
  //   if (this.$refs.formInvoice && this.$refs.formInvoice.isDirty()) {
  //     const answer = window.confirm(this.$t('There are unsaved changes. Do you want to discard them?'))

  //     if (answer) {
  //       next()
  //     } else {
  //       next(false)
  //     }
  //   } else {
  //     next()
  //   }
  // },
  methods: {
    async requestInvoice() {
      if (this.isLoading) {
        return
      }

      this.isLoading = true

      const params = {
        edit: 1,
        includes: 'invoiceServices'
      }

      try {
        const { data } = await this.$api.get(`/v1/invoices/${this.$route.params.id}`, { params })

        if (data.status === 'success') {
          this.formInvoice = data.data.invoice
          this.formReceipt = data.data.invoice
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    async requestSettings() {
      try {
        const { data } = await this.$api.get(`/v1/settings`)

        if (data.status === 'success') {
          this.formTemplateSettings = data.data.settings
        }
      } catch (err) {
        this.$q.notify(err)
      }
    },
    async rebuildInvoice() {
      if (!this.isCreate || this.isLoading) {
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
      if (this.isCreate) {
        this.$router.push(`/invoices/${entry.id}`)
      }
    },
    onPrint() {
      this.isFormPrintPreviewVisible = false
      this.printUrl = this.$router.resolve(`/invoices/${this.$route.params.id}/print`).href

      this.$nextTick(() => {
        if (this.$refs.printIframe) {
          const $refs = this.$refs.printIframe
          const contentWindow = $refs.contentWindow

          contentWindow.doPrinting = true
          contentWindow.doPrint = () => {
            this.printUrl = null
          }

          // let loaded = false

          // $refs.onload = () => {
          //   if (!loaded) {
          //     loaded = true

          //     setTimeout(() => {
          //       contentWindow.print()

          //       setTimeout(() => {
          //         this.printUrl = null
          //       }, 300)
          //     }, 100)
          //   }
          // }

          // setTimeout(() => {
          //   if (!loaded) {
          //     contentWindow.print()
          //     setTimeout(() => {
          //       this.printUrl = null
          //     }, 300)
          //   }
          // }, 4000)
        }
      })
    },
    onPreview() {
      this.isFormPrintPreviewVisible = true
    },
    formatDateBeforeSubmit(d) {
      if (d) {
        return date.formatDate(date.extractDate(d, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }
    },
    async onSave(options = {}) {
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
        stamp_duty: formInvoice.stamp_duty ? (parseFloat(formInvoice.stamp_duty, 10) || 0) : null,
        total: formInvoice.total || formInvoice.grand_total,
        due_at: this.formatDateBeforeSubmit(formInvoice.due_at),
        note: formInvoice.note,
        signatory_name: formInvoice.signatory_name,
        signatory_position: formInvoice.signatory_position,
        customer_name: formInvoice.customer_name,
        customer_address: formInvoice.customer_address,
        receipt_remark: this.formReceipt.receipt_remark,
        invoice_services: formInvoice.services || []
      }

      const params = {}

      if (options && options.force) {
        params.force = 1
      }

      this.isLoading = true;

      try {
        const endpoint = !this.isCreate ? `/v1/invoices/${this.$route.params.id}` : '/v1/invoices';
        const method = !this.isCreate ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry, { params });

        if (data.status === 'success') {
          if (this.isCreate) {
            this.$router.replace(`/invoices/${data.data.invoice.id}`)
          } else {
            // this.isEditable = false
            this.formInvoiceUpdated = {}
            this.formInvoice = {}
            this.formReceipt = {}
            this.isEditable = false
          }
          this.$emit('success', data.data.invoice);
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Invoice') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('invoice') })
          }
        }
      } catch (err) {
        if (err.response && err.response.data && err.response.data && err.response.data.code== 4) {
          this.$q.dialog({
            title: this.$t('Confirm'),
            message: err.response.data.message + '. ' + this.$t('Are you sure want to continue?'),
            cancel: {
              label: this.$t('Cancel'),
              color: 'dark',
              flat: true
            },
            ok: {
              label: this.$t('Yes'),
              color: 'primary',
              unelevated: true,
              flat: true,
              class: 'text-weight-bold'
            },
            persistent: true
          }).onOk(async () => {
            await this.onSave({ force: true })
            // try {
            //   let { data } = await this.$api.delete(`/v1/invoices/${row.id}`);

            //   if (data.message) {
            //     this.$q.notify({ message: data.message })
            //     this.onRequest()
            //   } else {
            //     if (data.status === 'success') {
            //       this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Invoice') }) })
            //       this.onRequest()
            //     } else {
            //       this.$t('Failed to delete {entity}', { entity: this.$t('invoice') })
            //     }
            //   }
            // } catch (err) {
            //   this.$q.notify(err);
            // }

          }).onCancel(() => {
            this.isLoading = false
          })



          this.isLoading = false
          return
        } else {
          this.$q.notify(err);
        }
      }

      this.isLoading = false;

      if (!this.isCreate) {
        this.requestInvoice()
      }
    },
    onFormInvoiceUpdated(value) {
      this.formInvoiceUpdated = value
    },
    onFormReceiptUpdated(value) {
      this.formReceipt = value
    },
    onFormTemplateSettingsSuccess(entry) {
      this.isFormTemplateSettingsVisible = false
      this.formTemplateSettings = entry
    },
    onFormTemplateSettingsCancel() {
      this.isFormTemplateSettingsVisible = false
    }
  }
}
</script>

<style lang="scss">
.page-invoice-single {
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

    .btn-page-invoices-single-preview {
      // position: absolute;
      // right: 0;
      // margin: 0;
      // margin-top: -1.5rem;
    }
  }
}
</style>
