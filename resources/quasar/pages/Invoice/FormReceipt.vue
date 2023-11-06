<template>
  <div class="form-receipt q-pa-sm q-gutter-md" :class="{ editable: isEditable }">
    <div class="print-paper-wrapper A4">
      <div class="print-paper">
        <div class="paper-content">
          <div class="receipt-header">
            <div class="company-logo-wrapper">
              <img src="/img/artacom.png" alt="artacom logo" class="company-logo">
            </div>
          </div>

          <div class="receipt-number">
            <div class="receipt-number-inner">
              <div class="receipt-number-label">
                KWITANSI
              </div>
              <div class="receipt-number-content">
                Nomor: {{ formEntry.receipt_no }}
              </div>
            </div>
          </div>


          <div class="receipt-detail">
            <div class="receipt-detail-row">
              <div class="receipt-detail-key">
                <div class="receipt-detail-label">
                  Sudah Terima dari
                </div>
                <div class="receipt-detail-colon">
                  :
                </div>
              </div>

              <div class="receipt-detail-value">
                <div class="receipt-detail-recipient-company-name">
                  {{ formEntry.customer_name }}
                </div>
                <div class="receipt-detail-recipient-company-address">
                  {{ formEntry.customer_address }}
                </div>
              </div>
            </div>

            <div class="receipt-detail-row">
              <div class="receipt-detail-key">
                <div class="receipt-detail-label">
                  Pekerjaan
                </div>
                <div class="receipt-detail-colon">
                  :
                </div>
              </div>

              <div class="receipt-detail-value">
                <template v-if="!isEditable">
                  <span v-if="formEntry.receipt_remark">{{ formEntry.receipt_remark }}</span>
                  <span v-else v-html="'&nbsp;'"></span>
                </template>
                <q-input
                  v-else
                  v-model="formEntry.receipt_remark"
                  filled
                  type="textarea"
                  rows="1"
                  autogrow
                  borderless
                  name="receipt_remark"
                  autocomplete="off"
                  dense
                  lazy-rules="ondemand"
                  :rules="rules.receipt_remark"
                  :error="!!errors.receipt_remark"
                  :error-message="errors.receipt_remark"
                />
              </div>
            </div>

            <div class="receipt-detail-row">
              <div class="receipt-detail-key">
                <div class="receipt-detail-label">
                  Jumlah
                </div>
                <div class="receipt-detail-colon">
                  :
                </div>
              </div>

              <div class="receipt-detail-value">
                <div class="receipt-detail-grand-total">
                  Rp {{ formattedGrandTotal }}
                </div>
              </div>
            </div>

            <div class="receipt-detail-row">
              <div class="receipt-detail-key">
                <div class="receipt-detail-label">
                  Terbilang
                </div>
                <div class="receipt-detail-colon">
                  :
                </div>
              </div>

              <div class="receipt-detail-value">
                <div class="receipt-detail-grand-total-text">
                  # {{ formattedGrandTotalTerbilang }} #
                </div>
              </div>
            </div>
          </div>

          <div class="receipt-date">
            <div class="receipt-date-inner">
              Jakarta, {{ formattedPublishedAt }}
            </div>
          </div>

          <div class="receipt-footer">
            <div class="receipt-owner">
              <div class="receipt-owner-name">
                Artacomindo Jejaring Nusa
              </div>
              <div class="receipt-owner-address">
                Menara Palma Lt. 12, Jl. HR. Rasuna Said Blok X2 Kav.6-Jakarta 12950
              </div>
              <div class="receipt-owner-contact">
                Telp: 021-293911111, 894 5212, 894 4821
              </div>
            </div>

            <div class="receipt-signature">
              <div class="receipt-signature-name">
                ({{ invoice.signatory_name }})
              </div>
              <div class="receipt-signature-role">
                {{ invoice.signatory_position }}
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

const DEFAULT_FORM_ENTRY = {
  receipt_remark: null
}

export default {
  name: 'FormReceipt',
  props: {
    entry: {
      type: Object,
      default() {
        return {}
      }
    },
    invoice: {
      type: Object,
      default() {
        return {}
      }
    },
    loading: Boolean,
    fetching: Boolean,
    editable: Boolean,
    closable: {
      type: Boolean,
      default: true
    },
    templateSettings: {
      type: [Object, Array],
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      formEntry: DEFAULT_FORM_ENTRY,
      isLoading: false,
      rules: {
        receipt_remark: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Receipt remark') })
        ]
      },
      errors: DEFAULT_FORM_ENTRY,
      isEditable: true,
      defaultFormEntry: DEFAULT_FORM_ENTRY,
      formServices: [
        {
          id: this.$utils.generateId(),
          description: null,
          qty: null,
          unit_price: null
        }
      ]
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
    formattedPublishedAt() {
      if (this.invoice.published_at) {
        let format = 'DD/MM/YYYY'

        if (this.invoice.published_at.includes('-')) {
          format = 'YYYY-MM-DD'
        }

        return date.formatDate(date.extractDate(this.invoice.published_at, format), 'DD-MMMM-YYYY')
      }
    },
    formattedGrandTotalTerbilang() {
      const grandTotal = this.invoice.grand_total || this.invoice.total || 0
      return grandTotal ? this.$utils.terbilang(grandTotal) : ''
    },
    formattedGrandTotal() {
      return this.$utils.currency(this.invoice.grand_total || this.invoice.total || 0, {
        decimal: '.',
        thousand: ',',
      })
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
    'formEntry.receipt_remark': debounce(function (n, o) {
      this.$emit('updated', this.formEntry)
    }, 300),
    formServices: {
      deep: true,
      handler(n) {
        this.$nextTick(() => {
          this.$forceUpdate()
        })
      }
    }
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

      if (this.parentDistributionCenterId) {
        form.distribution_center_id = this.parentDistributionCenterId
        this.defaultFormEntry.distribution_center_id = this.parentDistributionCenterId
      }

      this.formEntry = form;

      this.errors = DEFAULT_FORM_ENTRY

      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
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
        {
          id: this.$utils.generateId(),
          description: null,
          qty: null,
          unit_price: null
        }
      ]
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
    }
  }
}
</script>

<style lang="scss">
.form-receipt {
  padding-bottom: 3rem;

  &.editable {
    > .print-paper-wrapper {
      > .print-paper {
        height: auto;
        min-height: 296mm;
        padding-bottom: 10mm;
      }
    }
  }

  .paper-content {
    font-family: Arial, sans-serif;
    font-size: 12px;
    font-weight: 500;
  }

  .receipt-header {
    .company-logo-wrapper {
      width: 120px;
    }

    .company-logo {
      width: 100%;
    }
  }

  .receipt-number {
    display: flex;
    width: 100%;
    padding-right: 2rem;
    margin-bottom: 2rem;

    > .receipt-number-inner {
      margin-left: auto;

      > .receipt-number-label {
        font-size: 23px;
        font-weight: 600;
        letter-spacing: 1.46343mm;
      }

      > .receipt-number-content {
        border: 1px solid #000;
        padding: 5px 10px;
      }
    }
  }

  .receipt-detail {
    margin-bottom: 4rem;

    > .receipt-detail-row {
      display: flex;
      padding-left: 10px;
      padding-right: 10px;
      margin-bottom: 20px;

      > .receipt-detail-key {
        width: 24%;
        display: flex;

        > .receipt-detail-label {
          flex: 1;
        }

        > .receipt-detail-colon {
          margin-left: 1px;
          margin-right: 10px;
        }
      }

      > .receipt-detail-value {
        flex: 1;

        .receipt-detail-recipient-company-name {
          font-weight: 600;
        }

        // .receipt-detail-recipient-company-address {

        // }

        .receipt-detail-grand-total {
          padding: 1px 10px 5px 5px;
          border: 1px solid #000;
          font-weight: 600;
          max-width: 80%;
        }
        .receipt-detail-grand-total-text {
          padding: 1px 10px 5px 5px;
          border: 1px solid #000;
          font-style: italic;
          max-width: 80%;
        }
      }
    }
  }

  .receipt-date {
    display: flex;
    margin-bottom: 6rem;

    > .receipt-date-inner {
      margin-left: auto;
      text-align: center;
      width: 50%;
    }
  }

  .receipt-footer {
    display: flex;
    margin-bottom: 2rem;

    > .receipt-owner {
      flex: 1;
      padding-left: 10px;
      padding-right: 10px;

      > .receipt-owner-name {
        font-size: 18px;
      }

      > .receipt-owner-address {
        font-size: 10px;
      }
      > .receipt-owner-contact {
        font-size: 10px;
      }
    }

    > .receipt-signature {
      width: 50%;
      text-align: center;

      > .receipt-signature-name {
        font-weight: 900;
        text-decoration: underline;
        margin-bottom: 5px;
      }

      // > .receipt-signature-role {

      // }
    }
  }
}
</style>
