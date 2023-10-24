<template>
  <q-dialog
    v-model="isFormEntryVisible"
    :persistent="!isFormEntryReadonly"
  >
    <q-card class="form-entry q-pa-sm" :class="{ readonly }">
      <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
        <div v-if="!readonly" class="text-h6 q-px-md q-pt-sm">
          {{ statusActionTitle }}
        </div>
        <q-card-section>
          <div class="row q-col-gutter-sm">
            <div class="col-xs-12">
              <q-input
                v-model="formEntry.progress_message"
                :label="$t('Progress Status')"
                type="textarea"
                name="progress_message"
                :filled="!readonly"
                :borderless="readonly"
                :readonly="readonly"
                dense
                :rules="rules.progress_message"
                :error="!!errors.progress_message"
                :error-message="errors.progress_message"
              />
            </div>
            <template v-if="readonly">
              <div class="col-xs-12">
                <q-input
                  :value="formEntry.created_at"
                  :label="$t('Created At')"
                  borderless
                  readonly
                  dense
                />
              </div>
              <div class="col-xs-12">
                <q-input
                  :value="entry.created_by ? (entry.created_by.name || entry.created_by.username || entry.created_by.email) : '-'"
                  :label="$t('Created At')"
                  borderless
                  readonly
                  dense
                />
              </div>
            </template>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions v-if="readonly" align="right">
          <q-btn
            :label="$t('Close')"
            flat
            :disable="loading || isLoading"
            @click="cancel"
          />
        </q-card-actions>
        <q-card-actions v-else align="right">
          <q-btn
            v-if="currentStatus === $constant.ticket_timer.TIMER_PENDING && false"
            :label="$t('Skip Pending Clock')"
            flat
            color="negative"
            :disable="loading || isLoading"
            @click="onSkipPending"
          />
          <q-space />
          <q-btn
            :label="$t('Cancel')"
            flat
            :disable="loading || isLoading"
            @click="cancel"
          />
          <q-btn
            type="submit"
            v-bind="submitButtonProps"
            :loading="loading || isLoading"
            unelevated
            class="q-px-lg"
          />
        </q-card-actions>
      </q-form>
    </q-card>

  </q-dialog>
</template>


<script>
import { date } from 'quasar'

const DEFAULT_FORM_ENTRY = {
  name: null,
  location: null,
  status: 1
}

export default {
  name: 'FormProgress',
  props: {
    loading: Boolean,
    closable: {
      type: Boolean,
      default: true
    },
    ticket: {
      type: Object,
      default() {
        return {}
      }
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
    },
    statusAction: {
      type: [String, Number, Boolean]
    },
    visible: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isFormEntryVisible: false,
      isFormEntryReadonly: false,
      formEntry: DEFAULT_FORM_ENTRY,
      isLoading: false,
      rules: {
        progress_message: [
          v => ((this.statusAction === this.$constant.ticket_timer.TIMER_OPEN || this.statusAction === true) || !!v) || this.$t('{field} is required', { field: this.$t('Progress status') })
        ],
        location: [
          //
        ]
      },
      errors: DEFAULT_FORM_ENTRY
    }
  },
  computed: {
    isCreate() {
      return !this.formEntry.id
    },
    currentStatus() {
      return parseInt(this.statusAction, 10)
    },
    statusActionTitle() {
      const status = parseInt(this.statusAction, 10)
      const $constant = this.$constant.ticket_timer

      if (status == $constant.TIMER_OPEN) {
        return this.$t('Start Clock')
      }

      if (status == $constant.TIMER_PENDING) {
        return this.$t('Pending Clock')
      }

      if (status == $constant.TIMER_START) {
        return this.$t('Start Clock')
      }

      return this.$t('Close Ticket')
    },
    submitButtonProps() {
      const status = parseInt(this.statusAction, 10)
      const $constant = this.$constant.ticket_timer

      if (status == $constant.TIMER_OPEN) {
        return {
          color: 'info',
          icon: 'play_arrow',
          label: this.$t('Start')
        }
      }
      if (status == $constant.TIMER_PENDING) {
        return {
          color: 'secondary',
          icon: 'pause',
          label: this.$t('Pending')
        }
      }
      if (status == $constant.TIMER_START) {
        return {
          color: 'primary',
          icon: 'play_arrow',
          label: this.$t('Start')
        }
      }
      return {
        color: 'positive',
        icon: 'done',
        label: this.$t('Close Ticket')
      }
    }
  },
  watch: {
    entry: {
      immediate: true,
      handler(n) {
        this.$nextTick(() => {
          this.fill(n)
        })
      }
    },
    visible(n, o) {
      if (n !== o && n !== this.isFormEntryVisible) {
        this.isFormEntryVisible = n
      }
    },
    isFormEntryVisible(n, o) {
      if (n !== o && n !== this.visible) {
        this.$emit('update:visible', n)
      }
    }
  },
  methods: {
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form };

      if (form.created_at) {
        form.created_at = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
      }

      form.status = Boolean(parseInt(form.status, 10))

      this.formEntry = form;

      this.$nextTick(() => {
        this.$refs.form && this.$refs.form.resetValidation();
      })
    },
    async onSubmit() {
      if (this.loading || this.isLoading) {
        return;
      }

      if (this.readonly) {
        return
      }

      const isValid = await this.$refs.form.validate();

      if (!isValid) {
        return;
      }

      const entry = { ...this.formEntry }

      this.isLoading = true;

      const toggleStatusTo = parseInt(this.statusAction, 10)
      entry.status = toggleStatusTo

      try {
        const { data } = await this.$api.post(`/v1/tickets/${this.ticket.id}/timers/${this.statusAction !== true ? 'toggle' : 'complete'}`, entry);

        if (data.status === 'success') {
          this.$emit('success', data.data);

          if (this.statusAction === true) {
            this.$emit('success:ticket')

            this.$q.notify({ message: data.message })
          }
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
    onSkipPending() {

    }
  }
}
</script>

<style lang="scss" scoped>
.form-entry {
  @media (min-width: $breakpoint-sm-min) {
    max-width: 875px !important;
  }
}
</style>
