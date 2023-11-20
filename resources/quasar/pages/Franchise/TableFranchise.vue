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
            class="btn-datatable-view-franchise"
            @click.prevent="onView(row)"
          >
            <q-tooltip>
              {{ $t('View') }}
            </q-tooltip>
          </q-btn>
          <q-btn
            v-if="!row.has_password"
            unelevated
            color="default"
            size="sm"
            padding="xs"
            icon="login"
            class="q-ml-xs btn-datatable-set-login-credentials-franchise"
            @click.prevent="onSetLoginCredentials(row)"
          >
            <q-tooltip>
              {{ $t('Set login credentials') }}
            </q-tooltip>
          </q-btn>
          <q-btn
            v-else-if="row.has_password && $auth.can('edit.franchise')"
            unelevated
            color="default"
            size="sm"
            padding="xs"
            icon="lock_open"
            class="q-ml-xs btn-datatable-update-password-user"
            @click.prevent="onEditPassword(row)"
          >
            <q-tooltip>
              {{ $t('Update {entity}', { entity: $t('Password') }) }}
            </q-tooltip>
          </q-btn>
          <q-btn
            v-if="$auth.can('delete.franchise')"
            unelevated
            color="default"
            size="sm"
            padding="xs"
            icon="delete"
            class="q-ml-xs btn-datatable-delete-franchise"
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


    <q-dialog
      v-model="isFormLoginCredentialsVisible"
    >
      <form-login-credentials
        :entry="formEntry"
        :loading="isLoading"
        @success="onFormLoginCredentialsSuccess"
        @cancel="onFormLoginCredentialsCancel"
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

  </q-card>
</template>

<script>
import { date } from 'quasar'
import { mapGetters } from 'vuex'
import FormLoginCredentials from './FormLoginCredentials'
import FormPassword from './FormPassword'
import datatableMixins from 'src/mixins/datatable'

export default {
  name: 'TableFranchise',
  components: {
    FormLoginCredentials,
    FormPassword
  },
  props: {
    distributionCenter: {
      type: Object,
      default() {
        return {}
      }
    },
    asRelation: {
      type: Boolean,
      default: false
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
          name: 'npwp',
          field: 'npwp',
          format: (v, row) => row.npwp_formatted,
          label: this.$t('NPWP'),
          headerClasses: 'table-header-npwp',
          classes: 'table-cell-npwp',
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
          name: 'landline_number',
          field: 'landline_number',
          label: this.$t('Landline Number'),
          headerClasses: 'table-header-landline_number',
          classes: 'table-cell-landline_number',
          align: 'left',
          sortable: true
        },
        {
          name: 'phone_number',
          field: 'phone_number',
          label: this.$t('Phone Number'),
          headerClasses: 'table-header-phone_number',
          classes: 'table-cell-phone_number',
          align: 'left',
          sortable: true
        },
        {
          name: 'email',
          field: 'email',
          label: this.$t('Email'),
          headerClasses: 'table-header-email',
          classes: 'table-cell-email',
          align: 'left',
          sortable: true
        },
        {
          name: 'username',
          field: 'username',
          label: this.$t('Username'),
          headerClasses: 'table-header-username',
          classes: 'table-cell-username',
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
        'name',
        // 'npwp',
        'location',
        // 'landline_number',
        // 'phone_number',
        // 'email',
        // 'username',
        'created_at'
      ],
      isLoading: false,
      isAdvancedSearchVisible: false,
      isFormEntryVisible: false,
      isFormEntryReadonly: false,
      isFormPasswordVisible: false,
      isFormLoginCredentialsVisible: false,
      isFormImportVisible: false,
      formEntry: {},
      formPassword: {},
      pagination: {
        sortBy: 'id',
        descending: false,
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0
      },
      selected: [],
      search,
      advancedSearch: search
    }
  },
  async mounted() {
    await this.onRequest()
    await this.$nextTick()

    // if (!this.$store.getters['tourGuide/finishedGroups'].franchises) {
    //   this.$tourGuide.open('franchises')
    // }
  },
  computed: {
    ...mapGetters({
      'currentImportStatus': 'imports/status',
      'currentImportPath': 'imports/importPath',
      'currentImportProcessingPage': 'imports/processingPage',
      'currentImportHasError': 'imports/hasError',
    })
  },
  methods: {
    async onRequest(props) {
      if (this.isLoading) {
        return
      }

      if (this.asRelation && !this.distributionCenter.id) {
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

      if (this.asRelation) {
        params.table_search.distribution_center_id = { value: this.distributionCenter.id }
      }

      try {
        const { data } = await this.$api.get('/v1/franchises', { params })

        if (data.status === 'success') {
          this.entries = data.data.franchises
          this.selected = data.data.franchises
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
      this.$router.push('/franchises/create')

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
    onFormLoginCredentialsSuccess() {
      this.isFormLoginCredentialsVisible = false
      this.formEntry = {}

      this.onRequest()
    },
    onFormLoginCredentialsCancel() {
      this.isFormLoginCredentialsVisible = false
      this.formEntry = {}
    },
    onView(row) {
      if (this.franchise && this.franchise.id) {
        this.$router.push({
          path: `/franchises/${row.id}`,
          query: {
            franchise_id: this.franchise.id
          }
        })
      } else {
        this.$router.push(`/franchises/${row.id}`)
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
      this.isFormLoginCredentialsVisible = false
      this.isFormPasswordVisible = true
    },
    onSetLoginCredentials(row) {
      this.formEntry = { ...row }
      this.isFormPasswordVisible = false
      this.isFormLoginCredentialsVisible = true
    },
    onDelete(row) {
      if (this.isLoading) {
        return;
      }


      this.isFormLoginCredentialsVisible = false
      this.isFormPasswordVisible = false

      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('franchise') }),
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
          let { data } = await this.$api.delete(`/v1/franchises/${row.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Franchise') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('franchise') })
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
