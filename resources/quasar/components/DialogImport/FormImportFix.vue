<template>
  <q-card class="form-import-fix q-pa-sm">

    <table-distribution-center
      v-if="importType == $constant.import_type.DistributionCenter"
      :import-type="importType"
      :import-path="importPath"
      @success="$emit('success')"
    />
    <table-store
      v-else-if="importType == $constant.import_type.Store"
      :import-type="importType"
      :import-path="importPath"
      @success="$emit('success')"
    />
    <table-franchise
      v-else-if="importType == $constant.import_type.Franchise"
      :import-type="importType"
      :import-path="importPath"
      @success="$emit('success')"
    />
  </q-card>
</template>

<script>
import TableDistributionCenter from './TableDistributionCenter'
import TableStore from './TableStore'
import TableFranchise from './TableFranchise'

export default {
  name: 'FormImportFix',
  components: {
    TableDistributionCenter,
    TableStore,
    TableFranchise
  },
  props: {
    loading: Boolean,
    importType: {
      type: [String, Number]
    },
    importPath: {
      type: String
    },
    templateUrl: {
      type: String
    }
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
    }
  },
  computed: {
    isCreate() {
      return !this.formEntry.id
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

      this.isLoading = true;

      let endpoint = null
      const importType = this.importType
      const constant = this.$constant.import_type
      const params = {}

      if (importType === constant.DistributionCenter) {
        endpoint = `/v1/distribution-centers/import/errors`
      } else if (importType === constant.Store) {
        endpoint = `/v1/stores/import/errors`
        params.distribution_center_id = this.$route.params.id
      } else if (importType === constant.Franchise) {
        endpoint = `/v1/franchises/import/errors`
        params.distribution_center_id = this.$route.params.id
      }

      if (!endpoint) {
        this.$q.notify({ message: this.$t('Fatal error occurred. No import type given. Please try again.'), type: 'error' })
        this.isLoading = false
        return
      }

      try {
        let { data } = await this.$api.get(endpoint, { params });

        if (data.status === 'success') {
          this.entries = data.data.entries
          this.meta = data.data.meta
        }
      } catch (err) {
        if (err.validation) {
          this.errors = {
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
.form-import-fix {
  width: 100%;

  @media (min-width: $breakpoint-sm-min) {
    max-width: 100% !important;
  }
}
</style>
