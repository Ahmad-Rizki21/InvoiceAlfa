<template>
  <span v-if="!(ticket && ticket.id)">
    -
  </span>
  <q-chip v-else-if="ticket.status == $constant.ticket.STATUS_OPEN" :dense="dense" class="ticket-status-chip" color="positive" icon="fiber_manual_record" padding="md" text-color="white">
    {{ $t('OPEN') }}
  </q-chip>
  <q-chip v-else-if="ticket.status == $constant.ticket.STATUS_CLOSED" :dense="dense" class="ticket-status-chip" color="default" icon="fiber_manual_record" padding="md" text-color="white">
    {{ $t('CLOSED') }}
  </q-chip>
  <span v-else>
    -
  </span>
</template>

<script>
export default {
  name: 'TicketStatusChip',
  props: {
    ticket: {
      type: Object,
      default() {
        return {}
      }
    },
    dense: Boolean
  }
}
</script>

<style lang="scss">
.ticket-status-chip {
  &.bg-positive {
    .q-chip__icon {
      color: #0f7e29;
    }
  }

  &.q-chip--dense {
    font-size: 0.75rem;
  }
}
</style>
