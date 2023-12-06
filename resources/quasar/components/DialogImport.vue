<template>
  <q-dialog
    v-model="value"
    persistent
    :full-width="status === 'fix'"
    :full-height="status === 'fix'"
  >
    <form-import-upload
      v-if="!status"
      :import-type="importType"
      :template-url="templateUrl"
      @success="onFormImportUploadSuccess"
      @cancel="onFormImportUploadCancel"
    />
    <form-import-cache
      v-else-if="status === 'uploaded'"
      :import-type="importType"
      :status="status"
      :import-path="importPath"
      @success="onFormImportCacheSuccess"
    />
    <form-import-process
      v-else-if="status === 'cached'"
      :import-type="importType"
      :status="status"
      :import-path="importPath"
      :processing-page="processingPage"
      @updated="onFormImportProcessUpdated"
      @success="onFormImportProcessSuccess"
    />
    <form-import-fix
      v-else-if="status === 'fix'"
      :import-type="importType"
      :status="status"
      :import-path="importPath"
      :processing-page="processingPage"
      @success="onFormImportFixSuccess"
    />
  </q-dialog>
</template>

<script>
import FormImportUpload from './DialogImport/FormImportUpload'
import FormImportCache from './DialogImport/FormImportCache'
import FormImportProcess from './DialogImport/FormImportProcess'
import FormImportFix from './DialogImport/FormImportFix'

export default {
  name: 'DialogImport',
  components: {
    FormImportUpload,
    FormImportCache,
    FormImportProcess,
    FormImportFix
  },
  props: {
    importType: {
      type: [String, Number]
    },
    status: {
      type: String,
    },
    importPath: {
      type: String
    },
    templateUrl: {
      type: String
    },
    processingPage: {
      type: [String, Number],
      default() {
        return 1
      }
    },
    hasError: {
      type: Boolean,
      default: false
    },
    value: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      visible: false
    }
  },
  watch: {
    status: {
      immediate: true,
      handler(n, o) {
        if (n !== o) {
          if (n === 'proceed') {
            this.onFormImportFixSuccess()
          }
        }
      }
    },
    value: {
      immediate: true,
      handler(n, o) {
        if (n !== o && n !== this.visible) {
          this.visible = n
        }
      }
    },
    visible(n, o) {
      if (n !== o && n !== this.value) {
        this.$emit('input', n)
      }
    }
  },
  methods: {
    onFormImportUploadCancel() {
      this.visible = false
    },
    onFormImportUploadSuccess(importPath) {
      this.$emit('update:status', {
        status: 'uploaded',
        importPath
      })
    },
    onFormImportCacheSuccess(importPath) {
      this.$emit('update:status', {
        status: 'cached',
        importPath
      })
    },
    onFormImportProcessUpdated({ page, hasError }) {
      this.$emit('update:status', {
        status: 'cached',
        importPath: this.importPath,
        processingPage: page || 1,
        hasError
      })
    },
    onFormImportProcessSuccess({ hasError }) {
      const status = hasError || this.hasError ? 'fix' : 'proceed'
      this.$emit('update:status', {
        status,
        importPath: this.importPath,
        hasError
      })

      if (status === 'proceed') {
        this.onFormImportFixSuccess()
      }
    },
    onFormImportFixSuccess() {
      this.$store.dispatch('imports/reset')
      this.visible = false
      this.$emit('success')
    }
  }
}
</script>
