<template>
  <q-chip v-bind="chipProps">
    {{ chipProps.text }}
  </q-chip>
</template>

<script>
export default {
  name: 'InvoiceStatusChip',
  props: {
    invoice: {
      type: Object,
      default() {
        return {}
      }
    },
    dense: Boolean
  },
  computed: {
    chipProps() {
      const props = {
        ['class']: 'invoice-status-chip',
        textColor: 'white',
        icon: 'fiber_manual_record',
        padding: 'md',
        dense: this.dense
      }

      const status = this.invoice.status
      const constant = this.$constant.invoice_status

      if (!status || status == constant.Draft) {
        props.color = 'default'
        props.text = this.$t('Draft')
        props.textColor = 'dark'
      } else if (status == constant.Unpaid) {
        props.color = 'yellow-1'
        props.text = this.$t('Unpaid')
        props.textColor = 'orange-10'
      } else if (status == constant.PendingReview) {
        props.color = 'warning'
        props.text = this.$t('Pending Review')
      } else if (status == constant.Paid) {
        props.color = 'positive'
        props.text = this.$t('Paid')
      } else if (status == constant.Rejected) {
        props.color = 'negative'
        props.text = this.$t('Rejected')
      }

      return props
    }
  }
}
</script>

<style lang="scss">
.invoice-status-chip {
  &.bg-positive {
    .q-chip__icon {
      color: #0f7e29;
    }
  }

  .q-chip__content {
    font-weight: 500;
  }

  &.q-chip--dense {
    font-size: 0.725rem;

    .q-chip__content {
      padding-right: 0.25rem;
    }
  }
}
</style>
