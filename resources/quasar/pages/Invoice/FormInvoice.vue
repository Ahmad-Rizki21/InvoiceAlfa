<template>
  <div class="form-invoice q-pa-sm q-gutter-md" :class="{ editable: isEditable }">
    <div class="print-paper-wrapper A4">
      <div class="print-paper">
        <div class="paper-content">
          <div class="invoice-header">
            <div class="invoice-header-inner">
              <div class="company-logo-wrapper">
                <img src="/img/artacom.png" alt="artacom logo" class="company-logo">
              </div>

              <div class="invoice-header-company">
                <h1 class="company-name">Artacomindo Jejaring Nusa</h1>
                <p class="company-address">Menara Palma Lt. 12, Jl. HR. Rasuna Said Blok X2 Kav.6-Jakarta 12950</p>
              </div>
            </div>
            <div class="invoice-header-bottom">
              INVOICE
            </div>
          </div>

          <div class="invoice-recipient">
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Kepada Yth/To
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <div class="invoice-recipient-company-name">
                  {{ formEntry.customer_name }}
                </div>
                <div class="invoice-recepient-company-address">
                  {{ formEntry.customer_address }}
                </div>
              </div>

            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  NPWP
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formEntry.customer_npwp">{{ formEntry.customer_npwp }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  v-model="formEntry.customer_npwp"
                  filled
                  borderless
                  name="customer_npwp"
                  autocomplete="off"
                  dense
                />
              </div>
            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Nomor Invoice/Invoice Number
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                {{ formEntry.invoice_no }}
              </div>
            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Tanggal/Date
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formattedPublishedAt">{{ formattedPublishedAt }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  :value="formattedPublishedAt"
                  filled
                  borderless
                  name="published_at"
                  autocomplete="off"
                  dense
                  lazy-rules="ondemand"
                  :rules="rules.published_at"
                  :error="!!errors.published_at"
                  :error-message="errors.published_at"
                >
                  <q-menu>
                    <q-date v-model="formEntry.published_at" minimal mask="DD/MM/YYYY">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-menu>
                </q-input>
              </div>
            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Surat Penawaran/Our Ref.
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formEntry.offering_letter_reference_number">{{ formEntry.offering_letter_reference_number }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  v-model="formEntry.offering_letter_reference_number"
                  filled
                  borderless
                  name="offering_letter_reference_number"
                  autocomplete="off"
                  dense
                />
              </div>
            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Surat Penawaran FO/Our Ref.
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formEntry.fo_offering_letter_reference_number">{{ formEntry.fo_offering_letter_reference_number }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  v-model="formEntry.fo_offering_letter_reference_number"
                  filled
                  borderless
                  name="fo_offering_letter_reference_number"
                  autocomplete="off"
                  dense
                />
              </div>
            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Tanggal Persetujuan SAT-HO
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formattedApprovalDate">{{ formattedApprovalDate }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  :value="formattedApprovalDate"
                  filled
                  borderless
                  name="approval_date"
                  autocomplete="off"
                  dense
                >
                  <q-menu>
                    <q-date v-model="formEntry.approval_date" minimal mask="DD/MM/YYYY">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-menu>
                </q-input>
              </div>
            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Tanggal Persetujuan SAT-HO untuk FO
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formattedFoApprovalDate">{{ formattedFoApprovalDate }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  :value="formattedFoApprovalDate"
                  filled
                  borderless
                  name="fo_approval_date"
                  autocomplete="off"
                  dense
                >
                  <q-menu>
                    <q-date v-model="formEntry.fo_approval_date" minimal mask="DD/MM/YYYY">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-menu>
                </q-input>
              </div>
            </div>
            <div class="invoice-recipient-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Dasar untuk menerbitkan Invoice Tagihan
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formEntry.issuance_number">{{ formEntry.issuance_number }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  v-model="formEntry.issuance_number"
                  filled
                  borderless
                  name="issuance_number"
                  autocomplete="off"
                  dense
                />
              </div>
            </div>
            <div class="invoice-recipient-row last-row">
              <div class="invoice-recipient-key">
                <div class="invoice-recipient-label">
                  Dasar untuk menerbitkan Invoice Tagihan FO
                </div>
                <div class="invoice-recipient-colon">:</div>
              </div>
              <div class="invoice-recipient-value">
                <template v-if="!isEditable">
                  <span v-if="formEntry.fo_issuance_number">{{ formEntry.fo_issuance_number }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  v-model="formEntry.fo_issuance_number"
                  filled
                  borderless
                  name="fo_issuance_number"
                  autocomplete="off"
                  dense
                />
              </div>
            </div>
          </div>

          <div class="invoice-table">
            <div class="invoice-table-row invoice-table-header">
              <div class="invoice-table-left">
                <div class="invoice-table-no">
                  No.
                </div>
                <div class="invoice-table-description">
                  Description
                </div>
              </div>
              <div class="invoice-table-right">
                <div class="invoice-table-qty">
                  Qty
                </div>
                <div class="invoice-table-unit-price">
                  Unit Price
                </div>
                <div class="invoice-table-sub-total">
                  Sub Total
                </div>
              </div>
            </div>
            <template v-for="(service, i) in formServices">
              <form-invoice-service
                :ref="`formService${i}`"
                :index="i"
                :id="service.id"
                :editable="isEditable"
                :description.sync="service.description"
                :qty.sync="service.qty"
                :unit-price.sync="service.unit_price"
                :key="'srv'+service.id"
                @delete="onFormServiceDelete"
              />
            </template>
            <form-invoice-service
              :index="null"
              :id="$utils.generateId()"
              :editable="false"
            />


            <div v-if="isEditable" class="invoice-table-row">
              <div class="invoice-table-left">
                <div class="invoice-table-no">

                </div>
                <div class="invoice-table-description">
                  <q-btn
                    unelevated
                    color="primary"
                    size="sm"
                    padding="sm"
                    icon="add"
                    style="min-width: 5rem; width: 60%; margin: 0 auto;display: block"
                    class="btn-form-invoice-service-delete"
                    @click.prevent="onFormServiceAdd"
                  >
                    <span class="q-ml-xs">{{ $t('Add' )}}</span>
                  </q-btn>
                </div>
              </div>
              <div class="invoice-table-right">
                <div class="invoice-table-qty">

                </div>
                <div class="invoice-table-unit-price">

                </div>
                <div class="invoice-table-sub-total">

                </div>
              </div>
            </div>

          </div>

          <div class="invoice-payment">
            <div class="invoice-payment-left">
              <q-btn-dropdown
                v-if="isEditable"
                class="btn-edit"
                size="sm"
                icon="edit"
                flat
                dense
                color="primary"
                rounded
              >
                <q-list>
                  <q-item clickable v-close-popup @click="onTransferToTypeChange($constant.transfer_to_type.VirtualAccount)">
                    {{ $t('Virtual Account') }}
                  </q-item>
                  <q-item clickable v-close-popup @click="onTransferToTypeChange($constant.transfer_to_type.BankTransfer)">
                    {{ $t('Bank Transfer') }}
                  </q-item>
                </q-list>
              </q-btn-dropdown>

              <template v-if="formEntry.transfer_to_type == $constant.transfer_to_type.BankTransfer">
                Pembayaran dapat ditransfer ke rekening:<br>
                {{ templateSettings[$constant.setting_key.BankTransferName] }}<br>
                A/C {{ templateSettings[$constant.setting_key.BankTransferAccountNumber] }}<br>
                A/N {{ templateSettings[$constant.setting_key.BankTransferAccountName] }}
              </template>
              <template v-else-if="formEntry.transfer_to_type == $constant.transfer_to_type.VirtualAccount">
                Pembayaran dapat ditransfer ke:<br>
                <div class="virtual-account-detail">
                  {{ customer.transfer_to_virtual_account_bank_name }} Virtual Account<br>
                  {{ customer.transfer_to_virtual_account_number }}
                </div>
              </template>
            </div>
            <div class="invoice-payment-right">
              <div class="invoice-payment-right-row">
                <div class="invoice-payment-right-key">
                  Sub Total
                </div>
                <div class="invoice-payment-right-value">
                  {{ formattedSubTotal }}
                </div>
              </div>
              <div class="invoice-payment-right-row">
                <div class="invoice-payment-right-key">
                  VAT (PPN {{ formEntry.ppn_percentage }}%)
                </div>
                <div class="invoice-payment-right-value">
                  {{ formattedPpnTotal }}
                </div>
              </div>
              <div class="invoice-payment-right-row">
                <div class="invoice-payment-right-key">
                  {{ $t('Stamp Duty') }}
                </div>
                <div class="invoice-payment-right-value">
                  <template v-if="!isEditable">
                    <span v-if="formEntry.stamp_duty">{{ formEntry.stamp_duty }}</span>
                    <span v-else v-html="'&nbsp;'"></span>
                  </template>
                  <q-input
                    v-else
                    v-model="formEntry.stamp_duty"
                    filled
                    borderless
                    name="stamp_duty"
                    autocomplete="off"
                    dense
                    @keypress="$globalListeners.onKeypressOnlyFloat($event)"
                  />
                </div>
              </div>
              <div class="invoice-payment-right-row last-row">
                <div class="invoice-payment-right-key">
                  Total
                </div>
                <div class="invoice-payment-right-value">
                  {{  formattedGrandTotal }}
                </div>
              </div>
            </div>
          </div>

          <div class="invoice-note">
            <div class="invoice-note-row">
              &nbsp;
            </div>
            <div class="invoice-note-row">
              Terbilang: <em class="transfer-text"># {{ formattedGrandTotalTerbilang }} #</em>
            </div>
            <div class="invoice-note-row bold">
              <div class="invoice-note-row-split">
                <div class="invoice-note-row-split-left">
                  {{ $t('Due date') }}
                </div>
                <div class="invoice-note-row-split-right">
                  <div class="invoice-due-at-wrapper">
                    <span class="invoice-due-at-sep">:</span>

                    <template v-if="!isEditable">
                      <span v-if="formattedDueAt">{{ formattedDueAt }}</span>
                      <span v-else v-html="'&nbsp;'"></span>
                    </template>
                    <q-input
                      v-else
                      :value="formattedDueAt"
                      filled
                      borderless
                      name="due_at"
                      autocomplete="off"
                      dense
                      lazy-rules="ondemand"
                      :rules="rules.due_at"
                      :error="!!errors.due_at"
                      :error-message="errors.due_at"
                    >
                      <q-menu>
                        <q-date v-model="formEntry.due_at" minimal mask="DD/MM/YYYY">
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-date>
                      </q-menu>
                    </q-input>
                  </div>
                </div>
              </div>
            </div>
            <div class="invoice-note-row">
              <div class="note-wrapper">
                <span class="note-label">
                  Catatan:
                </span>

                <div class="note-input-wrapper">
                  <template v-if="!isEditable">
                    <span v-if="formEntry.note" v-html="formEntry.note"></span>
                    <span v-else v-html="'&nbsp;'"></span>
                  </template>
                  <text-editor
                    v-else
                    v-model="formEntry.note"
                    filled
                    borderless
                    min-height="1rem"
                    name="note"
                    autocomplete="off"
                    dense
                  />
                </div>
              </div>
              <!-- Catatan: Keterlambatan Pembayaran menyebabkan ketidaknyamanan Pelayanan Jaringan ke Pelanggan

              <strong>Note:</strong> Bukti transfer mohon dikirim via: <em class="transfer-note-to">(nazirin.nawawi@ajnusa.com)</em> -->
            </div>

          </div>

          <div class="invoice-signature">
            <div class="invoice-signature-inner">
              <div class="invoice-signature-date" :class="{ expand: !templateSettings[$constant.setting_key.SignatureImage] }">
                Jakarta, {{ formattedPublishedAt }}
              </div>
              <div v-if="templateSettings[$constant.setting_key.SignatureImage]" class="person-signature">
                <img :src="templateSettings[$constant.setting_key.SignatureImage]" class="signature">
              </div>
              <div v-if="templateSettings[$constant.setting_key.StampImage]" class="signature-stamp">
                <img :src="templateSettings[$constant.setting_key.StampImage]" class="signature">
              </div>
              <div class="invoice-signature-person">
                <div class="invoice-signature-person-name">
                  ({{ formEntry.signatory_name }})
                </div>
                <div class="invoice-signature-person-role">
                  {{ formEntry.signatory_position }}
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>


  </div>
</template>

<script>
import { date, debounce } from 'quasar'
import FormInvoiceService from './FormInvoiceService'

const DEFAULT_FORM_ENTRY = {
  distribution_center_id: null,
  franchise_id: null,
  // invoice_no: null,
  // receipt_no: null,
  published_at: null,
  offering_letter_reference_number: null,
  fo_offering_letter_reference_number: null,
  approval_date: null,
  fo_approval_date: null,
  issuance_number: null,
  fo_issuance_number: null,
  // customer_name: null,
  // customer_address: null,

  sub_total: null,
  ppn_percentage: null,
  ppn_total: null,
  stamp_duty: null,
  total: null,

  due_at: null,
  note: '',


  signatory_name: null,
  signatory_position: null
}

const DEFAULT_FORM_SERVICE = {
  id: null,
  description: null,
  qty: null,
  unit_price: null
}

export default {
  name: 'FormPage',
  components: {
    FormInvoiceService
  },
  props: {
    entry: {
      type: Object,
      default() {
        return {}
      }
    },
    loading: Boolean,
    syncing: Boolean,
    fetching: Boolean,
    editable: {
      type: Boolean,
      default: false
    },
    closable: {
      type: Boolean,
      default: true
    },
    templateSettings: {
      type: [Object, Array],
      default() {
        return {}
      }
    },
    customer: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      formEntry: DEFAULT_FORM_ENTRY,
      isSyncing: false,
      isLoading: false,
      rules: {
        published_at: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Publish date') }),
          // v => !v || (!!v && this.$utils.isDateValid(v)) || this.$t('{field} is invalid', { field: this.$t('Publish date') })
        ],
        due_at: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Due date') }),
          // v => !v || (!!v && this.$utils.isDateValid(v)) || this.$t('{field} is invalid', { field: this.$t('Due date') })
        ],
      },
      errors: DEFAULT_FORM_ENTRY,
      isEditable: true,
      defaultFormEntry: DEFAULT_FORM_ENTRY,
      formServices: [],
      emitChanges: null
    }
  },
  computed: {
    isCreate() {
      return !this.entry.id
    },
    readonly() {
      if (this.isEditable) {
        return false
      }

      return !this.isCreate
    },
    parentDistributionCenterId() {
      return parseInt(this.$route.query.distribution_center_id, 10) || null
    },
    formattedApprovalDate() {
      if (this.formEntry.approval_date) {
        return date.formatDate(date.extractDate(this.formEntry.approval_date, 'DD/MM/YYYY'), 'DD-MMMM-YYYY')
      }
    },
    formattedFoApprovalDate() {
      if (this.formEntry.fo_approval_date) {
        return date.formatDate(date.extractDate(this.formEntry.fo_approval_date, 'DD/MM/YYYY'), 'DD-MMMM-YYYY')
      }
    },
    formattedPublishedAt() {
      if (this.formEntry.published_at) {
        return date.formatDate(date.extractDate(this.formEntry.published_at, 'DD/MM/YYYY'), 'DD-MMMM-YYYY')
      }
    },
    formattedDueAt() {
      if (this.formEntry.due_at) {
        return date.formatDate(date.extractDate(this.formEntry.due_at, 'DD/MM/YYYY'), 'DD-MMMM-YYYY')
      }
    },
    formattedGrandTotalTerbilang() {
      return this.$utils.terbilang(this.grandTotal)
    },
    grandTotal() {
      return this.subTotal + this.ppnTotal + this.stampDuty
    },
    formattedGrandTotal() {
      return this.$utils.currency(this.grandTotal, {
        decimal: '.',
        thousand: ',',
      })
    },
    ppnTotal() {
      return this.subTotal * ((this.formEntry.ppn_percentage || 0) / 100)
    },
    formattedPpnTotal() {
      return this.$utils.currency(this.ppnTotal, {
        decimal: '.',
        thousand: ',',
      })
    },
    subTotal() {
      let total = 0

      this.formServices.forEach(v => {
        if (v.qty && v.unit_price) {
          const result = ((parseFloat(v.qty, 10) || 0) * (parseFloat(v.unit_price, 10) || 0)) || 0

          total += result
        }
      })

      return total
    },
    formattedSubTotal() {
      return this.$utils.currency(this.subTotal, {
        decimal: '.',
        thousand: ',',
      })
    },
    stampDuty() {
      if (this.formEntry.stamp_duty) {
        return parseFloat(this.formEntry.stamp_duty, 10) || 0
      }

      return 0
    }
  },
  watch: {
    entry: {
      immediate: true,
      handler(n) {
        this.$nextTick(() => {
          this.fill(n)
        })
      }
    },
    editable: {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.isEditable) {
          this.isEditable = n
        }
      }
    },
    isEditable(n, o) {
      if (n !== o && n !== this.editable) {
        this.$emit('update:editable', n)

        this.$nextTick(() => {
          setTimeout(() => {
            this.$forceUpdate()
          }, 100)
        })
      }
    },
    isLoading(n, o) {
      if (n !== o && n !== this.loading) {
        this.$emit('update:loading', n)
      }
    },
    syncing: {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.isSyncing) {
          this.isSyncing = true
        }
      }
    },
    isSyncing(n, o) {
      if (n !== o && n !== this.syncing) {
        this.$emit('update:syncing', n)
      }
    },
    formEntry: {
      deep: true,
      handler(n) {
        this.isSyncing = true;

        this.$nextTick(() => {
          setTimeout(() => {
            this.emitChanges()
          }, 100)
        })
      }
    },
    formServices: {
      deep: true,
      handler(n) {
        this.isSyncing = true

        this.$nextTick(() => {
          this.$forceUpdate()

          setTimeout(() => {
            this.emitChanges()
          }, 100)
        })
      }
    },
    templateSettings: {
      deep: true,
      handler(n) {
        if (this.isCreate) {
          const v = n || {}
          const constant = this.$constant.setting_key || {}
          this.formEntry.ppn_percentage = v[constant.PpnPercentage] ? parseFloat(v[constant.PpnPercentage]) : null
          this.formEntry.stamp_duty = v[constant.StampDuty] ? parseFloat(v[constant.StampDuty]) : null
          this.formEntry.signatory_name = v[constant.SignatoryName]
          this.formEntry.signatory_position = v[constant.SignatoryPosition]
          this.formEntry.note = v[constant.InvoiceNote]

          this.$nextTick(() => {
            this.$forceUpdate()
          })
        }
      }
    }
  },
  mounted() {
    this.emitChanges = debounce(() => {
      this.$emit('updated', {
        ...this.formEntry,
        services: [...this.formServices.map(v => ({ ...v }))],
        sub_total: this.subTotal,
        ppn: this.ppnTotal,
        grand_total: this.grandTotal,
        grand_total_terbilang: this.formattedGrandTotalTerbilang
      })

      this.isSyncing = false;
    }, 1500)
  },
  methods: {
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form };

      if (form.sla) {
        form.sla = parseFloat(form.sla, 10)
      }

      if (form.created_at) {
        form.created_at = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
        form.updated_at = date.formatDate(form.updated_at, 'DD MMM YYYY HH:mm')
      } else {
        const defaultFormEntry = {}

        for (const key in DEFAULT_FORM_ENTRY) {
          defaultFormEntry[key] = form[key]
        }

        this.defaultFormEntry = defaultFormEntry
      }

      if (form.approval_date) {
        form.approval_date = date.formatDate(form.approval_date, 'DD/MM/YYYY')
      }
      if (form.fo_approval_date) {
        form.fo_approval_date = date.formatDate(form.fo_approval_date, 'DD/MM/YYYY')
      }
      if (form.due_at) {
        form.due_at = date.formatDate(form.due_at, 'DD/MM/YYYY')
      }
      if (form.published_at) {
        form.published_at = date.formatDate(form.published_at, 'DD/MM/YYYY')
      }



      if (!form.transfer_to_type) {
        form.transfer_to_type = this.$constant.transfer_to_type.BankTransfer
      }

      if (this.parentDistributionCenterId) {
        form.distribution_center_id = this.parentDistributionCenterId
        this.defaultFormEntry.distribution_center_id = this.parentDistributionCenterId
      }

      const formServices = form.invoice_services || []
      delete form.invoice_services

      this.formEntry = form;

      if (formServices.length) {
        this.formServices = formServices
      } else {
        this.formServices = [this.generateFormService()]
      }

      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }

      setTimeout(() => {
        this.emitChanges()
      }, 250)
    },
    async validate() {
      const rules = this.rules
      let errMsg = true
      let formEntryErrMsg

      for (const name in rules) {
        for (let i = 0; i < rules[name].length; i++) {
          formEntryErrMsg = await rules[name][i](this.formEntry[name])

          if (typeof formEntryErrMsg === 'string') {
            this.errors[name] = formEntryErrMsg

            if (errMsg === true) {
              errMsg = formEntryErrMsg
            }
          }
        }
      }

      const formServices = this.formServices
      const formServiceLen = formServices.length
      let formServiceErrMsg
      let currentFormServiceRef

      for (let i = 0; i < formServiceLen; i++) {
        currentFormServiceRef = this.$refs[`formService${i}`]

        if (Array.isArray(currentFormServiceRef)) {
          currentFormServiceRef = currentFormServiceRef[0]
        }

        if (currentFormServiceRef) {
          formServiceErrMsg = currentFormServiceRef.validate()

          if (errMsg === true) {
            errMsg = formServiceErrMsg
          }
        }
      }

      return errMsg
    },
    isDirty() {
      return false || this.$utils.isDirty(this.defaultFormEntry, this.formEntry)
    },
    async onSubmit() {
      if (this.loading || this.isLoading) {
        return;
      }

      if (this.readonly) {
        return
      }

      const isValid = await this.$refs.form.validate();

      if (!isValid) {
        return;
      }

      const entry = { ...this.formEntry }

      if (entry.approval_date) {
        entry.approval_date = date.formatDate(date.extractDate(entry.approval_date, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }

      if (entry.fo_approval_date) {
        entry.fo_approval_date = date.formatDate(date.extractDate(entry.fo_approval_date, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }

      this.isLoading = true;

      try {
        const endpoint = entry.id ? `/v1/stores/${entry.id}` : '/v1/stores';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.defaultFormEntry = { ...this.formEntry }
          this.$emit('success', data.data.store);
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
        if (err.validation) {
          const validationErrors = {
            ...DEFAULT_FORM_ENTRY,
            ...err.validation.errors
          }

          this.errors = validationErrors
        } else {
          this.$q.notify(err);
        }
      }

      this.isLoading = false;
    },
    cancel() {
      if (this.loading || this.isLoading) {
        return;
      }

      this.isEditable = false
    },
    onEdit() {
      this.isEditable = true
    },
    onFormServiceAdd() {
      this.formServices = [
        ...this.formServices,
        this.generateFormService()
      ]
    },
    generateFormService() {
      return {
        id: this.$utils.generateId(),
        description: null,
        qty: null,
        unit_price: null
      }
    },
    onFormServiceDelete(id) {
      this.formServices = this.formServices.filter(v => v.id !== id)
    },
    onDelete() {
      if (this.isLoading) {
        return;
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('store') }),
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
        try {
          let { data } = await this.$api.delete(`/v1/stores/${this.entry.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.$emit('deleted', data)
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Store') }) })
              this.$emit('deleted', data)
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('store') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
      })
    },
    onTransferToTypeChange(to) {
      this.formEntry.transfer_to_type = to
    }
  }
}
</script>
