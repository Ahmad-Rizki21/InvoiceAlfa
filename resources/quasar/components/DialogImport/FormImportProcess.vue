<template>
  <q-card class="form-import-process q-pa-sm">
    <q-form ref="form" class="q-gutter-md">
      <div class="text-h6 q-px-md q-pt-sm">
        {{ $t('Import') }}
      </div>
      <q-card-section class="q-mb-md">
        <div class="row q-col-gutter-sm">
          <div class="col-xs-12">
            <div class="importing">
              <q-spinner
                color="primary"
                size="3em"
                :thickness="2"
              />

              <p>{{ $t('Validating rows') }}&hellip;</p>
            </div>
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn
          :label="$t('Cancel')"
          flat
          disable
          @click="cancel"
        />
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script>
export default {
  name: 'FormImportProcess',
  props: {
    loading: Boolean,
    importType: {
      type: [String, Number]
    },
    status: {
      type: String
    },
    importPath: {
      type: String
    },
    processingPage: {
      type: [String, Number],
      default() {
        return 1
      }
    }
  },
  data() {
    return {
      isLoading: false,
    }
  },
  mounted() {
    this.onSubmit()
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
      const importPath = this.importPath

      if (!importPath) {
        this.$q.notify({ message: this.$t('Fatal error occurred. No import path given. Please try again.'), type: 'error' })
        this.isLoading = false
        return
      }
      const payload = {
        import_path: importPath
      }

      if (importType === constant.DistributionCenter) {
        endpoint = `/v1/distribution-centers/import/process`
      } else if (importType === constant.Store) {
        endpoint = `/v1/stores/import/process`
        payload.distribution_center_id = this.$route.params.id
      } else if (importType === constant.Franchise) {
        endpoint = `/v1/franchises/import/process`
        payload.distribution_center_id = this.$route.params.id
      }

      if (!endpoint) {
        this.$q.notify({ message: this.$t('Fatal error occurred. No import type given. Please try again.'), type: 'error' })
        this.isLoading = false
        return
      }

      try {
        let { data } = await this.$api.post(endpoint, payload, {
          params: {
            page: this.processingPage
          }
        });

        if (data.status !== 'success') {
          this.$q.notify({ message: data.message || this.$t('Failed to process import file') })
          setTimeout(() => {
            this.$store.dispatch('imports/reset')
            // window.location.reload()
          }, 2000)
        } else {
          if (data.meta && data.meta.has_more_page) {
            this.$emit('updated', {
              page: this.processingPage + 1,
              hasError: data.data.has_error
            })

            this.$nextTick(() => {
              setTimeout(() => {
                this.isLoading = false;

                this.onSubmit()
              }, 300)
            })
          } else {
            this.$emit('success', {
              hasError: data.data.has_error
            })
          }
        }
      } catch (err) {
        if (err.validation) {
          this.errors = {
            ...err.validation.errors
          }
        } else {
          this.$q.notify(err);
          // window.location.reload()
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
  }
}
</script>

<style lang="scss" scoped>
.form-import-process {
  width: 100%;

  @media (min-width: $breakpoint-sm-min) {
    max-width: 480px !important;
  }

  .importing {
    text-align: center;

    .q-spinner {
      margin-bottom: 1.5rem;
    }
  }
}
</style>
