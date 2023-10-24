<template>
  <q-select
    v-bind="$props"
    v-on="$listeners"
    :name="$attrs['name']"
    :options="entries"
    :display-value="valueToDisplay"
    ref="select"
    @filter="onFilter"
  >
    <template #option="scope">
      <q-item
        v-bind="scope.itemProps"
        v-on="scope.itemEvents"
      >
        <q-item-section>
          <q-item-label v-if="scope.opt.code">{{ scope.opt.code }}</q-item-label>
          <q-item-label caption>
            <em v-if="scope.opt.terminal_name">{{ scope.opt.terminal_name }}</em>
            <span class="q-mx-xs">—</span>
            <span>{{ scope.opt.distribution_center }}</span>
          </q-item-label>
        </q-item-section>
      </q-item>
    </template>

    <template #no-option>
      <q-item>
        <q-item-section class="text-grey">
          <template v-if="customerRequired">
            {{ $t('Please select customer first.') }}
          </template>
          <template v-else>
            {{ $t('No results') }}
          </template>
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</template>

<script>
import QSelect from 'quasar/src/components/select/QSelect'

export default {
  name: 'AutocompleteRemoteLocation',
  props: {
    ...QSelect.options.props,
    optionValue: {
      type: [String, Function],
      default: 'id',
    },
    optionLabel: {
      type: [String, Function],
      default: 'terminal_name',
    },
    useInput: {
      type: Boolean,
      default: true
    },
    emitValue: {
      type: Boolean,
      default: true
    },
    customerId: [String, Number],
    customerRequired: Boolean
  },
  data() {
    return {
      isLoading: false,
      isFormEntryVisible: false,
      formEntry: {},
      entries: [],
      search: '',
      pagination: {
        sortBy: 'id',
        descending: false,
        page: 1,
        rowsPerPage: 9999999,
        rowsNumber: 0
      },
    };
  },
  computed: {
    valueToDisplay() {
      const value = this.entries.find(v => v.id == this.value)

      if (value) {
        return value.code ?  `${value.code} (${value.terminal_name || value.distribution_center})` : `${value.terminal_name} — ${value.distribution_center}`
      }

      return null
    }
  },
  watch: {
    customerId(n, o) {
      if (n !== o) {
        this.onRequest()
      }
    }
  },
  mounted() {
    this.onRequest()
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
        table_search: { fuzzy: { value: this.search } }
      }

      if (this.customerId) {
        params.table_search.customer_id = { value: this.customerId }
      } else if (this.customerRequired) {
        this.isLoading = false
        return
      }

      try {
        const { data } = await this.$api.get('/v1/remote-locations', { params })

        if (data.status === 'success') {
          this.entries = data.data.remote_locations
          this.pagination = { ...this.pagination, ...data.pagination }

          await this.$nextTick()
          this.$forceUpdate()

          if (!this.valueToDisplay) {
            this.$emit('input', null)

            if (this.$refs.select) {
              this.$refs.select.reset()

              await this.$nextTick()
              this.$forceUpdate()

              this.$refs.select.resetValidation()
            }
          }
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    async onFilter(val, update) {
      this.search = val

      if (val === '') {
        await this.onRequest()

        update(this.$utils.noop)
        return
      }

      await this.onRequest()

      update(this.$utils.noop)
    }
  }
}
</script>
