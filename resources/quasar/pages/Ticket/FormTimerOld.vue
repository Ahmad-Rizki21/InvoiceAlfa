<template>
  <div class="form-entry form-timer q-pa-sm">
    <q-card class="q-pa-sm" :class="{ readonly }">
      <q-card-section v-if="!isCreate">
        <div class="form-timer-item">
          <span>{{ $t('Total Time Taken') }}</span>
          <div class="timer total">{{ $utils.formatSecondsToTime(totalStart) }}</div>
        </div>
        <q-separator v-if="totalStartRepair" />
        <div v-if="totalStartRepair" class="form-timer-item">
          <span>{{ $t('Total Repairing Taken') }}</span>
          <div class="timer">{{ $utils.formatSecondsToTime(totalStartRepair) }}</div>
        </div>
        <q-separator />
        <div class="form-timer-item">
          <span>{{ $t('Total Pending') }}</span>
          <div class="timer">{{ $utils.formatSecondsToTime(totalStop) }}</div>
        </div>
      </q-card-section>
      <q-card-section v-else class="empty">
        {{ $t('Create ticket to start the timer.') }}
      </q-card-section>
    </q-card>


    <q-card v-if="!isCreate && ticket.status != $constant.ticket.STATUS_CLOSED" class="q-pa-sm" :class="{ readonly }" flat>
      <q-card-actions>
        <q-btn-dropdown
          :label="$t(isStatusStarted(latestStatus) ? 'Stop' : 'Start')"
          :color="isStatusStarted(latestStatus) ? 'negative' : 'primary'"
          :loading="loading || isLoading"
          unelevated
          class="full-width"
          split
          :icon="isStatusStarted(latestStatus) ? 'stop' : 'play_arrow'"
          @click.prevent="onToggleTimer(isStatusStarted(latestStatus) ? $constant.ticket_timer.STATUS_STOP : $constant.ticket_timer.STATUS_START)"
        >
          <q-list>
            <q-item v-if="latestStatus != $constant.ticket_timer.STATUS_START_REPAIR" clickable v-close-popup>
              <q-item-section>
                <q-item-label class="text-center">
                  <q-icon size="sm" name="play_arrow" style="margin-top: -3px;"/>
                  {{ $t('Start Repairing') }}
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-separator v-if="latestStatus != $constant.ticket_timer.STATUS_START_REPAIR" />

            <q-item clickable v-close-popup @click="onTicketClose">
              <q-item-section>
                <q-item-label class="text-center">
                  <q-icon size="sm" name="stop" style="margin-top: -3px;" color="negative"/>
                  <template v-if="!isStatusStarted(latestStatus)">
                    {{ $t('Close ticket') }}
                  </template>
                  <template v-else>
                    {{ $t('Stop and close ticket') }}
                  </template>
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-card-actions>
    </q-card>

    <form-progress
      :visible.sync="isFormProgressVisible"
      :status-action="formProgressStatusAction"
      :ticket="ticket"
      :entry="formProgress"
      @success="onFormProgressSuccess"
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
      totalStart: 0,
      totalStartRepair: 0,
      totalStop: 0,
      totalStartInterval: null,
      totalStartRepairInterval: null,
      totalStopInterval: null
    }
  },
  computed: {
    isCreate() {
      return !this.ticket.id
    },
    latestStatus() {
      return this.latestTimer.id ? parseInt(this.latestTimer.status, 10) : undefined
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
    clearInterval(this.totalStartInterval)
    clearInterval(this.totalStartRepairInterval)
    clearInterval(this.totalStopInterval)
  },
  methods: {
    isStatusStarted(status) {
      const $constant = this.$constant.ticket_timer

      return [$constant.STATUS_START, $constant.STATUS_START_REPAIR].includes(status)
    },
    async getLatestTimer() {
      try {
        const { data } = await this.$api.get(`/v1/tickets/${this.ticket.id}/latest-timer`)

        if (data.status === 'success') {
          this.latestTimer = data.data.ticket_timer
          this.totalStart = parseInt(data.data.total_start, 10) || 0
          this.totalStartRepair = parseInt(data.data.total_start_repair, 10) || 0
          this.totalStop = parseInt(data.data.total_stop, 10) || 0

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

      this.formProgress = {}
      this.formProgressStatusAction = toggleStatusTo
      this.isFormProgressVisible = true;
      return

      this.isLoading = true;
      const $constant = this.$constant.ticket_timer
      if (toggleStatusTo === $constant.STATUS_START) {
        toggleStatusTo = 'start'
      } else if (toggleStatusTo === $constant.STATUS_START_REPAIR) {
        toggleStatusTo = 'start-repair'
      } else if (toggleStatusTo === $constant.STATUS_STOP) {
        toggleStatusTo = 'stop'
      }

      try {
        let { data } = await this.$api.post(`/v1/tickets/${this.ticket.id}/timers/${toggleStatusTo}`);

        if (data.status === 'success') {
          this.$emit('success');

          this.latestTimer = data.data.ticket_timer;
          this.totalStart = parseInt(data.data.total_time_taken, 10) || 0
          this.totalStop = parseInt(data.data.pending_taken, 10) || 0

          this.startTimerTicker()
        }

        if (data.message) {
          this.$q.notify({ message: data.message })
        }
      } catch (err) {
        if (err.validation) {
          this.errors = {
            ...DEFAULT_FORM_ENTRY,
            ...err.validation.errors
          }
        } else {
          this.$q.notify(err);
        }
      }

      this.isLoading = false;
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
      this.totalStart = parseInt(data.total_start, 10) || 0
      this.totalStartRepair = parseInt(data.total_start_repair, 10) || 0
      this.totalStop = parseInt(data.total_stop, 10) || 0

      this.startTimerTicker()

      this.isFormProgressVisible = false
    },
    onFormProgressCancel() {
      this.isFormProgressVisible = false
    },
    startTimerTicker() {
      clearInterval(this.totalStartInterval)
      clearInterval(this.totalStartRepairInterval)
      clearInterval(this.totalStopInterval)

      if (this.ticket.status == this.$constant.ticket.STATUS_CLOSED) {
        return
      }

      if (!this.latestTimer.id) {
        return
      }

      if (this.latestStatus === this.$constant.ticket_timer.STATUS_START) {
        this.totalStartInterval = setInterval(() => {
          this.totalStart++
        }, 1000)
      } else if (this.latestStatus === this.$constant.ticket_timer.STATUS_START_REPAIR) {
        this.totalStartInterval = setInterval(() => {
          this.totalStart++
        }, 1000)

        this.totalStartRepairInterval = setInterval(() => {
          this.totalStartRepair++
        }, 1000)
      } else if (this.latestStatus === this.$constant.ticket_timer.STATUS_STOP) {
        this.totalStopInterval = setInterval(() => {
          this.totalStop++
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

    @media (min-width: $breakpoint-lg-min) {
      font-size: 1em;
    }
  }

  hr {
    border-top: 1px solid #ddd;
    margin-bottom: 0.75rem;
  }
}
</style>
