<template>
  <q-card flat class="form-import-fix-table-distribution-center">
    <q-table
      :data="entries"
      :columns="columns"
      :loading="isLoading"
      :pagination.sync="pagination"
      binary-state-sort
      row-key="id"
      :title="$t('Fix the errors to continue')"
      dense
      @request="onRequest"
      hide-pagination
      flat
      bordered
    >
      <template #top-right="scope">
        <q-btn
          color="primary"
          class="q-btn-lg"
          unelevated
          @click="onSubmit"
        >
          <span v-if="scope.isLastPage">{{ $t('Done') }}</span>
          <span v-else>{{ $t('Next') }}</span>
        </q-btn>
      </template>
      <template #body-cell-id="{ rowIndex }">
        <q-td>{{ rowIndex + 1 }}</q-td>
      </template>
      <template #body-cell-name="{ row, rowIndex }">
        <q-td class="table-cell-name">
          <q-input
            v-show="!(isEditingRow === 'name' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.name"
            :error-message="row && row.errors && row.errors.name ? (row && row.errors && row.errors.name[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.name ? (row && row.errors && row.errors.name[0] || false) : false)"
            @click="isEditingRow = 'name';isEditingRowIndex = rowIndex"
          />

          <q-input
            v-if="isEditingRow === 'name' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.name"
            :error-message="row && row.errors && row.errors.name ? (row && row.errors && row.errors.name[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.name ? (row && row.errors && row.errors.name[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'name', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-code="{ row, rowIndex }">
        <q-td class="table-cell-code">
          <q-input
            v-show="!(isEditingRow === 'code' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.code"
            :error-message="row && row.errors && row.errors.code ? (row && row.errors && row.errors.code[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.code ? (row && row.errors && row.errors.code[0] || false) : false)"
            @click="isEditingRow = 'code';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'code' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.code"
            :error-message="row && row.errors && row.errors.code ? (row && row.errors && row.errors.code[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.code ? (row && row.errors && row.errors.code[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'code', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-location="{ row, rowIndex }">
        <q-td class="table-cell-location">
          <q-input
            v-show="!(isEditingRow === 'location' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.location"
            :error-message="row && row.errors && row.errors.location ? (row && row.errors && row.errors.location[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.location ? (row && row.errors && row.errors.location[0] || false) : false)"
            @click="isEditingRow = 'location';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'location' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.location"
            :error-message="row && row.errors && row.errors.location ? (row && row.errors && row.errors.location[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.location ? (row && row.errors && row.errors.location[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'location', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-address="{ row, rowIndex }">
        <q-td class="table-cell-address">
          <q-input
            v-show="!(isEditingRow === 'address' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.address"
            :error-message="row && row.errors && row.errors.address ? (row && row.errors && row.errors.address[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.address ? (row && row.errors && row.errors.address[0] || false) : false)"
            @click="isEditingRow = 'address';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'address' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.address"
            :error-message="row && row.errors && row.errors.address ? (row && row.errors && row.errors.address[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.address ? (row && row.errors && row.errors.address[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'address', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-approval_date="{ row, rowIndex }">
        <q-td class="table-cell-approval_date">
          <q-input
            v-show="!(isEditingRow === 'approval_date' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.approval_date"
            mask="##/##/####"
            :error-message="row && row.errors && row.errors.approval_date ? (row && row.errors && row.errors.approval_date[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.approval_date ? (row && row.errors && row.errors.approval_date[0] || false) : false)"
            @click="isEditingRow = 'approval_date';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'approval_date' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.approval_date"
            :error-message="row && row.errors && row.errors.approval_date ? (row && row.errors && row.errors.approval_date[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.approval_date ? (row && row.errors && row.errors.approval_date[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'approval_date', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-fo_approval_date="{ row, rowIndex }">
        <q-td class="table-cell-fo_approval_date">
          <q-input
            v-show="!(isEditingRow === 'fo_approval_date' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.fo_approval_date"
            mask="##/##/####"
            :error-message="row && row.errors && row.errors.fo_approval_date ? (row && row.errors && row.errors.fo_approval_date[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.fo_approval_date ? (row && row.errors && row.errors.fo_approval_date[0] || false) : false)"
            @click="isEditingRow = 'fo_approval_date';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'fo_approval_date' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.fo_approval_date"
            :error-message="row && row.errors && row.errors.fo_approval_date ? (row && row.errors && row.errors.fo_approval_date[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.fo_approval_date ? (row && row.errors && row.errors.fo_approval_date[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'fo_approval_date', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-offering_letter_reference_number="{ row, rowIndex }">
        <q-td class="table-cell-offering_letter_reference_number">
          <q-input
            v-show="!(isEditingRow === 'offering_letter_reference_number' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.offering_letter_reference_number"
            :error-message="row && row.errors && row.errors.offering_letter_reference_number ? (row && row.errors && row.errors.offering_letter_reference_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.offering_letter_reference_number ? (row && row.errors && row.errors.offering_letter_reference_number[0] || false) : false)"
            @click="isEditingRow = 'offering_letter_reference_number';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'offering_letter_reference_number' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.offering_letter_reference_number"
            :error-message="row && row.errors && row.errors.offering_letter_reference_number ? (row && row.errors && row.errors.offering_letter_reference_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.offering_letter_reference_number ? (row && row.errors && row.errors.offering_letter_reference_number[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'offering_letter_reference_number', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-fo_offering_letter_reference_number="{ row, rowIndex }">
        <q-td class="table-cell-fo_offering_letter_reference_number">
          <q-input
            v-show="!(isEditingRow === 'fo_offering_letter_reference_number' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.fo_offering_letter_reference_number"
            :error-message="row && row.errors && row.errors.fo_offering_letter_reference_number ? (row && row.errors && row.errors.fo_offering_letter_reference_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.fo_offering_letter_reference_number ? (row && row.errors && row.errors.fo_offering_letter_reference_number[0] || false) : false)"
            @click="isEditingRow = 'fo_offering_letter_reference_number';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'fo_offering_letter_reference_number' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.fo_offering_letter_reference_number"
            :error-message="row && row.errors && row.errors.fo_offering_letter_reference_number ? (row && row.errors && row.errors.fo_offering_letter_reference_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.fo_offering_letter_reference_number ? (row && row.errors && row.errors.fo_offering_letter_reference_number[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'fo_offering_letter_reference_number', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-issuance_number="{ row, rowIndex }">
        <q-td class="table-cell-issuance_number">
          <q-input
            v-show="!(isEditingRow === 'issuance_number' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.issuance_number"
            :error-message="row && row.errors && row.errors.issuance_number ? (row && row.errors && row.errors.issuance_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.issuance_number ? (row && row.errors && row.errors.issuance_number[0] || false) : false)"
            @click="isEditingRow = 'issuance_number';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'issuance_number' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.issuance_number"
            :error-message="row && row.errors && row.errors.issuance_number ? (row && row.errors && row.errors.issuance_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.issuance_number ? (row && row.errors && row.errors.issuance_number[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'issuance_number', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-fo_issuance_number="{ row, rowIndex }">
        <q-td class="table-cell-fo_issuance_number">
          <q-input
            v-show="!(isEditingRow === 'fo_issuance_number' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.fo_issuance_number"
            :error-message="row && row.errors && row.errors.fo_issuance_number ? (row && row.errors && row.errors.fo_issuance_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.fo_issuance_number ? (row && row.errors && row.errors.fo_issuance_number[0] || false) : false)"
            @click="isEditingRow = 'fo_issuance_number';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'fo_issuance_number' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.fo_issuance_number"
            :error-message="row && row.errors && row.errors.fo_issuance_number ? (row && row.errors && row.errors.fo_issuance_number[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.fo_issuance_number ? (row && row.errors && row.errors.fo_issuance_number[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'fo_issuance_number', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-email="{ row, rowIndex }">
        <q-td class="table-cell-email">
          <q-input
            v-show="!(isEditingRow === 'email' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.email"
            :error-message="row && row.errors && row.errors.email ? (row && row.errors && row.errors.email[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.email ? (row && row.errors && row.errors.email[0] || false) : false)"
            @click="isEditingRow = 'email';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'email' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.email"
            :error-message="row && row.errors && row.errors.email ? (row && row.errors && row.errors.email[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.email ? (row && row.errors && row.errors.email[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'email', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-username="{ row, rowIndex }">
        <q-td class="table-cell-username">
          <q-input
            v-show="!(isEditingRow === 'username' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.username"
            :error-message="row && row.errors && row.errors.username ? (row && row.errors && row.errors.username[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.username ? (row && row.errors && row.errors.username[0] || false) : false)"
            @click="isEditingRow = 'username';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'username' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.username"
            :error-message="row && row.errors && row.errors.username ? (row && row.errors && row.errors.username[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.username ? (row && row.errors && row.errors.username[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'username', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-password="{ row, rowIndex }">
        <q-td class="table-cell-password">
          <q-input
            v-show="!(isEditingRow === 'password' && isEditingRowIndex === rowIndex)"
            dense
            readonly
            v-model="entries[rowIndex].content.password"
            :error-message="row && row.errors && row.errors.password ? (row && row.errors && row.errors.password[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.password ? (row && row.errors && row.errors.password[0] || false) : false)"
            @click="isEditingRow = 'password';isEditingRowIndex = rowIndex"
          />
          <q-input
            v-if="isEditingRow === 'password' && isEditingRowIndex === rowIndex"
            dense
            autofocus
            :value="entries[rowIndex].content.password"
            :error-message="row && row.errors && row.errors.password ? (row && row.errors && row.errors.password[0] || '') : ''"
            :error="Boolean(row && row.errors && row.errors.password ? (row && row.errors && row.errors.password[0] || false) : false)"
            @input="onEditingRowInputDebounced($event, 'password', rowIndex)"
          />
        </q-td>
      </template>
      <template #body-cell-action="{ row, rowIndex }">
        <q-td class="text-right table-cell-action">
          <q-btn
            v-if="$auth.can('delete.distribution_center')"
            unelevated
            color="default"
            size="sm"
            padding="xs"
            icon="delete"
            class="q-ml-xs btn-datatable-delete-distribution-center"
            @click.prevent="onDelete(row, rowIndex)"
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
import { debounce } from 'quasar'
import datatableMixins from 'src/mixins/datatable'

export default {
  name: 'TableDistributionCenter',
  props: {
    customer: {
      type: Object,
      default() {
        return {}
      }
    },
    importType: {
      type: [String, Number]
    },
    importPath: {
      type: String
    },
    status: {
      type: String
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
      isEditingRow: null,
      isEditingRowIndex: null,
      formEditing: {},
      entries: [],
      columns: [
        // {
        //   name: 'id',
        //   field: 'id',
        //   label: '#',
        //   realLabel: this.$t('ID'),
        //   align: 'left',
        //   style: 'max-width: 25px',
        //   headerClasses: 'table-header-id',
        //   classes: 'table-cell-id',
        //   sortable: true
        // },
        {
          name: 'name',
          field: (v) => v.content.name,
          label: this.$t('Name'),
          headerClasses: 'table-header-name',
          classes: 'table-cell-name',
          align: 'left',
          sortable: false
        },
        {
          name: 'code',
          field: 'code',
          label: this.$t('Code'),
          headerClasses: 'table-header-code',
          classes: 'table-cell-code',
          align: 'left',
          sortable: false
        },
        {
          name: 'location',
          field: 'location',
          label: this.$t('Location'),
          headerClasses: 'table-header-location',
          classes: 'table-cell-location',
          align: 'left',
          sortable: false
        },
        {
          name: 'address',
          field: 'address',
          label: this.$t('Address'),
          headerClasses: 'table-header-address',
          classes: 'table-cell-address',
          align: 'left',
          sortable: false
        },
        {
          name: 'approval_date',
          field: 'approval_date',
          label: this.$t('SAT-HO Approval Date'),
          headerClasses: 'table-header-approval_date',
          classes: 'table-cell-approval_date',
          align: 'left',
          sortable: false
        },
        {
          name: 'fo_approval_date',
          field: 'fo_approval_date',
          label: this.$t('SAT-HO Approval Date for FO'),
          headerClasses: 'table-header-fo_approval_date',
          classes: 'table-cell-fo_approval_date',
          align: 'left',
          sortable: false
        },
        {
          name: 'offering_letter_reference_number',
          field: 'offering_letter_reference_number',
          label: this.$t('Offering Letter'),
          headerClasses: 'table-header-offering_letter_reference_number',
          classes: 'table-cell-offering_letter_reference_number',
          align: 'left',
          sortable: false
        },
        {
          name: 'fo_offering_letter_reference_number',
          field: 'fo_offering_letter_reference_number',
          label: this.$t('FO Offering Letter'),
          headerClasses: 'table-header-fo_offering_letter_reference_number',
          classes: 'table-cell-fo_offering_letter_reference_number',
          align: 'left',
          sortable: false
        },
        {
          name: 'issuance_number',
          field: 'issuance_number',
          label: this.$t('Basis for issuing invoice'),
          headerClasses: 'table-header-issuance_number',
          classes: 'table-cell-issuance_number',
          align: 'left',
          sortable: false
        },
        {
          name: 'fo_issuance_number',
          field: 'fo_issuance_number',
          label: this.$t('Basis for issuing FO invoice'),
          headerClasses: 'table-header-fo_issuance_number',
          classes: 'table-cell-fo_issuance_number',
          align: 'left',
          sortable: false
        },
        {
          name: 'email',
          field: 'email',
          label: this.$t('Email'),
          headerClasses: 'table-header-email',
          classes: 'table-cell-email',
          align: 'left',
          sortable: false
        },
        {
          name: 'username',
          field: 'username',
          label: this.$t('Username'),
          headerClasses: 'table-header-username',
          classes: 'table-cell-username',
          align: 'left',
          sortable: false
        },
        {
          name: 'password',
          field: 'password',
          label: this.$t('Password'),
          headerClasses: 'table-header-password',
          classes: 'table-cell-password',
          align: 'left',
          sortable: false
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
      advancedSearch: search,
      onEditingRowInputDebounced: debounce(this.onEditingRowInput, 250)
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
    onEditingRowInput(value, name, rowIndex) {
      this.entries[rowIndex].content[name] = value
      delete this.entries[rowIndex].errors[name]
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
        table_search: { ...this.search },
        import_path: this.importPath
      }

      try {
        const { data } = await this.$api.get('/v1/distribution-centers/import/errors', { params })

        if (data.status === 'success') {
          this.entries = data.data.import_cache
          this.pagination = { ...this.pagination, ...data.pagination }
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    async onSubmit() {
      let hasError = false

      for (let i = 0; i < this.entries.length; i++) {
        if (this.entries[i].errors && Object.keys(this.entries[i].errors).length) {
          hasError = true
          break
        }
      }

      if (hasError) {
        this.$q.notify({ message: this.$t('Please fix all errors first to continue'), type: 'negative' })
        return
      }

      try {
        let { data } = await this.$api.post('/v1/distribution-centers/import/fix', {
          import_cache: this.entries
        });

        if (data.status === 'success') {
          if (this.pagination.hasMorePage) {
            this.onRequest()
          } else {
            this.$emit('success')
          }
        } else {
          this.onRequest()
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
    async onDelete(row, rowIndex) {
      try {
        let { data } = await this.$api.delete(`/v1/distribution-centers/import/row/${row.id}`);

        if (data.status !== 'success') {
          this.$q.notify({ message: this.$t('Failed to delete row' )})
        } else {
          this.entries = this.entries.filter((v, i) => i !== rowIndex)
          console.log(this.entries.length)
        }
      } catch (err) {
        this.$q.notify(err);
      }

    }
  }
}
</script>

<style lang="scss">
.form-import-fix-table-distribution-center {
  height: 100%;

  .table-header-name,
  .table-cell-name {
    width: 15rem;
  }
  .table-header-code,
  .table-cell-code {
    width: 13rem;
  }
  .table-header-location,
  .table-cell-location {
    width: 13rem;
  }
  .table-header-address,
  .table-cell-address {
    width: 20rem;
  }
  .table-header-approval_date,
  .table-cell-approval_date {
    width: 14rem;
  }
  .table-header-fo_approval_date,
  .table-cell-fo_approval_date {
    width: 14rem;
  }
  .table-header-offering_letter_reference_number,
  .table-cell-offering_letter_reference_number {
    width: 14rem;
  }
  .table-header-fo_offering_letter_reference_number,
  .table-cell-fo_offering_letter_reference_number {
    width: 14rem;
  }
  .table-header-issuance_number,
  .table-cell-issuance_number {
    width: 15rem;
  }
  .table-header-fo_issuance_number,
  .table-cell-fo_issuance_number {
    width: 15rem;
  }
  .table-header-email,
  .table-cell-email {
    width: 14rem;
  }
  .table-header-username,
  .table-cell-username {
    width: 14rem;
  }
  .table-header-password,
  .table-cell-password {
    width: 14rem;
  }

  .q-input {
    width: inherit;
    font-size: 0.9em;

    .q-field__control,
    .q-field__marginal {
      height: 30px;
    }

    .q-field__bottom {
      min-height: 10px;
      padding-top: 4px;
    }
  }

  .q-field--with-bottom {
    padding-bottom: 10px;
  }

  .q-table__container {
    height: 100%;
  }
  .q-table__middle.scroll {
    // overflow-y: hidden;
  }

  .q-td {
    border-bottom: none !important;
  }
  .table-cell-action {
    .q-btn {
      position: relative;
      top: -0.5rem;
    }
  }
}
</style>
