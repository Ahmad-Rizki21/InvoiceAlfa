<template>
  <q-page class="page-reports">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        {{ $t('Report') }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('export.report')"
        flat
        round
        dense
        icon="system_update_alt"
        :loading="isExporting"
      />
    </portal>

    <div class="page-header">
      <q-toolbar-title>{{ $t('Report') }}</q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('export.report')"
        color="secondary"
        class="q-ml-sm q-btn-lg btn-page-reports-export"
        :loading="isExporting"
        icon="system_update_alt"
        @click="onFormEntryExport"
      >
        <span class="q-ml-sm">{{ $t('Export') }}</span>
      </q-btn>
    </div>

    <div class="page-body">
      <q-card>
        <q-toolbar>
          <div class="col col-md-9 col-lg-8">
            <div class="row q-col-gutter-md">
              <div class="col col-md-4 col-lg-3">
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

              <div class="col col-md-4 col-lg-3">
                <autocomplete-customer
                  v-model="search.customer_id.value"
                  :label="$t('Search {entity}', { entity: $t('customer') })"
                  stack-label
                  dense
                  class="input-quick-search-customer_id"
                  clearable
                  clear-icon="close"
                  @input="onRequest()"
                />
              </div>

              <!-- <div class="col col-md-4 col-lg-3">
                <q-input
                  v-model="search.fuzzy.value"
                  :label="$t('Search {entity}', { entity: $t('location or terminal name') })"
                  stack-label
                  :debounce="300"
                  dense
                  clearable
                  clear-icon="close"
                  @input="onRequest()"
                />
              </div> -->
            </div>
          </div>

          <div class="col col-md-3 col-lg-2 q-ml-auto row">
            <q-btn
              v-if="false"
              unelevated
              color="default"
              size="sm"
              icon="search"
              class="q-ml-auto"
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
              class="q-ml-auto btn-datatable-view-columns"
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
          :data="entries"
          :columns="columns"
          :visible-columns="visibleColumns"
          :loading="isLoading"
          :pagination.sync="pagination"
          binary-state-sort
          row-key="id"
          @request="onRequest"
        >
          <template #body-cell-id="{ rowIndex }">
            <q-td>{{ rowIndex + 1 }}</q-td>
          </template>

          <template #body-cell-address="{ row }">
            <q-td class="overflow-ellipsis" style="max-width: 10rem;">
              <span :title="row.address">{{ row.address }}</span>
            </q-td>
          </template>

          <template #body-cell-status="{ row }">
            <q-td class="text-center">
              <q-icon :name="row.status ? 'check' : 'close'" class="q-mr-lg" />
            </q-td>
          </template>
          <template #body-cell-action="{ row }">
            <q-td class="text-right">
              <q-btn
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="visibility"
                @click.prevent="onView(row)"
              >
                <q-tooltip>
                  {{ $t('View') }}
                </q-tooltip>
              </q-btn>
              <q-btn
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="edit"
                class="q-ml-xs"
                @click.prevent="onEdit(row)"
              >
                <q-tooltip>
                  {{ $t('Edit') }}
                </q-tooltip>
              </q-btn>
              <q-btn
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="delete"
                class="q-ml-xs"
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
              :label="$t('Search {entity}', { entity: $t('name or location') })"
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

    <a v-if="exportUrl" :href="exportUrl" download ref="exportHiddenLink" style="display: none;"> </a>
  </q-page>
</template>

<script>
import { date } from 'quasar'
import FormEntry from './Report/FormEntry'
import datatableMixins from 'src/mixins/datatable'

export default {
  name: 'PageReport',
  components: {
    FormEntry
  },
  meta() {
    return {
      title: this.$t('Report') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Report') }
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
      customer_id: {
        value: null
      }
    }

    return {
      entries: [],
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
          name: 'customer',
          field: row => row.customer ? row.customer.name : null,
          format: v => v || '-',
          label: this.$t('Customer'),
          align: 'left',
          sortable: false
        },
        {
          name: 'address',
          field: 'address',
          label: this.$t('Address'),
          align: 'left',
          style: 'max-width: 15em',
          headerStyle: 'max-width: 15em',
          headerClasses: 'table-header-address',
          classes: 'table-cell-address',
          sortable: true
        },
        {
          name: 'terminal_name',
          field: 'terminal_name',
          label: this.$t('Terminal Name'),
          align: 'left',
          style: 'max-width: 15em',
          headerClasses: 'table-header-terminal_name',
          classes: 'table-cell-terminal_name',
          sortable: true
        },
        {
          name: 'online_at',
          field: 'online_at',
          label: this.$t('Online At'),
          align: 'left',
          format: v => v ? date.formatDate(v, 'DD/MM/YYYY') : '',
          style: 'max-width: 100px',
          sortable: false
        },
        {
          name: 'sla',
          field: row => row.customer ? row.customer.sla : null,
          label: this.$t('SLA'),
          align: 'left',
          format: v => v ? `${v}%` : '',
          style: 'max-width: 100px',
          sortable: false
        },
        {
          name: 'mttr_hour',
          field: 'mttr_hour',
          label: this.$t('MTTR (Hour)'),
          align: 'left',
          format: v => this.$utils.formatSecondsToTime(v, false, false),
          sortable: false
        },
        {
          name: 'total_time',
          field: 'total_time',
          label: this.$t('Total Time (Month)'),
          align: 'left',
          format: v => this.$utils.formatSecondsToTime(v, false, false),
          sortable: true
        },
        {
          name: 'down_time_customer',
          field: 'down_time_customer',
          label: this.$t('Down Time Customer'),
          align: 'left',
          format: v => this.$utils.formatSecondsToTime(v, false, false),
          sortable: false
        },
        {
          name: 'down_time_provider',
          field: 'down_time_provider',
          label: this.$t('Down Time AJNUSA'),
          align: 'left',
          format: v => this.$utils.formatSecondsToTime(v, false, false),
          sortable: false
        },
        {
          name: 'begin_clock',
          field: 'begin_clock',
          label: this.$t('Begin Clock'),
          align: 'left',
          format: v => this.$utils.formatSecondsToTime(v, false, false),
          sortable: false
        },
        {
          name: 'pending_clock',
          field: 'pending_clock',
          label: this.$t('Stop Clock'),
          align: 'left',
          format: v => this.$utils.formatSecondsToTime(v, false, false),
          sortable: false
        },
        {
          name: 'mttr',
          field: 'mttr',
          format: v => this.$utils.formatSecondsToTime(v, false, false),
          label: this.$t('MTTR'),
          align: 'left',
          sortable: false
        },
        {
          name: 'total_tickets',
          field: 'total_tickets',
          label: this.$t('Frequency'),
          align: 'left',
          style: 'max-width: 100px',
          sortable: false
        },
        {
          name: 'availability',
          field: 'availability',
          format: v => v ? this.$utils.formatFloatDecimal(v) + '%' : '',
          label: this.$t('Availability'),
          align: 'left',
          style: 'max-width: 100px',
          sortable: false
        },
        // {
        //   name: 'action',
        //   label: '',
        //   align: 'right',
        //   required: true
        // }
      ],
      visibleColumns: [
        'id',
        'customer',
        'address',
        'terminal_name',
        'online_at',
        'mttr_hour',
        'down_time_customer',
        'down_time_provider',
        'mttr',
        'total_time',
        'begin_clock',
        'pending_clock',
        'sla',
        'availability',
        'total_tickets',
      ],
      isLoading: false,
      isExporting: false,
      isAdvancedSearchVisible: false,
      isFormEntryVisible: false,
      isFormEntryReadonly: false,
      formEntry: {},
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
  watch: {
    'search.applicable_month.value': {
      immediate: true,
      handler(n) {
        if (n) {
          this.search.applicable_month.formattedValue = n ? date.formatDate(n, 'MMMM YYYY') : null
        }
      }
    }
  },
  async mounted() {
    await this.onRequest()

    await this.$nextTick()

    // if (!this.$store.getters['tourGuide/finishedGroups'].reports) {
    //   this.$tourGuide.open('reports')
    // }
  },
  methods: {
    onApplicableMonthChange() {
      this.$nextTick(() => {
        this.onRequest()
      })
    },
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
        table_search: { ...this.search }
      }

      if (params.table_search.applicable_month.value) {
        params.table_search.applicable_month = {
          value: date.formatDate(params.table_search.applicable_month.value, 'YYYY-MM-15')
        }
      }

      try {
        const { data } = await this.$api.get('/v1/reports', { params })

        if (data.status === 'success') {
          this.entries = data.data.reports
          this.pagination = { ...this.pagination, ...data.pagination }
        }
      } catch (err) {
        console.error(err)
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
      this.formEntry = {}
      this.isFormEntryReadonly = false
      this.isFormEntryVisible = true
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
    onView(row) {
      this.formEntry = { ...row }
      this.isFormEntryReadonly = true
      this.isFormEntryVisible = true
    },
    onEdit(row) {
      this.formEntry = { ...row }
      this.isFormEntryReadonly = false
      this.isFormEntryVisible = true
    },
    onDelete(row) {
      if (this.isLoading) {
        return;
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('report') }),
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
          let { data } = await this.$api.delete(`/v1/reports/${row.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Report') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('report') })
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

      if (params.table_search.applicable_month.value) {
        params.table_search.applicable_month = {
          value: date.formatDate(params.table_search.applicable_month.value, 'YYYY-MM-15')
        }
      }

      const { access_token } = await this.$store.dispatch('auth/getToken')

      params.api_token = access_token

      this.$cookies.set('api_token', access_token, { expires: 1 })

      this.exportUrl = this.$api.getUri({
        url: '/v1/reports/export',
        params: this.$api.defaults.paramsTransformer(params),
      })

      console.log(this.exportUrl)

      await this.$utils.delay(100)

      this.$refs.exportHiddenLink.click()

      await this.$utils.delay(2000)

      this.isLoading = false;
      this.isExporting = false;

      this.exportUrl = null
    }
  }
}
</script>

<style lang="scss">
.page-reports {
  padding-bottom: 3rem;
}
</style>
