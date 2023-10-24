<template>
  <div class="form-entry form-timer q-pa-sm">
    <q-card class="q-pa-sm q-mb-sm" :class="{ readonly }">
      <q-card-section v-if="!isCreate">
        <div class="form-timer-item open">
          <span><span v-if="isStatusOpen" class="dot"></span> {{ $t('Open Clock') }}</span>
          <div class="timer">{{ $utils.formatSecondsToTime(openClock) }}</div>
        </div>
        <div class="form-timer-item pending">
          <span><span v-if="isStatusPending" class="dot"></span> {{ $t('Pending Clock') }}</span>
          <div class="timer pending">{{ $utils.formatSecondsToTime(pendingClock) }}</div>
        </div>
        <div class="form-timer-item start">
          <span><span v-if="isStatusStart" class="dot"></span> {{ $t('Start Clock') }}</span>
          <div class="timer">{{ $utils.formatSecondsToTime(startClock) }}</div>
        </div>

      </q-card-section>
      <q-card-section v-else class="empty">
        {{ $t('Create ticket to start the clock timer.') }}
      </q-card-section>

      <q-card-actions v-if="!isCreate && ticket.status != $constant.ticket.STATUS_CLOSED">
        <q-btn
          v-bind="buttonProps"
          :loading="loading || isLoading"
          unelevated
          class="full-width btn-form-timer-ticket"
          @click.prevent="onToggleTimer(buttonProps.nextStatus)"
        />
      </q-card-actions>
    </q-card>

    <form-progress
      :visible.sync="isFormProgressVisible"
      :status-action="formProgressStatusAction"
      :ticket="ticket"
      :entry="formProgress"
      @success="onFormProgressSuccess"
      @success:ticket="onFormProgressSuccessTicket"
      @cancel="onFormProgressCancel"
    />
  </div>
</template>

<script>
import { date } from 'quasar'
import FormProgress from './FormProgress'

const DEFAULT_FORM_ENTRY = {
  name: null,
  location: null,
  status: 1
}

export default {
  name: 'FormTimer',
  components: {
    FormProgress
  },
  props: {
    ticket: {
      type: Object,
      default() {
        return {}
      }
    },
    loading: Boolean,
    closable: {
      type: Boolean,
      default: true
    },
    entry: {
      type: Object,
      default() {
        return DEFAULT_FORM_ENTRY
      }
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      formPage: DEFAULT_FORM_ENTRY,
      isLoading: false,
      isFormProgressVisible: false,
      formProgressStatusAction: null,
      formProgress: {},
      rules: {
        title: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Name') })
        ],
        content: [
          //
        ]
      },
      errors: DEFAULT_FORM_ENTRY,
      latestTimer: {},
      openClock: 0,
      pendingClock: 0,
      startClock: 0,
      openClockInterval: null,
      pendingClockInterval: null,
      startClockInterval: null
    }
  },
  computed: {
    isCreate() {
      return !this.ticket.id
    },
    latestStatus() {
      return this.latestTimer.id ? parseInt(this.latestTimer.status, 10) : undefined
    },
    isStatusOpen() {
      return this.latestStatus === this.$constant.ticket_timer.TIMER_OPEN
    },
    isStatusPending() {
      return this.latestStatus === this.$constant.ticket_timer.TIMER_PENDING
    },
    isStatusStart() {
      return this.latestStatus === this.$constant.ticket_timer.TIMER_START && this.ticket.status == this.$constant.ticket.STATUS_OPEN
    },
    buttonProps() {
      const $constant = this.$constant.ticket_timer

      if (this.isStatusOpen) {
        return {
          label: this.$t('Pause'),
          color: 'secondary',
          icon: 'pause',
          nextStatus: $constant.TIMER_PENDING
        }
      }

      if (this.isStatusPending) {
        return {
          label: this.$t('Start'),
          color: 'primary',
          icon: 'play_arrow',
          nextStatus: $constant.TIMER_START
        }
      }

      if (this.isStatusStart) {
        return {
          label: this.$t('Complete'),
          color: 'positive',
          icon: 'done',
          nextStatus: true
        }
      }

      return {
        label: this.$t('Start'),
        color: 'info',
        icon: 'play_arrow',
        nextStatus: $constant.TIMER_OPEN
      }
    }
  },
  watch: {
    ticket: {
      immediate: true,
      deep: true,
      handler(n, o) {
        if (n && n.id) {
          this.getLatestTimer()
        }
      }
    },
    entry: {
      immediate: true,
      handler(n) {
        this.$nextTick(() => {
          this.fill(n)
        })
      }
    }
  },
  beforeDestroy() {
    clearInterval(this.openClockInterval)
    clearInterval(this.pendingClockInterval)
    clearInterval(this.startClockInterval)
  },
  methods: {
    async getLatestTimer() {
      try {
        const { data } = await this.$api.get(`/v1/tickets/${this.ticket.id}/latest-timer`)

        if (data.status === 'success') {
          this.latestTimer = data.data.ticket_timer
          this.openClock = parseInt(data.data.open_clock, 10) || 0
          this.pendingClock = parseInt(data.data.pending_clock, 10) || 0
          this.startClock = parseInt(data.data.start_clock, 10) || 0

          this.$emit('latest-timer:requested', data.data.ticket_timer)
          this.startTimerTicker()
        }
      } catch (err) {
        //
      }
    },
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form };

      if (form.created_at) {
        form.created_at_formatted = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
      }

      form.status = Boolean(parseInt(form.status, 10))

      this.formPage = form;
    },
    async onToggleTimer(toggleStatusTo) {
      if (this.loading || this.isLoading) {
        return;
      }

      if (toggleStatusTo === 'true') {
        this.onTicketClose()
      } else {
        this.formProgress = {}
        this.formProgressStatusAction = toggleStatusTo
        this.isFormProgressVisible = true;
      }

    },
    cancel() {
      if (this.loading || this.isLoading) {
        return;
      }

      this.$emit('cancel');
    },
    onFormProgressSuccess(data) {
      this.$emit('success', data)

      this.latestTimer = data.ticket_timer
      this.openClock = parseInt(data.open_clock, 10) || 0
      this.pendingClock = parseInt(data.pending_clock, 10) || 0
      this.startClock = parseInt(data.start_clock, 10) || 0

      this.$emit('latest-timer:requested', data.ticket_timer)

      this.startTimerTicker()

      this.isFormProgressVisible = false
    },
    onFormProgressSuccessTicket() {
      this.$emit('success:ticket')
    },
    onFormProgressCancel() {
      this.isFormProgressVisible = false
    },
    startTimerTicker() {
      clearInterval(this.openClockInterval)
      clearInterval(this.pendingClockInterval)
      clearInterval(this.startClockInterval)

      if (this.ticket.status == this.$constant.ticket.STATUS_CLOSED) {
        return
      }

      if (!this.latestTimer.id) {
        return
      }

      const latestStatus = this.latestStatus

      const $constant = this.$constant.ticket_timer;

      if (latestStatus === $constant.TIMER_OPEN) {
        this.openClockInterval = setInterval(() => {
          this.openClock++
        }, 1000)
      } else if (latestStatus === $constant.TIMER_PENDING) {
        this.pendingClockInterval = setInterval(() => {
          this.pendingClock++
        }, 1000)
      } else if (latestStatus === $constant.TIMER_START) {
        this.startClockInterval = setInterval(() => {
          this.startClock++
        }, 1000)
      }
    },
    async onTicketClose() {
      if (this.loading || this.isLoading) {
        return
      }

      const shouldContinue = await new Promise((resolve) => {
        this.$q.dialog({
          title: this.$t('Confirm'),
          message: this.$t('Are you sure want to turn status to be CLOSED?'),
          cancel: true,
          persistent: true
        }).onOk(() => {
          resolve(true)
        }).onCancel(() => {
          resolve(false)
        }).onDismiss(() => {
          resolve(false)
        })
      })

      if (!shouldContinue) {
        return
      }

      this.isLoading = true;

      try {
        let { data } = await this.$api.patch(`/v1/tickets/${this.ticket.id}`, {
          status: this.$constant.ticket.STATUS_CLOSED,
        });

        if (data.status === 'success') {
          this.$emit('success:ticket', data.data.ticket);
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        } else {
          if (data.status === 'success') {
            this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Ticket') }) })
          } else {
            this.$t('Failed to save {entity}', { entity: this.$t('ticket') })
          }
        }
      } catch (err) {
        this.$q.notify(err);
      }

      this.isLoading = false;
    }
  }
}
</script>

<style lang="scss" scoped>
.form-timer {
  .form-timer {
    &-item {
      display: flex;
      align-items: center;
      width: 100%;
      margin-bottom: 0.5rem;

      > span {
        font-size: 0.8em;
        color: rgba(0, 0, 0, 0.6);
        position: relative;
      }

      .dot {
        width: 0.25rem;
        height: 0.25rem;
        background-color: #3355dd;
        display: block;
        border-radius: 50%;
        position: absolute;
        left: -0.5rem;
        top: 33.3333%;
        box-shadow: 0px 0px 2px 1px #5371e7;
        animation: 1s infinite alternate dot-shadow;
      }

      > .timer {
        text-align: right;
        flex: 1;
        font-size: 1.2em;
        font-weight: 500;

        &.total {
          font-size: 1.85em;
          font-weight: bold;
          margin-top: -0.25rem;
        }


        &.text-weight-bold {
          font-weight: bold;
          font-size: 1.3em;
        }
      }

      &.open {
        // font-weight: 300;
        // font-size: 0.8rem;
        // display: inline-block;
        // text-align: right;

        > .timer {
          // font-size: inherit;
          // font-weight: inherit;
          // display: inline;
          // margin-left: 0.25rem;
        }
      }

      &.start {
        > .timer {
          // font-size: 1.65em;
          // line-height: 1;
        }
      }

      &.pending {
        > .timer {

        }
      }

    }
  }

  .empty {
    min-height: 24vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-style: italic;
    color: #929292;
    font-size: 0.8rem;
    text-align: center;

    @media (min-width: $breakpoint-lg-min) {
      font-size: 1em;
    }
  }

  hr {
    border-top: 1px solid #ddd;
    margin-bottom: 0.75rem;
  }

  @keyframes dot-shadow {
    from {
      box-shadow: 0px 0px 2px 1px #5371e7;
    }

    to {
      box-shadow: 0px 0px 20px 1px #5371e7;
    }
  }
}
</style>
