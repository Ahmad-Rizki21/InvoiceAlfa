<template>
  <q-select
    v-bind="$props"
    v-on="$listeners"
    :name="$attrs['name']"
    :options="entries"
    :display-value="valueToDisplay"
    @filter="onFilter"
  >
    <template v-if="detailed" v-slot:option="scope">
      <q-item
        v-bind="scope.itemProps"
        v-on="scope.itemEvents"
      >
        <q-item-section>
          <q-item-label class="flex">
            {{ scope.opt.name }}

            <span class="text-caption q-item__label--caption q-ml-auto">{{ scope.opt.code }}</span>
          </q-item-label>
          <q-item-label caption>{{ scope.opt.location }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>

    <template #no-option>
      <q-item>
        <q-item-section class="text-grey">
          {{ $t('No results') }}
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</template>

<script>
import QSelect from 'quasar/src/components/select/QSelect'

export default {
  name: 'AutocompleteDistributionCenter',
  props: {
    ...QSelect.options.props,
    optionValue: {
      type: [String, Function],
      default: 'id',
    },
    optionLabel: {
      type: [String, Function],
      default: 'name',
    },
    useInput: {
      type: Boolean,
      default: true
    },
    emitValue: {
      type: Boolean,
      default: true
    },
    detailed: {
      type: Boolean,
      default: false
    }
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
        rowsPerPage: 999999,
        rowsNumber: 0
      },
    };
  },
  computed: {
    valueToDisplay() {
      const value = this.entries.find(v => v.id == this.value)

      if (value) {
        return value.name
      }

      return null
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
        table_search: { fuzzy: { value: this.search }, status: { value: 1 } }
      }

      try {
        const { data } = await this.$api.get('/v1/distribution-centers', { params })

        if (data.status === 'success') {
          this.entries = data.data.distribution_centers
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
