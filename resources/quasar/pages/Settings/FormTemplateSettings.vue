<template>
  <q-card class="form-entry q-pa-sm form-template-settings" :class="{ readonly }">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <q-card-section>
        <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Monetary') }}</div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-sm-6 col-md-3">
            <q-input
              v-model="formEntry[constant.StampDuty]"
              :label="$t('Stamp Duty')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              stack-label
              :name="'' + constant.StampDuty"
              autocomplete="off"
              :rules="rules[constant.StampDuty]"
              :error="!!errors[constant.StampDuty]"
              :error-message="errors[constant.StampDuty]"
              @keypress="$globalListeners.onKeypressOnlyFloat($event)"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3">
            <q-input
              v-model="formEntry[constant.PpnPercentage]"
              :label="$t('PPN Percentage')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :name="'' + constant.PpnPercentage"
              stack-label
              suffix="%"
              autocomplete="off"
              dense
              :rules="rules[constant.PpnPercentage]"
              :error="!!errors[constant.PpnPercentage]"
              :error-message="errors[constant.PpnPercentage]"
              @keypress="$globalListeners.onKeypressOnlyFloat($event)"
            />
          </div>
        </div>
        <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Signatory') }}</div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-input
              v-model="formEntry[constant.SignatoryName]"
              :label="$t('Signatory Name') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              :name="'' + constant.SignatoryName"
              stack-label
              autocomplete="off"
              :rules="rules[constant.SignatoryName]"
              :error="!!errors[constant.SignatoryName]"
              :error-message="errors[constant.SignatoryName]"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-input
              v-model="formEntry[constant.SignatoryPosition]"
              :label="$t('Signatory Position') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :name="'' + constant.SignatoryPosition"
              stack-label
              autocomplete="off"
              dense
              :rules="rules[constant.SignatoryPosition]"
              :error="!!errors[constant.SignatoryPosition]"
              :error-message="errors[constant.SignatoryPosition]"
            />
          </div>
          <div class="col-xs-12 col-sm-8 col-md-8">
            <q-field
              :label="$t('Signature') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :name="'' + constant.SignatureImage"
              stack-label
              autocomplete="off"
              dense
              :rules="rules[constant.SignatureImage]"
              :error="!!errors[constant.SignatureImage]"
              :error-message="errors[constant.SignatureImage]"
            >
              <div class="signature-image-wrapper">
                <div class="q-btns q-ml-auto">
                  <q-btn
                    :label="$t('Upload')"
                    :color="signatureImageMode === 'upload' ? 'primary' : 'secondary'"
                    padding="xs"
                    :class="{ 'font-weight-bold': signatureImageMode === 'upload' }"
                    flat
                    @click="signatureImageMode = 'upload'"
                  />
                  <q-btn
                    :label="$t('Draw')"
                    :color="signatureImageMode === 'draw' ? 'primary' : 'secondary'"
                    padding="xs"
                    :class="{ 'font-weight-bold': signatureImageMode === 'draw' }"
                    flat
                    @click="signatureImageMode = 'draw'"
                  />
                </div>

                <upload-field
                  v-if="signatureImageMode === 'upload'"
                  v-model="formEntry[constant.SignatureImage]"
                  :preview.sync="signatureImagePreview"
                  :min-dimension="signatureImageRules.minDimension"
                  :max-file-size="signatureImageRules.maxFileSize"
                  :accept="signatureImageRules.accept"
                  :url="formEntry[constant.SignatureImage]"
                  :readonly="false"
                  :editable="true"
                  :addable="!signatureImagePreview"
                  :uploading="false"
                  @deleted="signatureImagePreview = null"
                />
                <signature-pad
                  v-else
                  :url="formEntry[constant.SignatureImage]"
                  ref="signaturePad"
                />
              </div>
            </q-field>
          </div>
          <div class="col-xs-12 col-sm-8 col-md-8">
            <q-field
              :label="$t('Stamp') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :name="'' + constant.StampImage"
              stack-label
              autocomplete="off"
              dense
              :rules="rules[constant.StampImage]"
              :error="!!errors[constant.StampImage]"
              :error-message="errors[constant.StampImage]"
            >
              <div class="stamp-image-wrapper">
                <upload-field
                  v-model="formEntry[constant.StampImage]"
                  :preview.sync="stampImagePreview"
                  :min-dimension="signatureImageRules.minDimension"
                  :max-file-size="signatureImageRules.maxFileSize"
                  :accept="signatureImageRules.accept"
                  :url="formEntry[constant.StampImage]"
                  :readonly="false"
                  :editable="true"
                  :addable="!stampImagePreview"
                  :uploading="false"
                  @deleted="stampImagePreview = null"
                />
              </div>
            </q-field>
          </div>
        </div>
        <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Bank Transfer') }}</div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-sm-6 col-md-9">
            <q-input
              v-model="formEntry[constant.BankTransferName]"
              :label="$t('Bank Name') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              :name="'' + constant.BankTransferName"
              stack-label
              autocomplete="off"
              :rules="rules[constant.BankTransferName]"
              :error="!!errors[constant.BankTransferName]"
              :error-message="errors[constant.BankTransferName]"
            />
          </div>
        </div>
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-sm-6 col-md-4">
            <q-input
              v-model="formEntry[constant.BankTransferAccountNumber]"
              :label="$t('Account Number') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :name="'' + constant.BankTransferAccountNumber"
              stack-label
              autocomplete="off"
              dense
              :rules="rules[constant.BankTransferAccountNumber]"
              :error="!!errors[constant.BankTransferAccountNumber]"
              :error-message="errors[constant.BankTransferAccountNumber]"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-5">
            <q-input
              v-model="formEntry[constant.BankTransferAccountName]"
              :label="$t('Account Name') + '*'"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :name="'' + constant.BankTransferAccountName"
              stack-label
              autocomplete="off"
              dense
              :rules="rules[constant.BankTransferAccountName]"
              :error="!!errors[constant.BankTransferAccountName]"
              :error-message="errors[constant.BankTransferAccountName]"
            />
          </div>
        </div>
        <div class="q-mb-md text-subtitle text-weight-bold">{{ $t('Misc') }}</div>

        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-md-8">
            <text-editor
              v-model="formEntry[constant.InvoiceNote]"
              :label="$t('Invoice Note')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              dense
              :name="'' + constant.InvoiceNote"
              stack-label
              autocomplete="off"
              :rules="rules[constant.InvoiceNote]"
              :error="!!errors[constant.InvoiceNote]"
              :error-message="errors[constant.InvoiceNote]"
            />
          </div>
        </div>
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12 col-md-4 col-lg-3">
            <q-input
              v-model="formEntry[constant.InjectInvoiceNo]"
              :label="$t('Last Invoice No')"
              :filled="!readonly"
              :borderless="readonly"
              :readonly="readonly"
              :name="'' + constant.InjectInvoiceNo"
              stack-label
              autocomplete="off"
              dense
              :rules="rules[constant.InjectInvoiceNo]"
              :error="!!errors[constant.InjectInvoiceNo]"
              :error-message="errors[constant.InjectInvoiceNo]"
              @keypress="$globalListeners.onKeypressOnlyUnsignedNumber($event)"
            />
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions v-if="readonly" align="right">
        <q-btn
          :label="$t('Close')"
          flat
          :disable="loading || isLoading"
          @click="cancel"
        />
      </q-card-actions>
      <q-card-actions v-else align="right">
        <q-btn
          :label="$t('Reset')"
          flat
          :disable="loading || isLoading"
          @click="cancel"
        />
        <q-btn
          type="submit"
          :label="$t('Save')"
          color="primary"
          :loading="loading || isLoading"
          unelevated
          class="q-px-lg"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script>
export default {
  name: 'FormEntry',
  props: {
    loading: Boolean,
    closable: {
      type: Boolean,
      default: true
    },
    entry: {
      type: Object,
      default() {
        return this.generateDefaultFormEntry()
      }
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      formEntry: this.generateDefaultFormEntry(),
      isLoading: false,
      rules: {
        terminal_name: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Terminal name') })
        ],
        location: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Location') })
        ],
        online_at: [
          v => !v || (!!v && this.$utils.isDateValid(v)) || this.$t('{field} is invalid', { field: this.$t('Online at') })
        ],
        latitude: [

        ],
        longitude: [

        ],
        pic_name: [

        ],
        pic_phone_number: [

        ],
        sla: [

        ]
      },
      errors: this.generateDefaultFormEntry(),
      signatureImageRules: {
        maxFileSize: 1024 * 1024 * 3,
        minDimension: 100,
        accept: 'image/png'
      },
      signatureImagePreview: null,
      stampImagePreview: null,
      signatureImageDrawing: null,
      signatureImageMode: 'upload',
      originalEntry: {}
    }
  },
  computed: {
    isCreate() {
      return !this.formEntry.id
    },
    constant() {
      return this.$constant.setting_key || {
        InvoiceNote: 3
      }
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
  },
  methods: {
    generateDefaultFormEntry() {
      const constant = this.constant || {}

      return {
        [constant.BankTransferAccountName]: null,
        [constant.BankTransferAccountNumber]: null,
        [constant.BankTransferName]: null,
        [constant.InvoiceNote || 3]: '',
        [constant.PpnPercentage]: null,
        [constant.SignatoryName]: null,
        [constant.SignatoryPosition]: null,
        [constant.StampDuty]: null,
        [constant.SignatureImage]: null,
        [constant.StampImage]: null,
        [constant.InjectInvoiceNo]: null,
      }
    },
    fill(form) {
      form = { ...this.generateDefaultFormEntry(), ...form };
      form[this.constant.InvoiceNote || 3] = form[this.constant.InvoiceNote || 3] || ''
      this.originalEntry = { ...form }
      this.formEntry = form;
      this.signatureImagePreview = form[this.constant.SignatureImage] || null
      this.stampImagePreview = form[this.constant.StampImage] || null
      this.signatureImageMode = 'upload'
      this.$refs.form.resetValidation();
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

      const constant = this.$constant.setting_key
      const entry = { ...this.formEntry }
      const formData = new FormData()


      if (this.signatureImageMode === 'draw') {
        if (this.$refs.signaturePad) {
          if (this.$refs.signaturePad.isEmpty()) {
            entry[constant.SignatureImage] = ''
          } else {
            entry[constant.SignatureImage] = this.$refs.signaturePad.toBlob()
          }
        } else {
          entry[constant.SignatureImage] = ''
        }
      }

      for (const key in entry) {
        if (entry[key] === null || entry[key] === undefined) {
          entry[key] = ''
        }

        formData.append(key, entry[key])
      }

      this.isLoading = true;

      try {
        let { data } = await this.$api.post('/v1/settings', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        if (data.status === 'success') {
          this.$emit('success', data.data.settings);
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Settings') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('settings') })
          }
        }
      } catch (err) {
        if (err.validation) {
          this.errors = {
            ...this.generateDefaultFormEntry(),
            ...err.validation.errors
          }
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

      this.$emit('cancel');

      this.signatureImagePreview = null
      this.stampImagePreview = null
      this.fill(this.originalEntry)
    },
    onDoubleInputChildKeydown(e) {
      let value = String(e.target.value).replace(/[^0-9]/g, '')
      const isNumber = /^[0-9]$/i.test(e.key)

      if (isNumber) {
        value = `${value}${e.key}`
      }

      if (value > 59 || String(value).length > 2) {
        if (isNumber) {
          e.preventDefault()
        }
      }
    },
  }
}
</script>

<style lang="scss">
.form-template-settings {
  .signature-image-wrapper {
    width: 100%;

    .q-btns {
      position: absolute;
      top: 0;
      right: 0;
    }

    .upload-field {
      margin-top: 1rem;
      height: 12rem;

      .attachment-image,
      .action {
        width: 25rem;
      }
    }

    .attachment-image {
      display: flex;
      align-items: center;
    }
  }
  .stamp-image-wrapper {
    width: 100%;

    .q-btns {
      position: absolute;
      top: 0;
      right: 0;
    }

    .upload-field {
      margin-top: 1rem;
      height: 10rem;

      .attachment-image,
      .action {
        width: 10rem;
      }
    }
  }
}
</style>
