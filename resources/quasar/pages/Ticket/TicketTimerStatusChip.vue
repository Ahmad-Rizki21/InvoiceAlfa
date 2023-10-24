<template>
  <span v-if="!(ticketTimer && ticketTimer.id)">
    -
  </span>
  <q-chip v-else-if="ticket.status == $constant.ticket.STATUS_CLOSED" :dense="dense" class="ticket-timer-status-chip" color="default" icon="fiber_manual_record" padding="md" text-color="white">
    {{ $t('COMPLETED') }}
  </q-chip>
  <q-chip v-else-if="ticketTimer.status == $constant.ticket_timer.TIMER_OPEN" :dense="dense" class="ticket-timer-status-chip" color="info" icon="fiber_manual_record" padding="md" text-color="white">
    {{ $t('OPEN') }}
  </q-chip>
  <q-chip v-else-if="ticketTimer.status == $constant.ticket_timer.TIMER_PENDING" :dense="dense" class="ticket-timer-status-chip" color="secondary" icon="fiber_manual_record" padding="md" text-color="white">
    {{ $t('PENDING') }}
  </q-chip>
  <q-chip v-else-if="ticketTimer.status == $constant.ticket_timer.TIMER_START" :dense="dense" class="ticket-timer-status-chip" color="primary" icon="fiber_manual_record" padding="md" text-color="white">
    {{ $t('STARTED') }}
  </q-chip>
  <span v-else>
    -
  </span>
</template>

<script>
export default {
  name: 'TicketTimerStatusChip',
  props: {
    ticket: {
      type: Object,
      default() {
        return {}
      }
    },
    ticketTimer: {
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
.ticket-timer-status-chip {
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
