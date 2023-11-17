<template>
  <div class="form-page form-invoice-page q-pa-sm q-gutter-md">
    <q-card class="q-pa-sm" flat>
      <q-form ref="form" class="form-entry" greedy :class="{ readonly }" @submit.prevent="onSubmit">
        <q-card-section>
          <div class="row">
            <div :class="{ 'col-xs-12': !readonly, 'col-xs-11': readonly }">

              <div class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md">
                <div class="col-xs-12 col-sm-8 col-md-4 col-lg-3">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-select
                    v-show="!fetching"
                    v-model="formEntry.status"
                    :label="$t('Status') + '*'"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="status"
                    autocomplete="off"
                    :options="statusOptions"
                    option-label="label"
                    :option-disable="statusOptionDisable"
                    emit-value
                    :display-value="selectedStatus"
                    :dense="!readonly"
                    :rules="rules.status"
                    :error="!!errors.status"
                    :error-message="errors.status"
                  />
                </div>
              </div>

              <div v-if="formEntry.status == $constant.invoice_status.Rejected" class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md">
                <div class="col-xs-12">
                  <q-skeleton v-if="fetching" type="rect" />
                  <q-input
                    v-show="!fetching"
                    v-model="formEntry.reject_reason"
                    :label="$t('Reject Reason')"
                    :filled="!readonly"
                    :borderless="readonly"
                    :readonly="readonly"
                    stack-label
                    :placeholder="readonly ? '-' : ''"
                    name="reject_reason"
                    autocomplete="off"
                    :dense="!readonly"
                    :rules="rules.reject_reason"
                    :error="!!errors.reject_reason"
                    :error-message="errors.reject_reason"
                  />
                </div>
              </div>

              <div class="row">
                <div class="col-xs-12 col-md-3">
                  <q-input
                    v-show="!fetching && formEntry.actual_payment_date"
                    :value="formEntry.actual_payment_date"
                    :label="$t('Actual Payment Date')"
                    borderless
                    readonly
                    :dense="!readonly"
                  />
                </div>
              </div>
              <div
                v-if="(formEntry.invoice_payment_proofs && formEntry.invoice_payment_proofs.length) || editable"
                class="row q-col-gutter-x-sm q-col-gutter-y-md q-mb-md"
              >
                <div class="col-xs-12">
                  <q-field
                    :label="$t('Payment Proof')"
                    borderless
                    readonly
                    stack-label
                  >
                    <card-payment-proof
                      ref="cardPaymentProof"
                      :invoice="formEntry"
                      :uploadable="false"
                      :editable="editable"
                      flat
                      :addable="editable"
                      @updated="onPaymentProofUpdated"
                    />
                  </q-field>
                </div>
              </div>

              <template v-if="readonly">
                <div class="row">
                  <div class="col-xs-12 col-md-3">
                    <q-input
                      v-show="!fetching && formEntry.unpaid_updated_at"
                      :value="formEntry.unpaid_updated_at"
                      :label="$t('Published At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                  <div class="col-xs-12 col-md-3">
                    <q-input
                      v-show="!fetching && formEntry.pending_review_updated_at"
                      :value="formEntry.pending_review_updated_at"
                      :label="$t('Pending Review At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                  <div class="col-xs-12 col-md-3">
                    <q-input
                      v-show="!fetching && formEntry.paid_at"
                      :value="formEntry.paid_at"
                      :label="$t('Paid At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                  <div class="col-xs-12 col-md-3">
                    <q-input
                      v-show="!fetching && formEntry.rejected_at"
                      :value="formEntry.rejected_at"
                      :label="$t('Rejected At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                </div>
                <div class="row">
                  <div class="col-xs-12 col-md-3">
                    <q-input
                      v-show="!fetching"
                      :value="formEntry.created_at"
                      :label="$t('Created At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                  <div class="col-xs-12 col-md-3">
                    <q-input
                      v-show="!fetching"
                      :value="formEntry.updated_at"
                      :label="$t('Updated At')"
                      borderless
                      readonly
                      :dense="!readonly"
                    />
                  </div>
                </div>
              </template>
            </div>
          </div>
        </q-card-section>
      </q-form>
    </q-card>
  </div>
</template>

<script>
import { date } from 'quasar'
import CardPaymentProof from '../CustPay/CardPaymentProof'

const DEFAULT_FORM_ENTRY = {
  id: null,
  invoice_no: null,
  receipt_no: null,
  created_at: null,
  updated_at: null,
  invoice_services: [],
  invoice_payment_proofs: [],
  status: null,
  reject_reason: null
}

export default {
  name: 'FormPage',
  components: {
    CardPaymentProof
  },
  props: {
    entry: {
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
    }
  },
  data() {
    return {
      formEntry: DEFAULT_FORM_ENTRY,
      isLoading: false,
      rules: {
        name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Name') })
        ],
        username: [
          v => !v || (!!v && v.length > 3) || this.$t('{field} must be at least {length} characters', { field: this.$t('Username'), length: 3 }),
          v => !v || (!!v && /^[a-zA-Z0-9_\.]+$/.test(v)) || this.$t('{field} can only contain alphanumeric characters, underscores, and periods', { field: this.$t('Username') }),
          v => !v || (!!v && /^[^0-9][a-zA-Z0-9_\.]+$/.test(v)) || this.$t('{field} must be starts with letter', { field: this.$t('Username') })
        ],
        email: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Email') }),
          (v) =>
            (!!v && this.$utils.isEmail(v)) ||
            this.$t('{field} is invalid', { field: this.$t('Email') }),
        ],
        password: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password') }),
          v => v && v.length >= 6 || this.$t('{field} must be at least {length} characters', { field: this.$t('Password'), length: 6 })
        ],
        password_confirmation: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Password confirmation') }),
          v => v === this.formEntry.password || this.$t('{field} does not match', { field: this.$t('Password confirmation') })
        ]
      },
      errors: DEFAULT_FORM_ENTRY,
      isEditable: false,
      defaultFormEntry: DEFAULT_FORM_ENTRY,
      childTab: 'franchise'
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
    formattedApprovalDate() {
      if (this.readonly && this.formEntry.approval_date) {
        return date.formatDate(date.extractDate(this.formEntry.approval_date, 'DD/MM/YYYY'), 'DD MMM YYYY')
      }
    },
    formattedFoApprovalDate() {
      if (this.readonly && this.formEntry.fo_approval_date) {
        return date.formatDate(date.extractDate(this.formEntry.fo_approval_date, 'DD/MM/YYYY'), 'DD MMM YYYY')
      }
    },
    selectedStatus() {
      const selected = this.statusOptions.find(v => v.value == this.formEntry.status)

      if (selected) {
        return selected.label
      }

      return ''
    },
    statusOptions() {
      const constant = this.$constant.invoice_status

      const options = [
        {
          value: constant.Draft,
          label: this.$t('Draft')
        },
        {
          value: constant.Unpaid,
          label: this.$t('Publish') + ' (' + this.$t('Unpaid') + ')'
        },
        {
          value: constant.PendingReview,
          label: this.$t('Pending Review')
        },
        {
          value: constant.Paid,
          label: this.$t('Paid')
        },
        {
          value: constant.Rejected,
          label: this.$t('Rejected')
        },
      ]

      if (this.readonly) {
        return options
      }

      const status = this.entry.status

      if (status == constant.Draft) {
        return options.filter(v => [constant.Draft, constant.Unpaid].includes(v.value))
      }

      if (status == constant.Unpaid) {
        return options.filter(v => [constant.Draft, constant.Unpaid, constant.PendingReview].includes(v.value))
      }

      if (status == constant.PendingReview) {
        return options.filter(v => [constant.PendingReview, constant.Paid, constant.Rejected].includes(v.value))
      }

      if (status == constant.Paid) {
        return options.filter(v => [constant.Paid, constant.Rejected].includes(v.value))
      }

      if (status == constant.Rejected) {
        return options.filter(v => [constant.Rejected, constant.Paid, constant.PendingReview].includes(v.value))
      }

      return options
    }
  },
  watch: {
    entry: {
      immediate: true,
      deep: true,
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

          this.formEntry = {
            ...this.defaultFormEntry,
            invoice_payment_proofs: (this.defaultFormEntry.invoice_payment_proofs || []).map(v => {
              v.file = null
              return { ...v }
            })
          }

          if (this.$refs.form) {
            this.$refs.form.resetValidation();
          }
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
    }
  },
  methods: {
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form }

      if (form.created_at) {
        form.created_at = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
        form.updated_at = date.formatDate(form.updated_at, 'DD MMM YYYY HH:mm')

        if (form.approval_date) {
          form.approval_date = date.formatDate(form.approval_date, 'DD/MM/YYYY')
        }
        if (form.fo_approval_date) {
          form.fo_approval_date = date.formatDate(form.fo_approval_date, 'DD/MM/YYYY')
        }
      }

      form.unpaid_updated_at = form.unpaid_updated_at ? date.formatDate(form.unpaid_updated_at, 'DD MMM YYYY HH:mm') : null
      form.pending_review_updated_at = form.pending_review_updated_at ? date.formatDate(form.pending_review_updated_at, 'DD MMM YYYY HH:mm') : null
      form.paid_at = form.paid_at ? date.formatDate(form.paid_at, 'DD MMM YYYY HH:mm') : null
      form.rejected_at = form.rejected_at ? date.formatDate(form.rejected_at, 'DD MMM YYYY HH:mm') : null
      form.actual_payment_date = form.actual_payment_date ? date.formatDate(form.actual_payment_date, 'DD MMM YYYY') : null

      if (form.invoice_payment_proofs) {
        form.invoice_payment_proofs = (form.invoice_payment_proofs || []).map(v => {
          v.file = null
          return { ...v }
        })
      }

      const defaultFormEntry = {}

      for (const key in DEFAULT_FORM_ENTRY) {
        if (key === 'invoice_payment_proofs') {
          defaultFormEntry[key] = (form[key] || []).map(v => {
            v.file = null
            return { ...v }
          })
        } else {
          defaultFormEntry[key] = form[key]
        }
      }

      this.defaultFormEntry = defaultFormEntry

      this.formEntry = form;
      if (this.$refs.form) {
        this.$refs.form.resetValidation();
      }
    },
    async uploadPaymentProofs() {
      await this.$refs.cardPaymentProof.onUpload(true)
    },
    onPaymentProofUpdated(paymentProofs) {
      this.formEntry.invoice_payment_proofs = paymentProofs
      this.formEntry.invoice_payment_proof_dirty = true
    },
    statusOptionDisable(item) {
      if (this.readonly) {
        return item
      }

      return item
    },
    isDirty() {
      return this.$utils.isDirty(this.defaultFormEntry, this.formEntry)
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
        const endpoint = entry.id ? `/v1/invoices/${entry.id}` : '/v1/invoices';
        const method = entry.id ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.defaultFormEntry = { ...this.formEntry }
          this.$emit('success', data.data.invoice);
          this.isEditable = false
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
    onDelete() {
      if (this.isLoading) {
        return;
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('distribution center') }),
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
          let { data } = await this.$api.delete(`/v1/distribution-centers/${this.entry.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.$emit('deleted', data)
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Distribution center') }) })
              this.$emit('deleted', data)
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('distribution center') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
      })
    },
    onTableRelationFranchiseCreate() {
      this.$router.push({
        path: `/franchises/create`,
        query: {
          distribution_center_id: this.entry.id
        }
      })
    },
    onTableRelationStoreCreate() {
      this.$router.push({
        path: `/stores/create`,
        query: {
          distribution_center_id: this.entry.id
        }
      })
    }
  }
}
</script>

<style lang="scss">
.form-invoice-page {
  @media (min-width: $breakpoint-sm-min) {
    // max-width: 400px !important;
  }

  &.readonly {
    .q-field.q-textarea.q-field--readonly {
      line-height: 1.4;
    }
  }

  .card-payment-proof {
    > .q-card__section {
      // padding: 0;
      padding-top: 0.5rem;
      margin-left: -1rem;
    }

    .label-header,
    .label-meta {
      display: none;
    }
  }
}
</style>
