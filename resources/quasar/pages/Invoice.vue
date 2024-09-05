<template>
  <q-page class="page-invoices">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        {{ $t('Invoices') }}
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
      <q-toolbar-title>{{ $t('Invoices') }}</q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.invoice')"
        color="default"
        class="q-btn-lg btn-page-invoice-import q-mr-sm"
        icon="cloud_upload"
        @click="onFormImport"
      >
        <span class="q-ml-xs">{{ $t('Import') }}</span>
      </q-btn>

      <q-btn
        v-if="$auth.can('create.invoice')"
        color="secondary"
        class="q-ml-sm q-btn-lg btn-page-invoices-export"
        :loading="isExporting"
        icon="system_update_alt"
        @click="onFormEntryExport"
      >
        <span class="q-ml-sm">{{ $t('Export') }}</span>
      </q-btn>

      <q-btn
        v-if="$auth.can('create.invoice')"
        color="primary"
        class="q-btn-lg btn-page-invoices-create q-ml-md"
        icon="add"
        @click="onFormEntryCreate"
      >
        <span class="q-ml-xs">{{ $t('New {entity}', { entity: $t('Invoice') }) }}</span>
      </q-btn>
    </div>

    <div class="page-body">
      <q-card>
        <q-toolbar>

          <div class="col col-md-9 col-lg-8">
            <div class="row q-col-gutter-md quick-search-datatable">
              <div class="col col-md-3 col-lg-3">
                <q-input
                  v-model="search.applicable_month.formattedValue"
                  :label="$t('Period')"
                  autocomplete="off"
                  stack-label
                  class="input-quick-search-applicable_month"
                  dense
                  readonly
                >
                  <q-menu>
                    <monthpicker
                      v-model="search.applicable_month.value"
                      mask="DD/MM/YYYY"
                      :emit-immediately="false"
                      color="primary"
                      @input="onApplicableMonthChangeDebounced"
                    >
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </monthpicker>
                  </q-menu>
                </q-input>
              </div>

                <div class="col col-md-3 col-lg-3">
                <q-input
                  v-model="search.fuzzy.value"
                  :label="$t('Search {entity}', { entity: $t('no') })"
                  stack-label
                  :debounce="300"
                  dense
                  clearable
                  clear-icon="close"
                  @input="onRequest()"
                />
              </div>
              <div class="col col-md-3 col-lg-2">
                <select-invoice-status
                  v-model="search.status.value"
                  :label="$t('Search {entity}', { entity: $t('status') })"
                  stack-label
                  :debounce="300"
                  dense
                  class="input-search-datatable-status"
                  clearable
                  clear-icon="close"
                  @input="onRequest()"
                />
              </div>
            </div>
          </div>

          <div class="col col-md-3 col-lg-2 q-ml-auto row">
            <q-btn
              unelevated
              color="default"
              size="sm"
              icon="search"
              class="q-ml-auto btn-datatable-advanced-search"
              :disable="isLoading"
              @click="onAdvancedSearch"
            >
              {{ $t('Advanced Search') }}
            </q-btn>
            <q-btn
              unelevated
              color="default"
              size="sm"
              padding="xs"
              icon="view_column"
              class="q-ml-sm btn-datatable-view-columns"
            >
              <q-tooltip>
                {{ $t('View Column') }}
              </q-tooltip>

              <q-menu>
                <div class="row no-wrap q-pa-md" style="min-width: 200px">
                  <div class="column">
                    <q-option-group
                      style="margin-left: -16px;"
                      :options="columns.map(v => ({ label: v.realLabel || v.label, value: v.name })).filter(v => v.label)"
                      type="checkbox"
                      size="xs"
                      v-model="visibleColumns"
                    />
                  </div>
                </div>
              </q-menu>
            </q-btn>
          </div>
        </q-toolbar>

        <q-table
          ref="table"
          :data="entries"
          :columns="columns"
          :visible-columns="visibleColumns"
          :loading="isLoading"
          :pagination.sync="pagination"
          selection="multiple"
          :selected.sync="selectedEntries"
          binary-state-sort
          row-key="id"
          @request="onRequest"
          @selection="onSelection"
        >
          <template #top>
            <q-btn
              unelevated
              color="primary"
              icon="print"
              :disable="!selectedEntries.length"
              @click="isAdapterPrinterVisible = true"
            >
              <span class="q-ml-sm">{{ $t('Print') }}</span>
            </q-btn>
            <q-btn
              class="q-ml-sm"
              unelevated
              color="default"
              icon="delete"
              padding="sm"
              :disable="!selectedEntries.length"
              @click.prevent="onBulkDelete"
            >
            </q-btn>

            <span class="q-px-md q-py-sm" :class="{ 'text-default': !selectedEntries.length }">
              {{ $q.lang.table.selectedRecords(selectedEntries.length) }}
            </span>
          </template>

          <template #header-selection="scope">
            <q-checkbox v-model="scope.selected" />
          </template>

          <template #body-selection="scope">
            <q-checkbox :value="scope.selected" @input="(val, evt) => { Object.getOwnPropertyDescriptor(scope, 'selected').set(val, evt) }" />
          </template>

          <template #body-cell-id="{ rowIndex }">
            <q-td>{{ rowIndex + 1 }}</q-td>
          </template>
          <template #body-cell-status="{ row }">
            <q-td>
              <invoice-status-chip :invoice="row" dense />
            </q-td>
          </template>
          <template #body-cell-action="{ row }">
            <q-td class="text-right table-cell-action">
              <q-btn
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="visibility"
                class="btn-datatable-view-invoice"
                @click.prevent="onView(row)"
              >
                <q-tooltip>
                  {{ $t('View') }}
                </q-tooltip>
              </q-btn>
              <q-btn
                v-if="$auth.can('delete.invoice')"
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="delete"
                class="q-ml-xs btn-datatable-delete-invoice"
                @click.prevent="onDelete(row)"
              >
                <q-tooltip>
                  {{ $t('Delete') }}
                </q-tooltip>
              </q-btn>


            </q-td>
          </template>

          <template #no-data="{ icon, message, filter }">
            <div class="full-width row flex-center text-center q-gutter-sm">
              <q-icon size="2em" :name="filter ? 'filter_b_and_w' : icon" />
              <span>
                {{ message }}
              </span>
            </div>
          </template>
        </q-table>
      </q-card>
    </div>


    <q-dialog v-model="isAdvancedSearchVisible">
      <q-card class="q-pa-sm">
        <q-form @submit.prevent="onAdvancedSearchSubmit">
          <q-card-section class="q-pb-xs">
            <div class="text-h6">{{ $t('Search') }}</div>
          </q-card-section>
          <q-card-section class="q-mb-md">
            <q-input
              v-model="advancedSearch.fuzzy.value"
              :label="$t('Search {entity}', { entity: $t('name') })"
              outlined
              dense
            />
          </q-card-section>

          <q-separator />

          <q-card-actions align="right">
            <q-btn
              :label="$t('Cancel')"
              v-close-popup
              flat
            />
            <q-btn
              type="submit"
              :label="$t('Search')"
              v-close-popup
              color="default"
              unelevated
            />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <q-dialog
      v-model="isFormEntryVisible"
      :persistent="!isFormEntryReadonly"
    >
      <form-entry
        :entry="formEntry"
        :readonly="isFormEntryReadonly"
        :loading="isLoading"
        @success="onFormEntrySuccess"
        @cancel="onFormEntryCancel"
      />
    </q-dialog>


    <q-dialog
      v-model="isFormEntryVisible"
      :persistent="!isFormEntryReadonly"
    >
      <form-entry
        :entry="formEntry"
        :readonly="isFormEntryReadonly"
        :loading="isLoading"
        @success="onFormEntrySuccess"
        @cancel="onFormEntryCancel"
      />
    </q-dialog>

    <q-dialog
      v-model="isFormPasswordVisible"
      persistent
    >
      <form-password
        :entry="formPassword"
        :loading="isLoading"
        @success="onFormPasswordSuccess"
        @cancel="onFormPasswordCancel"
      />
    </q-dialog>

    <q-dialog
      v-model="isAdapterPrinterVisible"
      persistent
    >
      <adapter-printer
        :visible="isAdapterPrinterVisible"
        :entries="selectedEntries"
        :loading="isLoading"
        @success="onAdapterPrinterSuccess"
        @cancel="onAdapterPrinterCancel"
      />
    </q-dialog>

    <a v-if="exportUrl" :href="exportUrl" download ref="exportHiddenLink" style="display: none;"> </a>


    <q-dialog
      v-model="isFormImportVisible"
      persistent
    >
      <form-import
        :visible="isFormImportVisible"
        :entries="selectedEntries"
        :loading="isLoading"
        @success="onFormImportSuccess"
        @cancel="onFormImportCancel"
      />
    </q-dialog>

    <!-- <dialog-import
      v-model="isFormImportVisible"
      :status="currentImportStatus"
      :import-path="currentImportPath"
      :import-type="$constant.import_type.Invoice"
      :template-url="$q.lang.isoName === 'id' ? '/templates/template-invoice.xlsx' : '/templates/invoice-template.xlsx'"
      :processing-page="currentImportProcessingPage"
      :has-error="currentImportHasError"
      @update:status="onFormImportUpdateStatus"
      @success="onRequest"
    /> -->
  </q-page>
</template>

<script>
import { date } from 'quasar'
import FormEntry from './Invoice/FormEntry'
import FormPassword from './Invoice/FormPassword'
import FormImport from './Invoice/FormImport'
import InvoiceStatusChip from './Invoice/InvoiceStatusChip'
import AdapterPrinter from './Invoice/AdapterPrinter'
import datatableMixins from 'src/mixins/datatable'
import { mapGetters } from 'vuex'

export default {
  name: 'PageInvoice',
  components: {
    FormEntry,
    FormPassword,
    InvoiceStatusChip,
    AdapterPrinter,
    FormImport
  },
  meta() {
    return {
      title: this.$t('Invoices') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Invoices') }
    ]
  },
  mixins: [datatableMixins],
  data() {
    const search = {
      fuzzy: {
        value: null
      },
      applicable_month: {
        value: new Date(),
        formattedValue: null
      },
      status: {
        value: null
      }
    }

    return {
      entries: [],
      selectedEntries: [],
      columns: [
        {
          name: 'id',
          field: 'id',
          label: '#',
          realLabel: this.$t('ID'),
          align: 'left',
          style: 'max-width: 25px',
          headerClasses: 'table-header-id',
          classes: 'table-cell-id',
          sortable: true
        },
        {
          name: 'customer_name',
          field: 'customer_name',
          label: this.$t('Customer'),
          headerClasses: 'table-header-customer_name',
          classes: 'table-cell-customer_name',
          align: 'left',
          sortable: true
        },
        {
          name: 'invoice_no',
          field: 'invoice_no',
          label: this.$t('Invoice No'),
          headerClasses: 'table-header-invoice_no',
          classes: 'table-cell-invoice_no',
          align: 'left',
          sortable: true
        },
        {
          name: 'receipt_no',
          field: 'receipt_no',
          label: this.$t('Receipt No'),
          headerClasses: 'table-header-receipt_no',
          classes: 'table-cell-receipt_no',
          align: 'left',
          sortable: true
        },
        {
          name: 'status',
          field: 'status',
          label: this.$t('Status'),
          headerClasses: 'table-header-status',
          classes: 'table-cell-status',
          align: 'left',
          sortable: true
        },
        {
          name: 'ppn_total',
          field: 'ppn_total',
          format: val => this.$utils.currency(val, {
            decimal: '.',
            thousand: ',',
          }) || 0,
          label: this.$t('VAT'),
          headerClasses: 'table-header-ppn_total',
          classes: 'table-cell-ppn_total',
          align: 'left',
          sortable: true
        },
        {
          name: 'stamp_duty',
          field: 'stamp_duty',
          format: val => this.$utils.currency(val, {
            decimal: '.',
            thousand: ',',
          }) || 0,
          label: this.$t('Stamp Duty'),
          headerClasses: 'table-header-stamp_duty',
          classes: 'table-cell-stamp_duty',
          align: 'left',
          sortable: true
        },
        {
          name: 'sub_total',
          field: 'sub_total',
          format: val => this.$utils.currency(val, {
            decimal: '.',
            thousand: ',',
          }),
          label: this.$t('Sub Total'),
          headerClasses: 'table-header-sub_total',
          classes: 'table-cell-sub_total',
          align: 'left',
          sortable: true
        },
        {
          name: 'total',
          field: 'total',
          format: val => this.$utils.currency(val, {
            decimal: '.',
            thousand: ',',
          }),
          label: this.$t('Total'),
          headerClasses: 'table-header-total',
          classes: 'table-cell-total',
          align: 'left',
          sortable: true
        },
        {
          name: 'due_at',
          field: 'due_at',
          label: this.$t('Term of Payment'),
          align: 'left',
          headerClasses: 'table-header-due_at',
          classes: 'table-cell-due_at',
          format: val => date.formatDate(val, 'DD MMM YYYY'),
          sortable: true
        },
        {
          name: 'published_at',
          field: 'published_at',
          label: this.$t('Published At'),
          align: 'left',
          headerClasses: 'table-header-published_at',
          classes: 'table-cell-published_at',
          format: val => date.formatDate(val, 'DD MMM YYYY'),
          sortable: true
        },
        {
          name: 'created_at',
          field: 'created_at',
          label: this.$t('Created At'),
          align: 'left',
          headerClasses: 'table-header-created_at',
          classes: 'table-cell-created_at',
          format: val => date.formatDate(val, 'DD MMM YYYY HH:mm'),
          sortable: true
        },
        {
          name: 'action',
          label: '',
          align: 'right',
          headerClasses: 'table-header-action',
          classes: 'table-cell-action',
          required: true
        }
      ],
      visibleColumns: [
        'id',
        'customer_name',
        'invoice_no',
        // 'receipt_no',
        'status',
        'ppn_total',
        'stamp_duty',
        'sub_total',
        'total',
        'due_at',
        'published_at',
        'created_at'
      ],
      isLoading: false,
      isExporting: false,
      isFormImportVisible: false,
      isAdvancedSearchVisible: false,
      isFormEntryVisible: false,
      isFormEntryReadonly: false,
      isFormPasswordVisible: false,
      isAdapterPrinterVisible: false,
      formEntry: {},
      formPassword: {},
      pagination: {
        sortBy: 'id',
        descending: false,
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0
      },
      search,
      advancedSearch: search,
      exportUrl: null,
      onApplicableMonthChangeDebounced: this.$utils.debounce(this.onRequest, 500)
    }
  },
  computed: {
    ...mapGetters({
      'currentImportStatus': 'imports/status',
      'currentImportPath': 'imports/importPath',
      'currentImportProcessingPage': 'imports/processingPage',
      'currentImportHasError': 'imports/hasError',
    })
  },
  watch: {
    'search.applicable_month.value': {
      immediate: true,
      handler(n) {
        if (n) {
          this.search.applicable_month.formattedValue = n ? date.formatDate(n, 'MMMM YYYY') : null
        }
      }
    },
    'advancedSearch.applicable_month.value': {
      immediate: true,
      handler(n) {
        if (n) {
          this.advancedSearch.applicable_month.formattedValue = n ? date.formatDate(n, 'MMMM YYYY') : null
        }
      }
    },
  },
  beforeRouteLeave(to, from, next) {
    if (!to.name.includes('invoices')) {
      this.$store.dispatch('app/storeSearches', { id: this.$options.name, payload: null })
    }
    next()
  },
  async mounted() {
    try {
      let storedSearches = this.$store.getters['app/storedSearches']
      if (storedSearches && storedSearches[this.$options.name]) {
        storedSearches = JSON.parse(storedSearches[this.$options.name])

        if (storedSearches.applicable_month && storedSearches.applicable_month.value) {
          storedSearches.applicable_month.value = new Date(storedSearches.applicable_month.value)
        }

        this.search = storedSearches
      }

      await this.$nextTick()
    } catch (err) {}
    await this.onRequest()
    await this.$nextTick()
    // if (!this.$store.getters['tourGuide/finishedGroups'].invoices) {
    //   this.$tourGuide.open('invoices')
    // }
  },
  methods: {
    async onRequest(props) {
      if (this.isLoading) {
        return
      }

      if (!props) {
        props = { pagination: this.pagination }
      }

      this.isLoading = true

      const params = {
        table_pagination: { ...(props.pagination || {}) },
        table_search: this.$utils.merge({}, { ...this.search })
      }

      this.$store.dispatch('app/storeSearches', { id: this.$options.name, payload: params.table_search })

      if (params.table_search.applicable_month.value) {
        params.table_search.applicable_month = {
          value: date.formatDate(params.table_search.applicable_month.value, 'YYYY-MM-15')
        }
      }

      try {
        const { data } = await this.$api.get('/v1/invoices', { params })

        if (data.status === 'success') {
          this.entries = data.data.invoices
          this.pagination = { ...this.pagination, ...data.pagination }
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    onAdvancedSearch() {
      this.advancedSearch = JSON.parse(JSON.stringify(this.search))

      this.isAdvancedSearchVisible = true
    },
    onAdvancedSearchSubmit() {
      this.search = this.$utils.merge({}, this.advancedSearch)

      this.onRequest()

      this.isAdvancedSearchVisible = false
    },
    onFormEntryCreate() {
      this.$router.push('/invoices/create')

      this.formEntry = {}
      this.isFormEntryReadonly = false
      this.isFormEntryVisible = false
    },
    onFormEntrySuccess() {
      this.isFormEntryReadonly = false
      this.isFormEntryVisible = false

      this.onRequest()
    },
    onFormEntryCancel() {
      this.isFormEntryReadonly = false
      this.isFormEntryVisible = false
    },
    onFormPasswordSuccess() {
      this.isFormPasswordVisible = false
    },
    onFormPasswordCancel() {
      this.isFormPasswordVisible = false
    },
    onAdapterPrinterSuccess() {
      this.isAdapterPrinterVisible = false
    },
    onAdapterPrinterCancel() {
      this.isAdapterPrinterVisible = false
    },
    onView(row) {
      this.$router.push(`/invoices/${row.id}`)
      this.formEntry = { ...row }
      this.isFormEntryReadonly = true
      this.isFormEntryVisible = false
    },
    onEdit(row) {
      this.formEntry = { ...row }
      this.isFormEntryReadonly = false
      this.isFormEntryVisible = true
    },
    onEditPassword(row) {
      this.formPassword = { ...row }
      this.isFormPasswordVisible = true
    },

    onSelection({ rows, added, evt }) {
      // ignore selection change from header of not from a direct click event
      if (rows.length !== 1 || evt === void 0) {
        return
      }

      const { oldSelectedRow } = this
      const [newSelectedRow] = rows
      const { ctrlKey, shiftKey } = evt

      if (shiftKey !== true) {
        this.oldSelectedRow = newSelectedRow
      }

      this.$nextTick(() => {
        if (shiftKey === true) {
          const tableRows = this.$refs.table.filteredSortedRows
          let firstIndex = tableRows.indexOf(oldSelectedRow)
          let lastIndex = tableRows.indexOf(newSelectedRow)

          if (firstIndex < 0) {
            firstIndex = 0
          }

          if (firstIndex > lastIndex) {
            [firstIndex, lastIndex] = [lastIndex, firstIndex]
          }

          const rangeRows = tableRows.slice(firstIndex, lastIndex + 1)

          this.selectedEntries = added === true
            ? this.selectedEntries.concat(rangeRows.filter(row => this.selectedEntries.includes(row) === false))
            : this.selectedEntries.filter(row => rangeRows.includes(row) === false)
        }
      })
    },
    onDelete(row) {
      if (this.isLoading) {
        return;
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('invoice') }),
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
          let { data } = await this.$api.delete(`/v1/invoices/${row.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Invoice') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('invoice') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
      })
    },
    onBulkDelete() {
      if (this.isLoading) {
        return;
      }

      const selected = this.selectedEntries

      if (!selected.length) {
        return
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t(`Apakah Anda yakin ingin menghapus ${selected.length} data yang dipilih?`),
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
          let { data } = await this.$api.post(`/v1/invoices/delete`, {
            ids: selected.map(v => v.id)
          });

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t(`${selected.length} invoice berhasil dihapus`) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('invoice') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
      })
    },
    async onFormEntryExport(props) {

      if (this.isLoading || this.isExporting) {
        return false;
      }

      if (!props) {
        props = { pagination: this.pagination }
      }

      this.isLoading = true
      this.isExporting = true

      const params = {
        table_pagination: { ...(props.pagination || {}) },
        table_search: { ...this.search },
        ext: props.ext || 'xlsx'
      }

      // if (params.table_search.created_at_range.value && params.table_search.created_at_range.value.to) {
      //   params.table_search = {
      //     ...params.table_search,
      //     created_at_range: {
      //       value: this.search.created_at_range.formattedValue
      //     }
      //   }
      // }

      // if (params.table_search.applicable_month.value) {
      //   params.table_search.applicable_month = {
      //     value: date.formatDate(params.table_search.applicable_month.value, 'YYYY-MM-15')
      //   }
      // }



      // if (params.table_search.applicable_month.value) {
      //   params.table_search.applicable_month = {
      //     value: date.formatDate(params.table_search.applicable_month.value, 'YYYY-MM-15')
      //   }
      // }


      const requestExport = async (i = 0) => {
        const { data } = await this.$api.post('/v1/invoices/export', this.$api.defaults.paramsTransformer(params))

        if (data.status === 'success') {
          this.exportUrl = data.data.url
          console.log(this.exportUrl)

          await this.$utils.delay(100)

          this.$refs.exportHiddenLink.click()

          await this.$utils.delay(3000)
        } else if (data.data.wait) {
          await this.$utils.delay(15000)

          return requestExport(i + 1)
        }
      }

      try {
        await requestExport()
      } catch (err) {
        this.$q.notify(err)
      }


      // const { access_token } = await this.$store.dispatch('auth/getToken')

      // params.api_token = access_token

      // this.$cookies.set('api_token', access_token, { expires: 1 })
      // this.exportUrl = this.$api.getUri({
      //   url: '/v1/export/tickets',
      //   params: this.$api.defaults.paramsTransformer(params),
      // })

      // console.log(this.exportUrl)

      // await this.$utils.delay(100)

      // this.$refs.exportHiddenLink.click()

      // await this.$utils.delay(2000)

      this.isLoading = false;
      this.isExporting = false;

      this.exportUrl = null
    },
    onFormImport() {
      this.isFormEntryVisible = false
      this.isFormImportVisible = true
    },
    onFormImportSuccess() {
      this.onRequest();
      this.isFormEntryVisible = false
      this.isFormImportVisible = false
    },
    onFormImportCancel() {
      this.isFormEntryVisible = false
      this.isFormImportVisible = false
    },
    onFormImportUpdateStatus({ status, importPath, processingPage, hasError }) {
      this.$store.dispatch('imports/setStatus', status)
      this.$store.dispatch('imports/setImportPath', importPath)

      if (processingPage) {
        this.$store.dispatch('imports/setProcessingPage', processingPage)
      }

      if (typeof hasError !== 'undefined') {
        this.$store.dispatch('imports/setHasError', hasError)
      }

      this.$store.dispatch('imports/sync')
    },
  }
}
</script>

<style lang="scss">
.page-invoices {
  padding-bottom: 3rem;

  .page-body > .q-card > .q-toolbar {
    margin-bottom: 0.5rem !important;
  }

  .q-table__top {
    padding-top: 0;
    padding-bottom: 0;
    font-size: 13px;

    .visibility-hidden {
      opacity: 0;
      visibility: hidden;
    }

    .q-btn {
      &.disabled {
        opacity: 0.3 !important;
      }
    }
  }
}
</style>
