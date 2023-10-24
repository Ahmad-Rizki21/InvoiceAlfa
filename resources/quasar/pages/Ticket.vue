<template>
  <q-page class="page-tickets">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        {{ $t('Ticket') }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('export.ticket')"
        flat
        round
        dense
        icon="system_update_alt"
        :loading="isExporting"
      />

      <q-btn
        v-if="$auth.can('create.ticket')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-toolbar-title>{{ $t('Tickets') }}</q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.ticket')"
        color="secondary"
        class="q-ml-sm q-btn-lg btn-page-tickets-export"
        :loading="isExporting"
        icon="system_update_alt"
        @click="onFormEntryExport"
      >
        <span class="q-ml-sm">{{ $t('Export') }}</span>
      </q-btn>

      <q-btn
        v-if="$auth.can('create.ticket')"
        color="primary"
        class="q-ml-sm q-btn-lg btn-page-tickets-create"
        icon="add"
        to="/tickets/create"
      >
        <span class="q-ml-xs">{{ $t('New {entity}', { entity: $t('Ticket') }) }}</span>
      </q-btn>
    </div>

    <div class="page-body">
      <q-card>
        <q-toolbar>
          <div class="col col-md-9 col-lg-8">
            <div class="row q-col-gutter-md quick-search-datatable">
              <div class="col col-md-3 col-lg-3">
                <autocomplete-customer
                  v-model="search.customer_id.value"
                  :label="$t('Search {entity}', { entity: $t('customer') })"
                  stack-label
                  dense
                  class="input-search-datatable-customer"
                  clearable
                  clear-icon="close"
                  @input="onRequest()"
                />
              </div>
              <div class="col col-md-3 col-lg-3">
                <q-input
                  v-model="search.created_at_range.formattedValue"
                  :label="$t('Period')"
                  autocomplete="off"
                  stack-label
                  dense
                  class="keep-styling input-search-datatable-created-at-range"
                  clearable
                  @keypress="$globalListeners.onKeypressDisabled"
                  @keyup="$globalListeners.onKeyupDisabled"
                  @clear="onCreatedAtRangeClear"
                >
                  <q-menu>
                    <q-date
                      v-model="search.created_at_range.value"
                      mask="DD/MM/YYYY"
                      :emit-immediately="false"
                      color="primary"
                      range
                      @input="onCreatedAtRangeChangeDebounced"
                    >
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-menu>
                </q-input>
              </div>

              <!-- <div class="col col-md-3 col-lg-3">
                <autocomplete-remote-location
                  v-model="search.remote_location_id.value"
                  :label="$t('Search {entity}', { entity: $t('remote') })"
                  stack-label
                  :debounce="300"
                  dense
                  class="input-search-datatable-remote-location"
                  clearable
                  clear-icon="close"
                  :customer-id="search.customer_id.value"
                  @input="onRequest()"
                />
              </div> -->
              <div class="col col-md-3 col-lg-3">
                <q-input
                  v-model="search.fuzzy.value"
                  :label="$t('Search {entity}', { entity: $t('no or title') })"
                  stack-label
                  :debounce="300"
                  dense
                  class="input-search-datatable-fuzzy"
                  clearable
                  clear-icon="close"
                  :customer-id="search.customer_id.value"
                  @input="onRequest()"
                />
              </div>
              <div class="col col-md-3 col-lg-2">
                <select-ticket-status
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
                <div class="row no-wrap q-pa-md">
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
          <template #body-cell-no="{ row }">
            <q-td>
              <router-link :to="`/tickets/${row.id}`">{{ row.no }}</router-link>
            </q-td>
          </template>
          <template #body-cell-open_clock="{ row }">
            <q-td class="text-right">
              <span>{{ $utils.formatSecondsToTime(row.open_clock, false, true) }}</span>
              <template v-if="row.latest_ticket_timer && row.latest_ticket_timer.status == $constant.ticket_timer.TIMER_OPEN">+</template>
              <template v-else>&nbsp;&nbsp;</template>
            </q-td>
          </template>
          <template #body-cell-pending_clock="{ row }">
            <q-td class="text-right">
              <span>{{ $utils.formatSecondsToTime(row.pending_clock, false, true) }}</span>
              <template v-if="row.latest_ticket_timer && row.latest_ticket_timer.status == $constant.ticket_timer.TIMER_PENDING">+</template>
              <template v-else>&nbsp;&nbsp;</template>
            </q-td>
          </template>
          <template #body-cell-start_clock="{ row }">
            <q-td class="text-right">
              <span>{{ $utils.formatSecondsToTime(row.start_clock, false, true) }}</span>
              <template v-if="row.latest_ticket_timer && row.latest_ticket_timer.status == $constant.ticket_timer.TIMER_START && row.status != $constant.ticket.STATUS_CLOSED">+</template>
              <template v-else>&nbsp;&nbsp;</template>
            </q-td>
          </template>
          <template #body-cell-clock_status="{ row }">
            <q-td>
              <ticket-timer-status-chip :ticket="row" :ticket-timer="row.latest_ticket_timer" dense />
            </q-td>
          </template>
          <template #body-cell-status="{ row }">
            <q-td>
              <ticket-status-chip :ticket="row" dense />
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
                class="btn-datatable-view-ticket"
                @click.prevent="onView(row)"
              >
                <q-tooltip>
                  {{ $t('View') }}
                </q-tooltip>
              </q-btn>
              <!-- <q-btn
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
              </q-btn> -->
              <q-btn
                v-if="$auth.can('delete.ticket')"
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="delete"
                class="q-ml-xs btn-datatable-delete-ticket"
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
          <q-card-section class="q-mb-md q-gutter-md">
            <autocomplete-customer
              v-model="advancedSearch.customer_id.value"
              :label="$t('Search {entity}', { entity: $t('customer') })"
              outlined
              clearable
              dense
            />
            <autocomplete-remote-location
              v-model="advancedSearch.remote_location_id.value"
              :label="$t('Search {entity}', { entity: $t('remote') })"
              outlined
              clearable
              :customer-id="advancedSearch.customer_id.value"
              dense
            />
            <q-input
              v-model="advancedSearch.created_at_range.formattedValue"
              :label="$t('Period')"
              autocomplete="off"
              outlined
              clearable
              dense
              class="keep-styling"
              @keypress="$globalListeners.onKeypressDisabled"
              @keyup="$globalListeners.onKeyupDisabled"
            >
              <q-menu>
                <q-date
                  v-model="advancedSearch.created_at_range.value"
                  mask="DD/MM/YYYY"
                  :emit-immediately="false"
                  color="primary"
                  range
                >
                  <div class="row items-center justify-end">
                    <q-btn v-close-popup label="Close" color="primary" flat />
                  </div>
                </q-date>
              </q-menu>
            </q-input>

            <q-input
              v-model="advancedSearch.no.value"
              :label="$t('Search {entity}', { entity: $t('no') })"
              outlined
              clearable
              dense
            />
            <q-input
              v-model="advancedSearch.title.value"
              :label="$t('Search {entity}', { entity: $t('title') })"
              outlined
              clearable
              dense
            />
            <select-ticket-status
              v-model="advancedSearch.status.value"
              :label="$t('Search {entity}', { entity: $t('status') })"
              outlined
              clearable
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
import FormEntry from './Ticket/FormEntry'
import datatableMixins from 'src/mixins/datatable'
import TicketStatusChip from './Ticket/TicketStatusChip'
import TicketTimerStatusChip from './Ticket/TicketTimerStatusChip'

export default {
  name: 'PageTickets',
  components: {
    FormEntry,
    TicketStatusChip,
    TicketTimerStatusChip
  },
  meta() {
    return {
      title: this.$t('Tickets') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Tickets') }
    ]
  },
  mixins: [datatableMixins],
  data() {
    const search = {
      fuzzy: {
        value: null
      },
      customer_id: {
        value: null
      },
      title: {
        value: null
      },
      remote_location_id: {
        value: null
      },
      status: {
        value: null
      },
      no: {
        value: null
      },
      created_at_range: {
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
          headerClasses: 'table-header-id',
          classes: 'table-cell-id',
          sortable: true
        },
        {
          name: 'no',
          field: 'no',
          label: this.$t('No'),
          align: 'left',
          headerClasses: 'table-header-no',
          classes: 'table-cell-no',
          sortable: true
        },
        {
          name: 'title',
          field: 'title',
          label: this.$t('Title'),
          align: 'left',
          sortable: true
        },
        {
          name: 'customer',
          field: row => row.customer ? row.customer : null,
          format: v => v ? v.name : '-',
          label: this.$t('Customer'),
          align: 'left',
          headerClasses: 'table-header-customer',
          classes: 'table-cell-customer',
          sortable: false
        },
        {
          name: 'terminal_name',
          field: row => row.remote_location ? row.remote_location.terminal_name : null,
          format: v => v || '-',
          label: this.$t('Terminal Name'),
          align: 'left',
          style: 'max-width: 15em',
          sortable: false
        },
        // {
        //   name: 'content',
        //   field: 'content',
        //   label: this.$t('Content'),
        //   align: 'left',
        //   style: 'max-width: 100px',
        //   sortable: true
        // },
        {
          name: 'clock_status',
          field: 'clock_status',
          label: this.$t('Clock Status'),
          align: 'left',
          sortable: false
        },
        {
          name: 'status',
          field: 'status',
          format: v => this.$t(v == this.$constant.ticket.STATUS_CLOSED ? 'Closed' : 'Open'),
          label: this.$t('Status'),
          align: 'left',
          sortable: true
        },
        {
          name: 'open_clock',
          field: 'open_clock',
          label: this.$t('Open Clock'),
          align: 'right',
          headerStyle: 'max-width: 7.5em',
          sortable: false
        },
        {
          name: 'pending_clock',
          field: 'pending_clock',
          format: v => this.$utils.formatSecondsToTime(v, false, true),
          label: this.$t('Pending Clock'),
          align: 'right',
          headerStyle: 'max-width: 8em',
          sortable: false
        },
        {
          name: 'start_clock',
          field: 'start_clock',
          format: v => this.$utils.formatSecondsToTime(v, false, true),
          label: this.$t('Start Clock'),
          align: 'right',
          headerStyle: 'max-width: 8em',
          sortable: false
        },
        {
          name: 'down_time_customer',
          field: 'down_time_customer',
          format: v => this.$utils.formatSecondsToTime(v, false, true),
          label: this.$t('Down Time Customer'),
          align: 'right',
          sortable: false
        },
        {
          name: 'down_time_provider',
          field: 'down_time_provider',
          format: v => this.$utils.formatSecondsToTime(v, false, true),
          label: this.$t('Down Time AJNUSA'),
          align: 'right',
          sortable: false
        },
        {
          name: 'created_at',
          field: 'created_at',
          format: val => date.formatDate(val, 'DD MMM YYYY HH:mm'),
          label: this.$t('Created At'),
          align: 'left',
          sortable: true
        },
        {
          name: 'created_by',
          field: 'created_by',
          format: v => v ? v.name : '',
          label: this.$t('Created By'),
          align: 'left',
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
        'no',
        // 'title',
        'customer',
        'terminal_name',
        // 'content',
        'open_clock',
        'pending_clock',
        'start_clock',
        'clock_status',
        'status',
        // 'down_time_customer',
        // 'down_time_provider',
        'created_at',
        'created_by'
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
      onCreatedAtRangeChangeDebounced: this.$utils.debounce(this.onRequest, 500)
    }
  },
  watch: {
    'search.fuzzy.value'(n, o) {
      if (n !== o && n) {
        this.search.no.value = null
        this.search.title.value = null

        this.onRequest()
      }
    },
    'search.created_at_range.value': {
      immediate: true,
      handler(n) {
        if (n) {
          if (n.to && n.from) {
            this.search.created_at_range.formattedValue = `${n.from} - ${n.to}`
          } else {
            this.search.created_at_range.formattedValue = n
          }
        }
      }
    },
    'search.created_at_range.formattedValue': {
      immediate: true,
      handler(n, o) {
        if (n !== o && !n) {
          this.search.created_at_range.value = null
        }
      }
    },
    'advancedSearch.created_at_range.value': {
      immediate: true,
      handler(n) {
        if (n) {
          if (n.to && n.from) {
            this.advancedSearch.created_at_range.formattedValue = `${n.from} - ${n.to}`
          } else {
            this.advancedSearch.created_at_range.formattedValue = n
          }
        }
      }
    },
    'advancedSearch.created_at_range.formattedValue': {
      immediate: true,
      handler(n, o) {
        if (n !== o && !n) {
          this.advancedSearch.created_at_range.value = null
        }
      }
    },
  },
  async mounted() {
    await this.onRequest()

    this.$nextTick(() => {
      if (!this.$store.getters['tourGuide/finishedGroups'].tickets) {
        this.$tourGuide.open('tickets')
      }
    })
  },
  methods: {
    onCreatedAtRangeClear() {
      this.$nextTick(() => {
        this.search.created_at_range = {
          value: null
        }

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
        table_search: JSON.parse(JSON.stringify({ ...this.search })),
        includes: 'remoteLocation|customer|createdBy|allClock|latestTicketTimer'
      }

      if (params.table_search.created_at_range.value && params.table_search.created_at_range.value.to) {
        params.table_search = {
          ...params.table_search,
          created_at_range: {
            value: this.search.created_at_range.formattedValue
          }
        }
      }

      try {
        const { data } = await this.$api.get('/v1/tickets', { params })

        if (data.status === 'success') {
          this.entries = data.data.tickets
          this.pagination = { ...this.pagination, ...data.pagination }
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    onAdvancedSearch() {
      this.advancedSearch = JSON.parse(JSON.stringify(this.search))

      if (this.search.fuzzy.value) {
        this.advancedSearch.no.value = null
        this.advancedSearch.title.value = null
      }

      this.isAdvancedSearchVisible = true
    },
    onAdvancedSearchSubmit() {
      this.search = this.$utils.merge({}, this.advancedSearch)

      if (this.advancedSearch.no.value || this.advancedSearch.title.value) {
        this.search.fuzzy.value = null
      }

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
      this.$router.push(`/tickets/${row.id}`)
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
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('ticket') }),
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
          let { data } = await this.$api.delete(`/v1/tickets/${row.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Ticket') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('ticket') })
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

      // if (params.table_search.applicable_month.value) {
      //   params.table_search.applicable_month = {
      //     value: date.formatDate(params.table_search.applicable_month.value, 'YYYY-MM-15')
      //   }
      // }

      const { access_token } = await this.$store.dispatch('auth/getToken')

      params.api_token = access_token

      this.$cookies.set('api_token', access_token, { expires: 1 })

      this.exportUrl = this.$api.getUri({
        url: '/v1/export/tickets',
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
.page-tickets {
  padding-bottom: 3rem;
}
</style>
