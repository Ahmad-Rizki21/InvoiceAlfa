<template>
  <q-card>
    <q-toolbar>
      <div class="col col-md-3 col-lg-2 quick-search-datatable">
        <q-input
          v-model="search.fuzzy.value"
          :label="$t('Search {entity}', { entity: $t('name') })"
          stack-label
          :debounce="300"
          dense
          clearable
          clear-icon="close"
          @input="onRequest()"
        />
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
            icon="visibility"
            class="btn-datatable-view-distribution-center"
            @click.prevent="onView(row)"
          >
            <q-tooltip>
              {{ $t('View') }}
            </q-tooltip>
          </q-btn>
          <q-btn
            v-if="$auth.can('delete.distribution_center')"
            unelevated
            color="default"
            size="sm"
            padding="xs"
            icon="delete"
            class="q-ml-xs btn-datatable-delete-distribution-center"
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
</template>

<script>
import { date } from 'quasar'
import FormEntry from './FormEntry'
import FormPassword from './FormPassword'
import datatableMixins from 'src/mixins/datatable'

export default {
  name: 'TableDistributionCenter',
  props: {
    customer: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  mixins: [datatableMixins],
  data() {
    const search = {
      fuzzy: {
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
          name: 'code',
          field: 'code',
          label: this.$t('Code'),
          headerClasses: 'table-header-code',
          classes: 'table-cell-code',
          align: 'left',
          sortable: true
        },
        {
          name: 'name',
          field: 'name',
          label: this.$t('Name'),
          headerClasses: 'table-header-name',
          classes: 'table-cell-name',
          align: 'left',
          sortable: true
        },
        {
          name: 'location',
          field: 'location',
          label: this.$t('Location'),
          headerClasses: 'table-header-location',
          classes: 'table-cell-location',
          align: 'left',
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
        'code',
        'location',
        'name',
        'created_at'
      ],
      isLoading: false,
      isAdvancedSearchVisible: false,
      isFormEntryVisible: false,
      isFormEntryReadonly: false,
      isFormPasswordVisible: false,
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
      advancedSearch: search
    }
  },
  async mounted() {
    await this.onRequest()
    await this.$nextTick()

    // if (!this.$store.getters['tourGuide/finishedGroups'].distribution_centers) {
    //   this.$tourGuide.open('distribution_centers')
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
        table_search: { ...this.search },
      }

      params.table_search.customer_id = { value: this.customer.id }
      console.log('askdjaksdj')

      try {
        const { data } = await this.$api.get('/v1/distribution-centers', { params })

        if (data.status === 'success') {
          this.entries = data.data.distribution_centers
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
      this.$router.push('/distribution-centers/create')

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
    onView(row) {
      if (this.customer.id) {
        this.$router.push({
          path: `/distribution-centers/${row.id}`,
          query: {
            customer_id: this.customer.id
          }
        })
      } else {
        this.$router.push(`/distribution-centers/${row.id}`)
      }

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
    onDelete(row) {
      if (this.isLoading) {
        return;
      }

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('distribution center') }) + ' ' + this.$t('All corresponding remote, tickets and reports would also be deleted.'),
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
          let { data } = await this.$api.delete(`/v1/distribution-centers/${row.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Distribution Center') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('distribution center') })
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
