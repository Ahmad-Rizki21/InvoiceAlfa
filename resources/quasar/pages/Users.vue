<template>
  <q-page class="page-users">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="menu" @click="$store.dispatch('app/toggleSidebar')" />
      <q-toolbar-title>
        {{ $t('Users') }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.users')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-toolbar-title>{{ $t('Users') }}</q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.users')"
        color="primary"
        class="q-ml-sm q-btn-lg btn-page-users-create"
        icon="add"
        @click="onFormEntryCreate"
      >
        {{ $t('New {entity}', { entity: $t('User') }) }}
      </q-btn>
    </div>

    <div class="page-body">
      <q-card>
        <q-toolbar>
          <div class="col col-md-9 col-lg-8">
            <div class="row q-col-gutter-md quick-search-datatable">

              <div class="col col-md-4 col-lg-3">
                <q-input
                  v-model="search.fuzzy.value"
                  :label="$t('Search {entity}', { entity: $t('name, email or username') })"
                  stack-label
                  :debounce="300"
                  dense
                  clearable
                  clear-icon="close"
                />
              </div>
              <div class="col col-md-4 col-lg-3">
                <autocomplete-role
                  v-model="search.role_id.value"
                  :label="$t('Search {entity}', { entity: $t('role') })"
                  stack-label
                  dense
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
                <div class="row no-wrap q-pa-md" style="min-width: 10em">
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
          <template #body-cell-action="{ row }">
            <q-td class="text-right table-cell-action">
              <q-btn
                unelevated
                color="default"
                size="sm"
                padding="xs"
                class="btn-datatable-view-user"
                icon="visibility"
                @click.prevent="onView(row)"
              >
                <q-tooltip>
                  {{ $t('View') }}
                </q-tooltip>
              </q-btn>
              <q-btn
                v-if="$auth.can('edit.users')"
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="edit"
                class="q-ml-xs btn-datatable-edit-user"
                @click.prevent="onEdit(row)"
              >
                <q-tooltip>
                  {{ $t('Edit') }}
                </q-tooltip>
              </q-btn>
              <q-btn
                v-if="$auth.can('edit.users')"
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
                v-if="$auth.can('delete.users')"
                :disable="parseInt(row.id, 10) === 1"
                unelevated
                color="default"
                size="sm"
                padding="xs"
                icon="delete"
                class="q-ml-xs btn-datatable-delete-user"
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
          <q-card-section class="q-col-gutter-md">
            <q-input
              v-model="advancedSearch.name.value"
              :label="$t('Search {entity}', { entity: $t('name') })"
              outlined
              dense
              clearable
            />
            <autocomplete-role
              v-model="advancedSearch.role_id.value"
              :label="$t('Search {entity}', { entity: $t('role') })"
              outlined
              dense
              clearable
            />
            <q-input
              v-model="advancedSearch.email.value"
              :label="$t('Search {entity}', { entity: $t('email') })"
              outlined
              dense
              clearable
            />
            <q-input
              v-model="advancedSearch.username.value"
              :label="$t('Search {entity}', { entity: $t('username') })"
              outlined
              dense
              clearable
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
  </q-page>
</template>

<script>
import { date } from 'quasar'
import FormEntry from './Users/FormEntry'
import FormPassword from './Users/FormPassword'
import datatableMixins from 'src/mixins/datatable'

export default {
  name: 'PageUsers',
  components: {
    FormEntry,
    FormPassword
  },
  meta() {
    return {
      title: this.$t('Users') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Users') }
    ]
  },
  mixins: [datatableMixins],
  data() {
    const search = {
      fuzzy: {
        value: null
      },
      role_id: {
        value: null
      },
      name: {
        value: null,
        operator: 'like'
      },
      email: {
        value: null,
        operator: 'like'
      },
      username: {
        value: null,
        operator: 'like'
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
          name: 'name',
          field: 'name',
          label: this.$t('Name'),
          align: 'left',
          headerClasses: 'table-header-name',
          classes: 'table-cell-name',
          sortable: true
        },
        {
          name: 'email',
          field: 'email',
          label: this.$t('Email'),
          align: 'left',
          headerClasses: 'table-header-email',
          classes: 'table-cell-email',
          sortable: true
        },
        {
          name: 'username',
          field: 'username',
          label: this.$t('Username'),
          align: 'left',
          headerClasses: 'table-header-username',
          classes: 'table-cell-username',
          sortable: true
        },
        {
          name: 'role_id',
          field: row => row.role ? row.role.name : '',
          label: this.$t('Role'),
          align: 'left',
          headerClasses: 'table-header-role_id',
          classes: 'table-cell-role_id',
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
        'name',
        'email',
        'username',
        'role_id',
        'created_at'
      ],
      isLoading: false,
      isAdvancedSearchVisible: false,
      isFormEntryVisible: false,
      isFormEntryReadonly: false,
      formEntry: {},
      isFormPasswordVisible: false,
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
  watch: {
    'search.fuzzy.value'(n, o) {
      if (n !== o && n) {
        this.search.name.value = null
        this.search.email.value = null
        this.search.username.value = null

        this.onRequest()
      }
    }
  },
  async mounted() {
    await this.onRequest()

    await this.$nextTick()

    if (!this.$store.getters['tourGuide/finishedGroups'].users) {
      this.$tourGuide.open('users')
    }
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
        includes: 'role',
      }

      try {
        const { data } = await this.$api.get('/v1/users', { params })

        if (data.status === 'success') {
          this.entries = data.data.users
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
        this.advancedSearch.name.value = null
        this.advancedSearch.email.value = null
        this.advancedSearch.username.value = null
      }

      this.isAdvancedSearchVisible = true
    },
    onAdvancedSearchSubmit() {
      this.search = this.$utils.merge({}, this.advancedSearch)

      if (this.advancedSearch.name.value || this.advancedSearch.email.value || this.advancedSearch.username.value) {
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
    onFormPasswordSuccess() {
      this.isFormPasswordVisible = false
    },
    onFormPasswordCancel() {
      this.isFormPasswordVisible = false
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
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('user') }),
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
          let { data } = await this.$api.delete(`/v1/users/${row.id}`);

          if (data.message) {
            this.$q.notify({ message: data.message })
            this.onRequest()
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('User') }) })
              this.onRequest()
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('user') })
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
.page-users {
  padding-bottom: 3rem;
}
</style>
