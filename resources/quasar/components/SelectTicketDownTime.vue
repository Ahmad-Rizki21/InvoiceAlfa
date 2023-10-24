<template>
  <q-select
    v-bind="$props"
    v-on="$listeners"
    :name="$attrs['name']"
  >
    <template #selected>
      <template v-if="value == $constant.ticket.DOWN_TIME_CUSTOMER">
        {{ $t('Customer') }}
      </template>
      <template v-else-if="value == $constant.ticket.DOWN_TIME_PROVIDER">
        {{ $t('AJNUSA') }}
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
  name: 'SelectTicketDownTime',
  props: {
    ...QSelect.options.props,
    options: {
      type: Array,
      default() {
        return [{
          id: this.$constant.ticket.DOWN_TIME_CUSTOMER,
          name: this.$t('Customer')
        }, {
          id: this.$constant.ticket.DOWN_TIME_PROVIDER,
          name: this.$t('AJNUSA')
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
