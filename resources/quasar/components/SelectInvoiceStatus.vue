<template>
  <q-select
    v-bind="$props"
    v-on="$listeners"
    :name="$attrs['name']"
  >
    <template #selected>
      <template v-if="value == $constant.invoice_status.Draft">
        {{ $t('Draft') }}
      </template>
      <template v-else-if="value == $constant.invoice_status.Unpaid">
        {{ $t('Unpaid') }}
      </template>
      <template v-else-if="value == $constant.invoice_status.PendingReview">
        {{ $t('Pending Review') }}
      </template>
      <template v-else-if="value == $constant.invoice_status.Paid">
        {{ $t('Paid') }}
      </template>
      <template v-else-if="value == $constant.invoice_status.Rejected">
        {{ $t('Rejected') }}
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
        const constant = this.$constant.invoice_status

        return [{
          id: constant.Draft,
          name: this.$t('Draft')
        }, {
          id: constant.Unpaid,
          name: this.$t('Unpaid')
        }, {
          id: constant.PendingReview,
          name: this.$t('Pending Review')
        }, {
          id: constant.Paid,
          name: this.$t('Paid')
        }, {
          id: constant.Rejected,
          name: this.$t('Rejected')
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
