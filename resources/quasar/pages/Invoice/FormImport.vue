<template>
  <q-card class="form-import-upload q-pa-sm">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div class="text-h6 q-px-md q-pt-sm">
        {{ $t('Import') }}
      </div>
      <q-card-section class="q-mb-md">
        <div v-if="templateUrl" class="row q-col-gutter-sm q-mb-lg">
          <div class="col-xs-12">
            <small><a :href="templateUrl" download>{{ $t('Download template') }}</a></small>
          </div>
        </div>
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <q-file
              v-model="internalValue"
              :label="$t('File') + '*'"
              filled
              :accept="xlsMimeType.join(',')"
              dense
              stack-label
              :max-file-size="maxFileSize"
              :rules="rules.file"
              :error="!!errors.file"
              :error-message="errors.file"
              @input="onChange"
              @rejected="onRejected"
            />
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn
          :label="$t('Cancel')"
          flat
          :disable="loading || isLoading"
          @click="cancel"
        />
        <q-btn
          type="submit"
          :label="$t('Import')"
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
  name: 'FormImportUpload',
  props: {
    loading: Boolean,
  },
  data() {
    return {
      internalValue: null,
      isLoading: false,
      rules: {
        file: [
          v => !!v || this.$t('{field} is required', { field: this.$t('File') })
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
      errors: {},
      xlsMimeType: [
        'application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/x-excel',
        'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
      ],
      maxFileSize: 1024 * 500
    }
  },
  computed: {
    isCreate() {
      return !this.formEntry.id
    },
    templateUrl() {
      return this.$q.lang.isoName === 'id' ? '/templates/template-invoice.xlsx' : '/templates/invoice-template.xlsx'
    },
    importType() {
      return this.$constant.import_type.Invoice
    }
  },
  methods: {
    async onSubmit() {
      if (this.loading || this.isLoading) {
        return;
      }

      const isValid = await this.$refs.form.validate();

      if (!isValid) {
        return;
      }

      const formData = new FormData()
      formData.append('file', this.internalValue)

      this.isLoading = true;

      let endpoint = null
      const importType = this.importType
      const constant = this.$constant.import_type

      if (importType === constant.DistributionCenter) {
        endpoint = `/v1/distribution-centers/import/upload`
      } else if (importType === constant.Store) {
        endpoint = `/v1/stores/import/upload`
        formData.append('distribution_center_id', this.$route.params.id)
      } else if (importType === constant.Franchise) {
        endpoint = `/v1/franchises/import/upload`
        formData.append('distribution_center_id', this.$route.params.id)
      }

      // if (!endpoint) {
      //   this.$q.notify({ message: this.$t('Fatal error occurred. No import type given. Please try again.'), type: 'error' })
      //   this.isLoading = false
      //   return
      // }

      try {
        let { data } = await this.$api.post('/v1/invoices/simple-import/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        if (data.status !== 'success') {
          if (data.data.wait) {
            await this.waitUpload(data.data.url)
          } else {
            this.$q.notify({ message: data.message || this.$t('Failed to upload import file') })
          }
        } else {
          if (data.data.url) {
            await this.waitUpload(data.data.url)
          } else {
            this.$emit('success')

            this.internalValue = null
          }
        }
      } catch (err) {
        if (err.validation) {
          this.errors = {
            ...err.validation.errors
          }
          this.internalValue = null
          this.isLoading = false;
        } else {
          this.isLoading = false
          this.cancel();
          this.$q.notify(err);
        }
      }

      this.isLoading = false;
    },
    async waitUpload(url) {
      await this.$utils.delay(7500)
      this.isLoading = true;

      try {
        let { data } = await this.$api.post(url);

        if (data.status !== 'success') {
          if (data.data.wait) {
            await this.waitUpload(url)
          } else {
            this.$q.notify({ message: data.message || this.$t('Failed to upload import file') })
            this.internalValue = null
          }
        } else {
          if (data.data.url) {
            await this.waitUpload(url)
          } else {
            this.$emit('success')
            this.isLoading = false;
            this.internalValue = null
          }
        }
      } catch (err) {
        if (err.validation) {
          this.errors = {
            ...err.validation.errors
          }
          this.internalValue = null
          this.isLoading = false;
        } else {
          this.$q.notify(err);
          this.isLoading = false
          this.cancel();
        }
      }
    },
    cancel() {
      if (this.loading || this.isLoading) {
        return;
      }

      this.$emit('cancel');
    },
    onChange(file) {
      this.errorMessages = [];

      if (!(file && file.type)) {
        this.internalValue = null
        return
      }

      this.internalValue = file;
    },
    onRejected(rejectedEntries) {
      let errMsg = this.$t('Selected file is not accepted. Please use another one.')
      let prop

      if (rejectedEntries && rejectedEntries[0] && rejectedEntries[0].failedPropValidation) {
        prop = rejectedEntries[0].failedPropValidation

        if (prop === 'accept') {
          errMsg = this.$t('The selected file is unsupported. Supported files: {entity}', { entity: '.png, .jpg, .jpeg, .svg, .heic' })
        } else if (prop === 'max-file-size') {
          errMsg = this.$t('File size exceeds maximum limit: {max}', { max: this.$utils.humanStorageSize(this.maxFileSize) })
        } else if (prop === 'max-dimension') {
          errMsg = this.$t('Image resolution must be greater than {resolution}', { resolution: `${this.minDimension}x${this.minDimension}` })
        }
      }

      this.$q.notify({
        type: 'negative',
        message: errMsg
      })
    },
  }
}
</script>

<style lang="scss" scoped>
.form-import-upload {
  width: 100%;

  @media (min-width: $breakpoint-sm-min) {
    max-width: 480px !important;
  }
}
</style>
