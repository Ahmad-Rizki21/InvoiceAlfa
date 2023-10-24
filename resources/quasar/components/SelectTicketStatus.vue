<template>
  <q-select
    v-bind="$props"
    v-on="$listeners"
    :name="$attrs['name']"
  >
    <template #selected>
      <template v-if="value == $constant.ticket.STATUS_OPEN">
        {{ $t('Open') }}
      </template>
      <template v-else-if="value == $constant.ticket.STATUS_CLOSED">
        {{ $t('Closed') }}
      </template>
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
  name: 'SelectTicketStatus',
  props: {
    ...QSelect.options.props,
    options: {
      type: Array,
      default() {
        return [{
          id: this.$constant.ticket.STATUS_OPEN,
          name: this.$t('Open')
        }, {
          id: this.$constant.ticket.STATUS_CLOSED,
          name: this.$t('Closed')
        }]
      }
    },
    optionValue: {
      type: [String, Function],
      default: 'id',
    },
    optionLabel: {
      type: [String, Function],
      default: 'name',
    },
    emitValue: {
      type: Boolean,
      default: true
    }
  }
}
</script>
