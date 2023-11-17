<template>
  <q-card
    class="card-payment-proof"
    :class="{ dragging: isDragging }"
    :flat="flat"
    @dragover="onDragOver"
    @dragenter="onDragEnter"
    @dragleave="onDragLeave"
    @drop="onDrop"
  >
    <q-card-section v-if="showPaymentDate">
      <p class="label-header label-actual-payment">
        {{ $t('Actual Payment Date') }}
      </p>

      <div class="row q-col-gutter-sm">
        <div class="col col-md-6 col-lg-5 col-xl-4">
          <q-skeleton v-if="fetching" type="rect" />
          <q-input
            v-if="readonly"
            v-show="!isFetching"
            :value="formattedFoApprovalDate"
            :label="$t('Payment Date')"
            :filled="!readonly"
            :borderless="readonly"
            :readonly="readonly"
            stack-label
            :placeholder="readonly ? '-' : ''"
            name="actual_payment_date"
            autocomplete="off"
            :dense="!readonly"
          />
          <q-input
            v-else
            v-show="!fetching"
            v-model="actualPaymentDate"
            :label="$t('Paid at')"
            :filled="!readonly"
            :borderless="readonly"
            :readonly="readonly"
            stack-label
            :placeholder="readonly ? '-' : ''"
            name="actual_payment_date"
            autocomplete="off"
            :dense="!readonly"
            :rules="rules.actual_payment_date"
            :error="!!errors.actual_payment_date"
            :error-message="errors.actual_payment_date"
            mask="##/##/####"
            :hint="$utils.isDateValid(actualPaymentDate) ? undefined : $t('Valid format: DD/MM/YYYY')"
          >
            <template v-slot:append>
              <q-icon v-if="!readonly" name="event" class="cursor-pointer">
                <q-popup-proxy transition-show="scale" transition-hide="scale">
                  <q-date v-model="actualPaymentDate" minimal mask="DD/MM/YYYY">
                    <div class="row items-center justify-end">
                      <q-btn v-close-popup label="Close" color="primary" flat />
                    </div>
                  </q-date>
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </div>


      </div>

    </q-card-section>
    <q-card-section>
      <p class="label-header">
        {{ $t('Payment Proof') }}
      </p>

      <p class="label-meta">
        {{ $t('Select max 5 photos of payment proof and click Upload.') }}
      </p>
      <div class="bill-attachments">
        <bill-attachment
          v-for="(attachment, i) in paymentProofs"
          v-model="paymentProofs[i].file"
          :preview.sync="paymentProofs[i].preview"
          :min-dimension="attachmentRules.minDimension"
          :max-file-size="attachmentRules.maxFileSize"
          :accept="attachmentRules.accept"
          :url="attachment.url"
          :key="`attch${attachment.id}`"
          :readonly="isUploading"
          :editable="editable"
          :addable="addable && !attachment.preview"
          :uploading="uploadingPaymentProofs[attachment.id]"
          @input="onAttachmentInput(attachment, i)"
          @deleted="onAttachmentDeleted(attachment, i)"
        />
      </div>

      <!-- <p class="label-meta">
        {{ $t('Add a remark if necessary') }}
      </p>

      <q-input
        :label="$t('Remark')"
        v-model="paymentProofRemark"
        filled
        stack-label
        row="1"
        autogrow
        type="textarea"
      /> -->
    </q-card-section>

    <q-card-actions v-if="uploadable">
      <q-space />
      <q-btn
        v-show="canUpload"
        class="q-px-xl"
        color="primary"
        icon="send"
        :loading="isUploading"
        @click="onUpload"
      >
        <span class="q-ml-sm">{{ $t('Submit') }}</span>
      </q-btn>
    </q-card-actions>
  </q-card>
</template>

<script>
import { date } from 'quasar'
import BillAttachment from './BillAttachment'

export default {
  name: 'CardPaymentProof',
  components: {
    BillAttachment
  },
  props: {
    invoice: {
      type: Object,
      default() {
        return {}
      }
    },
    fetching: Boolean,
    flat: Boolean,
    uploadable: {
      type: Boolean,
      default: true
    },
    editable: Boolean,
    addable: {
      type: Boolean,
      default: true
    },
    showPaymentDate: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isFetching: false,
      isLoading: false,
      isDragging: false,
      isUploading: false,
      settings: {},
      paymentProofs: [],
      uploadingPaymentProofs: {},
      deletePaymentProofs: [],
      paymentProofRemark: '',
      canUpload: true,
      actualPaymentDate: null,
      attachmentRules: {
        maxFileSize: 1024 * 1024 * 10,
        minDimension: 300,
        accept: 'image/png, image/jpeg, image/jpg, image/svg+xml, image/heic, image/heif'
      },
      rules: {
        actual_payment_date: [
          v => !v || (!!v && this.$utils.isDateValid(v)) || this.$t('{field} is invalid', { field: this.$t('Date') })
        ],
      },
      errors: {}
    }
  },
  computed: {
    constant() {
      return this.$constant.setting_key
    },
    readonly() {
      return !(this.uploadable || this.editable || this.addable)
    },
    formattedActualPaymentDate() {
      if (this.readonly && this.actualPaymentDate) {
        return date.formatDate(date.extractDate(this.actualPaymentDate, 'DD/MM/YYYY'), 'DD MMM YYYY')
      }
    },
  },
  watch: {
    invoice: {
      deep: true,
      immediate: true,
      handler(n) {
        this.$nextTick(() => {
          if (n && n.id) {
            if (n.actual_payment_date) {
              this.actualPaymentDate = date.formatDate(n.actual_payment_date, 'DD/MM/YYYY')
            } else {
              this.actualPaymentDate = null
            }

            this.canUpload = true

            if (this.editable) {
              this.fill(this.invoice.invoice_payment_proofs)
            } else {
              this.onRequest()
            }
          } else {
            this.actualPaymentDate = null
          }
        })
      }
    }
  },
  methods: {
    fill(form) {
      let paymentProofs = (form || []).map(v => {
        v.file = v.file || null
        v.preview = v.preview || v.url || null
        return v
      })

      if (paymentProofs.length < 5) {
        if (!paymentProofs.find(v => !v.preview)) {
          paymentProofs.push(this.generateBlankAttachment())
        }
      } else {
        this.canUpload = false
      }

      this.paymentProofs = paymentProofs
    },
    async onRequest() {
      if (this.isLoading) {
        return
      }

      this.isFetching = true
      this.isLoading = true
      this.deletePaymentProofs = []

      const params = {
        invoice_id: this.invoice.id
      }

      try {
        const { data } = await this.$api.get(`/v1/invoices/payment-proofs`, { params })

        if (data.status === 'success') {
          this.fill(data.data.invoice_payment_proofs)
        } else {
          this.paymentProofs = [this.generateBlankAttachment()]
        }
      } catch (err) {
        this.$q.notify(err)
        this.paymentProofs = [this.generateBlankAttachment()]
      }

      this.isFetching = false
      this.isLoading = false
    },
    async onSubmit() {
      const formEntry = {
        actual_payment_date: null
      }

      if (this.actualPaymentDate) {
        formEntry.actual_payment_date = date.formatDate(date.extractDate(this.actualPaymentDate, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      } else {}

      try {
        const { data } = await this.$api.patch(`/v1/invoices/${this.invoice.id}`, formEntry);

        if (data.status === 'success') {
          return true
        }
      } catch (err) {
        this.$q.notify(err);
      }

      return false
    },
    async onUpload(throws = false) {
      const formEntry = {}

      if (this.actualPaymentDate) {
        formEntry.actual_payment_date = date.formatDate(date.extractDate(this.actualPaymentDate, 'DD/MM/YYYY'), 'YYYY-MM-DD')
      }

      const uploadingFile = this.paymentProofs.filter(v => !!v.file)
      const alreadyHasPaymentProofs = this.paymentProofs.filter((v) => {

        return !!v.id
      }).length > 0

      if (!uploadingFile.length && !this.deletePaymentProofs.length && !alreadyHasPaymentProofs) {
        this.$q.notify({
          type: 'negative',
          message: this.$t('Please add at least 1 photo to upload')
        })

        if (throws) {
          throw new Error(this.$t('Please add at least 1 photo to upload'))
        }

        return
      }

      if (this.isUploading) {
        return
      }

      const isSubmitted = await this.onSubmit()

      if (!isSubmitted) {
        return false
      }

      this.uploadingPaymentProofs = {}
      this.isUploading = true

      let hasDeleted = false

      try {
        hasDeleted = await this.onDelete()
      } catch (err) {
        this.isUploading = false

        if (throws) {
          throw err
        }

        return
      }

      try {
        const hasUploaded = await this.onUploadItemIndex(-1)

        if (hasUploaded >= 0) {
          this.$q.notify({ message: this.$t('Payment proofs successfully sent') })
        } else if (hasDeleted) {
          this.$q.notify({ message: this.$t('Payment proofs successfully updated') })
        }
        this.$emit('uploaded')
      } catch (err) {
        if (throws) {
          throw err
        }
      }

      if (this.paymentProofs.filter(v => !!v.url).length >= 5) {
        this.canUpload = false
      }

      this.isUploading = false
    },
    async onUploadItemIndex(i, uploadedI = 0) {
      if (i >= 5) {
        return uploadedI
      }

      i++

      const uploadingFile = this.paymentProofs.find((v, j) => j === i)

      if (!uploadingFile || !uploadingFile.file) {
        return this.onUploadItemIndex(i)
      }

      this.uploadingPaymentProofs = { ...this.uploadingPaymentProofs, [uploadingFile.id]: true }

      const formData = new FormData()
      formData.append('id', this.invoice.id)
      formData.append('payment_proof', uploadingFile.file)

      try {
        const { data } = await this.$api.post(`/v1/invoices/payment-proofs`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        this.uploadingPaymentProofs = { ...this.uploadingPaymentProofs, [uploadingFile.id]: false }

        if (data.status === 'success') {
          const invoicePaymentProof = data.data.invoice_payment_proof || {}
          invoicePaymentProof.file = null
          invoicePaymentProof.preview = invoicePaymentProof.url

          this.paymentProofs[i] = invoicePaymentProof
          return this.onUploadItemIndex(i, ++uploadedI)
        } else  {
          this.$q.notify({ message: data.message})
        }
      } catch (err) {
        this.$q.notify(err)

        this.uploadingPaymentProofs = { ...this.uploadingPaymentProofs, [uploadingFile.id]: false }
        throw err
      }

      return uploadedI
    },
    async onDelete() {
      const payload = {
        id: this.deletePaymentProofs.map(v => v.id)
      }

      if (!payload.id.length) {
        return
      }

      try {
        const { data } = await this.$api.post(`/v1/invoices/payment-proofs/delete`, payload)

        if (data.status === 'success') {
          this.deletePaymentProofs = []
        }
      } catch (err) {
        this.$q.notify(err)
        throw err
      }
    },
    onDragOver(e) {
      e.preventDefault()

      const paymentProofs = this.paymentProofs

      if (paymentProofs.length < 5 || paymentProofs.find(v => !v.preview)) {
        this.isDragging = true
      }
    },
    onDragEnter(e) {
      const paymentProofs = this.paymentProofs

      if (paymentProofs.length < 5 || paymentProofs.find(v => !v.preview)) {
        this.isDragging = true
      }
    },
    onDragLeave(e) {
      this.isDragging = false
    },
    async onDrop(e) {
      e.preventDefault()

      this.isDragging = false
      const paymentProofs = this.paymentProofs
      const attachmentRules = this.attachmentRules
      const foundIndex = paymentProofs.findIndex(v => !v.preview)

      if (paymentProofs.length < 5 || foundIndex !== -1) {
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
          const file = e.dataTransfer.files[0]

          if (file.size > attachmentRules.maxFileSize) {
            this.$q.notify({
              type: 'negative',
              message: this.$t('File size exceeds maximum limit: {max}', { max: this.$utils.humanStorageSize(attachmentRules.maxFileSize) })
            })

            return
          }

          if (!(file.type && attachmentRules.accept.includes(file.type))) {
            this.$q.notify({
              type: 'negative',
              message: this.$t('The selected file is unsupported. Supported files: {entity}', { entity: '.png, .jpg, .jpeg, .svg, .heic' })
            })

            return
          }

          const minDimension = attachmentRules.minDimension
          const isDimensionValid = await BillAttachment.methods.validateDimension(file, minDimension)

          if (!isDimensionValid) {
            this.$q.notify({
              type: 'negative',
              message: this.$t('Image resolution must be greater than {resolution}', { resolution: `${minDimension}x${minDimension}` })
            })

            return
          }

          const preview = await BillAttachment.methods.generateFilePreview(file)

          this.paymentProofs[foundIndex] = {
            ...this.paymentProofs[foundIndex],
            file,
            preview
          }

          this.paymentProofs = [...this.paymentProofs]

          this.$emit('updated', this.paymentProofs)

          this.addBlankAttachment()
        }
      }
    },
    onAttachmentInput(attachment, i) {
      this.addBlankAttachment()

      this.$emit('updated', this.paymentProofs)
    },
    onAttachmentDeleted(attachment, i) {
      this.paymentProofs = this.paymentProofs.filter(v => v.id !== attachment.id)

      this.addBlankAttachment()

      if (attachment.url) {
        this.deletePaymentProofs.push(attachment)
      }

      this.$nextTick(() => {
        this.$emit('updated', this.paymentProofs)
      })
    },
    addBlankAttachment() {
      this.$nextTick(() => {
        if (this.paymentProofs.length < 5 && !this.paymentProofs.find(v => !v.preview)) {
          this.paymentProofs = [...this.paymentProofs, this.generateBlankAttachment()]
        }
      })
    },
    generateBlankAttachment() {
      return {
        id: 'new:'+this.$utils.generateId(),
        preview: null,
        file: null
      }
    }
  }
}
</script>

<style lang="scss">
.card-payment-proof {
  &.dragging {
    user-select: none;
    border: 1px dashed rgba(0, 0, 0, 0.5);
  }

  > .q-card__section {
    .label-header {
      font-size: 0.9em;
      font-weight: 500;
      margin-bottom: 0;

      &.label-actual-payment {
        margin-bottom: 0.5rem;
      }
    }

    .label-meta {
      color: rgba(49, 53, 60, 0.6);
      font-size: 0.9em;
    }
  }

  .bill-attachments {
    display: flex;
    margin-bottom: 0.5rem;

    > .bill-attachment {
      margin-left: 0.75rem;

      &:first-child {
        margin-left: 0;
      }
    }
  }
}
</style>
