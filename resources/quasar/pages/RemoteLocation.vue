<template>
  <q-page class="page-remote-locations">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        {{ $t('Remote Locations') }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.remote_location')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-toolbar-title>{{ $t('Remote Locations') }}</q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.remote_location')"
        color="primary"
        class="q-btn-lg btn-page-remote-locations-create"
        icon="add"
        @click="onFormEntryCreate"
      >
        <span class="q-ml-xs">{{ $t('New {entity}', { entity: $t('Remote') }) }}</span>
      </q-btn>
    </div>

    <div class="page-body">
      <q-card>
        <q-toolbar>
          <div class="col col-md-9 col-lg-8">
            <div class="row q-col-gutter-md quick-search-datatable">

              <div class="col col-md-4 col-lg-3">
                <autocomplete-customer
                  v-model="search.customer_id.value"
                  :label="$t('Search {entity}', { entity: $t('customer') })"
                  stack-label
                  dense
                  clearable
                  clear-icon="close"
                  @input="onRequest()"
                />
              </div>
              <div class="col col-md-4 col-lg-3">
                <q-input
                  v-model="search.fuzzy.value"
                  :label="$t('Search {entity}', { entity: $t('code, dc, or terminal name') })"
                  stack-label
                  :debounce="300"
                  dense
                  clearable
                  clear-icon="close"
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
                      :options="columns.map(v => ({ label: v.realLabel || v.label, value: v.name })).filter(v => !!v.label && (v.label !== 'customer' || !search.customer_id.value))"
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
          <template #body-cell-coordinate="{ row }">
            <q-td>
              <span v-if="row.latitude && row.longitude" class="text-color-default">
                <span>{{ $t('Latitude') }}: {{ row.latitude || '-' }}</span>
                <span class="q-ml-sm">{{ $t('Longitude') }}: {{ row.longitude || '-' }}</span>
                <q-btn
                  :href="`http://maps.google.com/maps?q=${row.latitude},${row.longitude}`"
                  flat
                  target="_blank"
                  class="q-ml-xs"
                  style="margin-top: -0.25rem"
                  padding="xs"
                  icon="link"
                  round
                  size="sm"
                >
                  <q-tooltip>
                    {{ $t('Open Google Maps') }}
                  </q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  class="q-ml-xs"
                  style="margin-top: -0.25rem"
                  padding="xs"
                  icon="content_copy"
                  round
                  size="sm"
                  @click.prevent="$utils.copyToClipboard(`http://maps.google.com/maps?q=${row.latitude},${row.longitude}`); $q.notify({ message: $t('{entity} copied to clipboard', { entity: $t('URL') }) })"
                >
                  <q-tooltip>
                    {{ $t('Copy {entity}', { entity: $t('URL') }) }}
                  </q-tooltip>
                </q-btn>
              </span>
            </q-td>
          </template>
          <template #body-cell-address="{ row }">
            <q-td class="overflow-ellipsis" style="max-width: 10rem;">
              <span :title="row.address">{{ row.address }}</span>
            </q-td>
          </template>
          <template #body-cell-gsm="{ row }">
            <q-td>
              <template v-if="row.gsm_no && row.gsm_provider">
                <span>{{ $t('No') }}: {{ row.gsm_no || '-' }}</span>
                <span class="q-ml-sm">{{ $t('Provider') }}: {{ row.gsm_provider || '-' }}</span>
              </template>
            </q-td>
          </template>
          <template #body-cell-gsm2="{ row }">
            <q-td>
              <template v-if="row.gsm_no2 && row.gsm_provider2">
                <span>{{ $t('No') }}: {{ row.gsm_no2 || '-' }}</span>
                <span class="q-ml-sm">{{ $t('Provider') }}: {{ row.gsm_provider2 || '-' }}</span>
              </template>
            </q-td>
          </template>
          <template #body-cell-pic_remote="{ row }">
            <q-td>
              <template v-if="row.pic_remote_name && row.pic_remote_phone_number">
                <span>{{ $t('Name') }}: {{ row.pic_remote_name || '-' }}</span>
                <span class="q-ml-sm">{{ $t('Phone') }}: {{ row.pic_remote_phone_number || '-' }}</span>
              </template>
            </q-td>
          </template>
          <template #body-cell-pic_it_sat="{ row }">
            <q-td>
              <template v-if="row.pic_it_sat_name && row.pic_it_sat_phone_number">
                <span>{{ $t('Name') }}: {{ row.pic_it_sat_name || '-' }}</span>
                <span class="q-ml-sm">{{ $t('Phone') }}: {{ row.pic_it_sat_phone_number || '-' }}</span>
              </template>
            </q-td>
          </template>
          <template #body-cell-pic_fo_provider="{ row }">
            <q-td>
              <template v-if="row.pic_fo_provider_name && row.pic_fo_provider_phone_number">
                <span>{{ $t('Name') }}: {{ row.pic_fo_provider_name || '-' }}</span>
                <span class="q-ml-sm">{{ $t('Phone') }}: {{ row.pic_fo_provider_phone_number || '-' }}</span>
              </template>
            </q-td>
          </template>
          <template #body-cell-pic_service_point="{ row }">
            <q-td>
              <template v-if="row.pic_service_point_name && row.pic_service_point_phone_number">
                <span>{{ $t('Name') }}: {{ row.pic_service_point_name || '-' }}</span>
                <span class="q-ml-sm">{{ $t('Phone') }}: {{ row.pic_service_point_phone_number || '-' }}</span>
              </template>
            </q-td>
          </template>
          <template #body-cell-status="{ row }">
            <q-td class="text-center">
              <q-icon :name="row.status ? 'check' : 'close'" class="q-mr-lg" />
            </q-td>
          </template>
          <template #body-cell-action="{ row }">
            <q-td class="text-right table-cell-action">
              <q-btn
                unelevated
                color="default"
                size="sm"
                padding="xs"
                class="btn-datatable-view-remote-location"
                icon="visibility"
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
                v-if="$auth.can('delete.remote_location')"
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="delete"
                class="q-ml-xs btn-datatable-delete-remote-location"
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
              dense
            />
            <q-input
              v-model="advancedSearch.code.value"
              :label="$t('Search {entity}', { entity: $t('code') })"
              outlined
              dense
            />
            <q-input
              v-model="advancedSearch.terminal_name.value"
              :label="$t('Search {entity}', { entity: $t('terminal name') })"
              outlined
              dense
            />
            <q-input
              v-model="advancedSearch.distribution_center.value"
              :label="$t('Search {entity}', { entity: $t('DC') })"
              outlined
              dense
            />
            <q-input
              v-model="advancedSearch.address.value"
              :label="$t('Search {entity}', { entity: $t('address') })"
              outlined
              dense
            />
            <q-input
              v-model="advancedSearch.infrastructure_type.value"
              :label="$t('Search {entity}', { entity: $t('infrastructure type') })"
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
  </q-page>
</template>

<script>
import { date } from 'quasar'
import FormEntry from './RemoteLocation/FormEntry'
import datatableMixins from 'src/mixins/datatable'

export default {
  name: 'PageRemoteLocation',
  components: {
    FormEntry
  },
  meta() {
    return {
      title: this.$t('Remote Locations') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Remote Locations') }
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
      code: {
        value: null
      },
      terminal_name: {
        value: null
      },
      distribution_center: {
        value: null
      },
      address: {
        value: null
      },
      infrastructure_type: {
        value: null,
        operator: 'like'
      }
    }

    return {
      entries: [],
      visibleColumns: [
        'id',
        'customer',
        'code',
        'name',
        'distribution_center',
        'terminal_name',
        'address',
        // 'postal_code',
        // 'coordinate',
        'online_at',
        // 'pic_remote',
        // 'pic_it_sat',
        'infrastructure_type',
        // 'gsm',
        // 'gsm2',
        // 'cid_provider',
        // 'fo_provider',
        // 'pic_fo_provider',
        // 'pic_service_point',
        // 'fo_contract_by_name',
        'created_at'
      ],
      isLoading: false,
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
      advancedSearch: search
    }
  },
  computed: {
    columns() {
      return [
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
        this.search.customer_id.value ? null : {
          name: 'customer',
          field: 'customer',
          format: v => v ? v.name : '',
          label: this.$t('Customer'),
          align: 'left',
          headerClasses: 'table-header-customer_id',
          classes: 'table-cell-customer_id',
          sortable: true
        },
        {
          name: 'code',
          field: 'code',
          label: this.$t('Code'),
          align: 'left',
          headerClasses: 'table-header-code',
          classes: 'table-cell-code',
          sortable: true
        },
        {
          name: 'name',
          field: 'name',
          label: this.$t('Name'),
          align: 'left',
          headerClasses: 'table-header-name',
          classes: 'table-cell-name',
          sortable: true
        },
        {
          name: 'distribution_center',
          field: 'distribution_center',
          label: this.$t('DC'),
          align: 'left',
          headerClasses: 'table-header-distribution_center',
          classes: 'table-cell-distribution_center',
          sortable: true
        },
        {
          name: 'terminal_name',
          field: 'terminal_name',
          label: this.$t('Terminal Name'),
          align: 'left',
          headerClasses: 'table-header-terminal_name',
          classes: 'table-cell-terminal_name',
          sortable: true
        },
        {
          name: 'address',
          field: 'address',
          label: this.$t('Address'),
          align: 'left',
          style: 'max-width: 100px',
          headerClasses: 'table-header-address',
          classes: 'table-cell-address',
          sortable: true
        },
        {
          name: 'postal_code',
          field: 'postal_code',
          label: this.$t('Postal Code'),
          align: 'left',
          headerClasses: 'table-header-postal_code',
          classes: 'table-cell-postal_code',
          sortable: true
        },
        {
          name: 'coordinate',
          field: 'coordinate',
          label: this.$t('Coordinate'),
          align: 'left',
          headerClasses: 'table-header-coordinate',
          classes: 'table-cell-coordinate',
          format: (v, row) => row.latitude && row.longitude ? `${this.$utils.formatFloatDecimal(row.latitude, 3)}, ${this.$utils.formatFloatDecimal(row.longitude, 3)}` : '',
          sortable: false
        },
        {
          name: 'online_at',
          field: 'online_at',
          label: this.$t('Online At'),
          align: 'left',
          format: v => v ? date.formatDate(v, 'DD/MM/YYYY') : '',
          style: 'max-width: 100px',
          headerClasses: 'table-header-online_at',
          classes: 'table-cell-online_at',
          sortable: true
        },
        {
          name: 'pic_remote',
          field: 'pic_remote',
          label: this.$t('PIC Remote'),
          align: 'left',
          headerClasses: 'table-header-pic_remote',
          classes: 'table-cell-pic_remote',
          sortable: false
        },
        {
          name: 'pic_it_sat',
          field: 'pic_it_sat',
          label: this.$t('PIC IT SAT'),
          align: 'left',
          headerClasses: 'table-header-pic_it_sat',
          classes: 'table-cell-pic_it_sat',
          sortable: false
        },
        {
          name: 'infrastructure_type',
          field: 'infrastructure_type',
          label: this.$t('Infrastructure Type'),
          align: 'left',
          headerClasses: 'table-header-infrastructure_type',
          classes: 'table-cell-infrastructure_type',
          sortable: false
        },
        {
          name: 'gsm',
          field: 'gsm',
          label: this.$t('GSM') + ' 1',
          align: 'left',
          headerClasses: 'table-header-gsm',
          classes: 'table-cell-gsm',
          sortable: false
        },
        {
          name: 'gsm2',
          field: 'gsm2',
          label: this.$t('GSM') + ' 2',
          align: 'left',
          headerClasses: 'table-header-gsm_2',
          classes: 'table-cell-gsm_2',
          sortable: false
        },
        {
          name: 'cid_provider',
          field: 'cid_provider',
          label: this.$t('CID Provider'),
          align: 'left',
          headerClasses: 'table-header-cid_provider',
          classes: 'table-cell-cid_provider',
          sortable: false
        },
        {
          name: 'fo_provider',
          field: 'fo_provider',
          label: this.$t('FO Provider'),
          align: 'left',
          headerClasses: 'table-header-fo_provider',
          classes: 'table-cell-fo_provider',
          sortable: false
        },
        {
          name: 'pic_fo_provider',
          field: 'pic_fo_provider',
          label: this.$t('PIC FO Provider'),
          align: 'left',
          headerClasses: 'table-header-pic_fo_provider',
          classes: 'table-cell-pic_fo_provider',
          sortable: false
        },
        {
          name: 'pic_service_point',
          field: 'pic_service_point',
          label: this.$t('PIC Service Point'),
          align: 'left',
          headerClasses: 'table-header-pic_service_point',
          classes: 'table-cell-pic_service_point',
          sortable: false
        },
        {
          name: 'fo_contract_by_name',
          field: 'fo_contract_by_name',
          label: this.$t('FO Contract By'),
          align: 'left',
          headerClasses: 'table-header-fo_contract_by',
          classes: 'table-cell-fo_contract_by',
          sortable: false
        },
        {
          name: 'created_at',
          field: 'created_at',
          label: this.$t('Created At'),
          align: 'left',
          format: val => date.formatDate(val, 'DD MMM YYYY HH:mm'),
          headerClasses: 'table-header-created_at',
          classes: 'table-cell-created_at',
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
      ].filter(v => Boolean(v))
    }
  },
  watch: {
    'search.fuzzy.value' (n, o) {
      if (n !== o && n) {
        this.search.code.value = null
        this.search.name.value = null
        this.search.terminal_name.value = null
        this.search.distribution_center.value = null

        this.onRequest()
      }
    }
  },
  async mounted() {
    if (this.$route.query.customer_id) {
      this.search.customer_id.value = this.$route.query.customer_id
      await this.$nextTick()

      this.$router.replace({
        path: this.$route.path,
        params: {
          customer_id: this.$route.query.customer_id
        }
      })

      return
    } else if (this.$route.params.customer_id) {
      this.search.customer_id.value = this.$route.params.customer_id
      await this.$nextTick()
    }

    await this.onRequest()

    // if (!this.$store.getters['tourGuide/finishedGroups']['remote-locations']) {
    //   this.$tourGuide.open('remote-locations')
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
        table_search: { ...this.search }
      }

      params.includes = 'customer'

      try {
        const { data } = await this.$api.get('/v1/remote-locations', { params })

        if (data.status === 'success') {
          this.entries = data.data.remote_locations
          this.pagination = { ...this.pagination, ...data.pagination }

          this.$nextTick(() => {
            this.$forceUpdate()
          })
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    onAdvancedSearch() {
      this.advancedSearch = JSON.parse(JSON.stringify(this.search))

      if (this.search.fuzzy.value) {
        this.advancedSearch.code.value = null
        this.advancedSearch.name.value = null
        this.advancedSearch.terminal_name.value = null
        this.advancedSearch.distribution_center.value = null
      }

      this.isAdvancedSearchVisible = true
    },
    onAdvancedSearchSubmit() {
      this.search = this.$utils.merge({}, this.advancedSearch)

      if (this.advancedSearch.code.value || this.advancedSearch.name.value || this.advancedSearch.terminal_name.value || this.advancedSearch.distribution_center.value) {
        this.search.fuzzy.value = null
      }

      this.onRequest()

      this.isAdvancedSearchVisible = false
    },
    onFormEntryCreate() {
      this.formEntry = {}
      this.isFormEntryReadonly = false
      this.isFormEntryVisible = false
      this.$router.push('/remote-locations/create')
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
      this.$router.push(`/remote-locations/${row.id}`)
      this.formEntry = { ...row }
      this.isFormEntryReadonly = true
      this.isFormEntryVisible = false
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
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('location') }) + ' ' + this.$t('All corresponding tickets and reports would also be deleted.'),
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
          let { data } = await this.$api.delete(`/v1/remote-locations/${row.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Remote location') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('remote location') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
      })
    }
  }
}
</script>

<style lang="scss">
.page-remote-locations {
  padding-bottom: 3rem;
}
</style>
