<template>
  <q-timeline-entry class="form-note-item" :icon="isCreate ? 'add' : undefined" :color="isCreate ? 'positive' : undefined">
    <q-form ref="form" class="q-gutter-md" greedy @submit.prevent="onSubmit">
      <div v-if="!readonly" class="remark">
        <q-input
          v-model="formEntry.progress_message"
          :label="$t('Progress status')"
          filled
          type="textarea"
          dense
          :rules="rules.progress_message"
          :error="!!errors.progress_message"
          :error-message="errors.progress_message"
        />
      </div>
      <div v-else class="form-note-item-wrapper">
        <div class="form-note-item-header">
          <div class="form-note-item-status">
            <q-chip v-if="entry.status === $constant.ticket_timer.TIMER_OPEN" color="info" text-color="white" size="sm">
              {{ $t('Open Clock') }}
            </q-chip>
            <q-chip v-else-if="entry.status === $constant.ticket_timer.TIMER_PENDING" color="secondary" text-color="white" size="sm">
              {{ $t('Pending Clock') }}
            </q-chip>
            <q-chip v-else-if="entry.status === $constant.ticket_timer.TIMER_START" color="primary" text-color="white" size="sm">
              {{ $t('Start Clock') }}
            </q-chip>
            <q-chip v-else-if="ticket.status === $constant.ticket.STATUS_CLOSED && entry.status === 999999999" color="default" text-color="white" size="sm">
              {{ $t('Completed') }}
            </q-chip>

            <span v-if="!(ticket.status === $constant.ticket.STATUS_CLOSED && entry.status === 999999999)" class="form-note-item-diff">
              {{ $utils.formatSecondsToTime(entry.diff) }}<template v-if="entry.started_at && !entry.ended_at">+</template>
            </span>
          </div>

          <div class="form-note-item-user">
            <!-- <q-avatar size="24px" color="primary" text-color="white">
              <template v-if="entry.created_by && entry.created_by.id">
                <img v-if="entry.created_by.avatar" :src="entry.created_by.avatar">
                <template v-else>
                  {{ $utils.initials(entry.created_by.name || entry.created_by.username || entry.created_by.email) }}
                </template>
              </template>
            </q-avatar> -->

            <div class="form-note-item-username">
              {{ entry.created_by ? (entry.created_by.name || entry.created_by.username || entry.created_by.email) : '-' }}
            </div>
          </div>

          <div class="form-note-item-date">
            <time :datetime="formEntry.created_at" :title="formEntry.created_at">{{ formEntry.created_at_formatted }}</time>
          </div>

          <div class="form-note-item-action">
            <q-btn v-if="$auth.can('edit.ticket')" icon="more_vert" padding="xs" size="md" flat>
              <q-menu auto-close anchor="bottom right" self="top right">
                <q-list style="min-width: 100px">
                  <q-item clickable @click="onEdit">
                    <q-item-section>Edit</q-item-section>
                  </q-item>
                  <!-- <q-item clickable @click="onDelete">
                    <q-item-section>Delete</q-item-section>
                  </q-item> -->
                </q-list>
              </q-menu>
            </q-btn>
          </div>
        </div>
        <div class="form-note-item-body">
          {{ entry.progress_message }}
        </div>
      </div>

      <!-- <q-separator class="q-mb-sm" /> -->

      <div v-if="!readonly" class="timeline-action">
        <div class="row">
          <q-btn type="submit" color="primary" unelevated class="q-px-lg" :label="$t('Save')" />

          <q-btn flat unelevated class="q-ml-sm" :label="$t('Cancel')" @click="onCancel" />
        </div>
      </div>
    </q-form>
  </q-timeline-entry>
</template>

<script>
import { date } from 'quasar'

const DEFAULT_FORM_ENTRY = {
  progress_message: null,
}
export default {
  name: 'FormNoteItem',
  props: {
    ticket: {
      type: Object,
      default() {
        return {}
      }
    },
    entry: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      formEntry: DEFAULT_FORM_ENTRY,
      rules: {
        progress_message: [
          v => !!v || this.$t('{field} is required', { field: this.$t('Progress status') })
        ]
      },
      errors: {},
      editable: false,
      isStarted: false
    }
  },
  computed: {
    isCreate() {
      return !this.entry.id || String(this.entry.id).includes('newnote')
    },
    readonly() {
      if (this.editable) {
        return false
      }

      return !this.isCreate
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
    }
  },
  methods: {
    fill(form) {
      form = { ...DEFAULT_FORM_ENTRY, ...form };

      if (form.created_at) {
        form.created_at_formatted = date.formatDate(form.created_at, 'DD MMM YYYY HH:mm')
      }

      this.formEntry = form;
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

      const entry = {
        progress_message: this.formEntry.progress_message
      }

      this.isLoading = true;

      try {
        const endpoint = !this.isCreate ? `/v1/tickets/${this.ticket.id}/timers/${this.formEntry.id}` : `/v1/tickets/${this.ticket.id}/timers`;
        const method = !this.isCreate ? 'patch' : 'post';

        let { data } = await this.$api[method](endpoint, entry);

        if (data.status === 'success') {
          this.$emit('success', {
            entry: data.data.ticket_timer,
            old_entry: { ...this.formEntry },
            is_create: this.isCreate
          });

          this.editable = false
        }

        if (data.status === 'success') {
          this.$q.notify({ message: this.$t('{entity} saved', { entity: this.$t('Progress status') }) })
        } else {
          this.$t('Failed to save {entity}', { entity: this.$t('progress status') })
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
    onEdit() {
      this.editable = true
    },
    onCancel() {
      if (this.isCreate) {
        this.$emit('cancel', this.entry)
      } else {
        this.formEntry.progress_message = this.entry.progress_message
      }

      this.editable = false
    },
    async onDelete() {
      this.$q.dialog({
        title: this.$t('Confirm'),
        message: this.$t('Are you sure want to delete this {entity}?', { entity: this.$t('note') }),
        cancel: {
          label: this.$t('Cancel'),
          color: 'dark',
          flat: true
        },
        ok: {
          label: this.$t('Yes'),
          color: 'primary',
          unelevated: true,
          flat: true,
          class: 'text-weight-bold'
        },
        persistent: true
      }).onOk(async () => {
        this.editable = false

        try {
          let { data } = await this.$api.delete(`/v1/tickets/${this.ticket.id}/notes/${this.entry.id}`);

          if (data.status === 'success') {
            this.$emit('deleted', this.entry)
          }

          if (data.message) {
            this.$q.notify({ message: data.message })
          } else {
            if (data.status === 'success') {
              this.$q.notify({ message: this.$t('{entity} deleted', { entity: this.$t('Note') }) })
            } else {
              this.$t('Failed to delete {entity}', { entity: this.$t('note') })
            }
          }
        } catch (err) {
          this.$q.notify(err);
        }

      }).onCancel(() => {
        this.isLoading = false
        this.editable = false
      })
    }
  }
}
</script>

<style lang="scss">
.page-ticket-single .form-note-item {
  .q-timeline__title,
  .q-timeline__subtitle {
    display: none;
  }

  .form-note-item {
    &-header {
      display: flex;
      align-items: center;
      width: 100%;
      font-size: 0.859em;
      line-height: 1.25em;
      margin-bottom: 0.5rem;
    }

    &-user {
      display: flex;
      align-items: center;
      margin-left: auto;

      .q-avatar {
        margin-top: -2px;
      }
    }

    &-username {
      margin-left: 0.5rem;
      font-size: 1em;
      font-weight: 500;
    }

    &-date {
      font-size: 0.9em;
      color: #65676b;
      margin-left: 1rem;
    }

    &-action {
      margin-left: 0.5rem;
    }
  }

  .remark {
    margin-bottom: 0.5rem;
  }

  .timeline-action {
    margin-top: 0.5rem !important;
    margin-bottom: 0.75rem;
  }
}
</style>
