<template>
  <div class="form-notes">
    <div class="form-notes-header">
      <div class="form-notes-title text-body text-weight-bold q-py-sm">
        {{ $t('Progress') }}
      </div>

      <!-- <div class="form-notes-action">
        <q-btn
          color="primary"
          :disable="hasCreatingNote"
          outline
          icon="add"
          size="sm"
          :label="$t('Add {entity}', { entity: $t('Progress') })"
          @click.prevent="onNoteCreate"
        />
      </div> -->
    </div>

    <q-timeline color="secondary">
      <template v-if="isLoading">
        <q-timeline-entry v-for="i in 2" :key="i">
          <q-skeleton type="text" class="text-subtitle2" animation="fade" />
          <q-skeleton type="text" width="50%" class="text-subtitle2" animation="fade" />
        </q-timeline-entry>
      </template>
      <form-note-item
        v-else-if="entries.length"
        :ticket="ticket"
        :entry="note"
        v-for="note in entries"
        :key="note.id"
        @cancel="onNoteCancel"
        @success="onNoteSuccess"
        @deleted="onNoteDeleted"
      />
      <div v-else class="empty-notes">
        <span>{{ $t('No progress created, yet.') }}</span>
      </div>
    </q-timeline>
  </div>
</template>

<script>
import FormNoteItem from './FormNoteItem'

export default {
  name: 'FormNotes',
  components: {
    FormNoteItem
  },
  props: {
    ticket: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      editable: false,
      rules: {

      },
      isLoading: false,
      errors: {},
      entries: [],
      pagination: {
        sortBy: 'id',
        descending: false,
        page: 1,
        rowsPerPage: 99999,
        rowsNumber: 0
      }
    }
  },
  computed: {
    isCreate() {
      return !this.ticket.id
    },
    hasCreatingNote() {
      return Boolean(this.entries.length && this.entries.find(v => String(v.id).includes('newnote')))
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.onRequest()
    })
  },
  methods: {
    async onRequest(props) {
      if (this.isLoading) {
        return
      }

      if (!props) {
        props = { ticketId: this.ticket ? this.ticket.id : null, pagination: this.pagination }
      }

      if (!props.ticketId) {
        return
      }

      this.isLoading = true

      const params = {
        table_pagination: { ...(props.pagination || {}) },
        includes: 'createdBy'
      }

      try {
        const { data } = await this.$api.get(`/v1/tickets/${props.ticketId}/timers`, { params })

        if (data.status === 'success') {
          let ticketTimers = data.data.ticket_timers || []

          if (this.ticket.status == this.$constant.ticket.STATUS_CLOSED) {
            ticketTimers = [...ticketTimers, {
              id: 9999999999999,
              progress_message: this.ticket.closed_message,
              status: 999999999,
              created_at: this.ticket.closed_at,
              created_by_id: this.ticket.closed_by_id,
              created_by: this.ticket.closed_by || {}
            }]
          }

          this.entries = ticketTimers
          this.pagination = { ...this.pagination, ...data.pagination }
        }
      } catch (err) {
        console.error(err)
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    onNoteCreate() {
      if (this.hasCreatingNote) {
        this.$q.notify({ message: this.$t('Please save your currently creating note first.') })
        return
      }

      this.entries = [{
        id: 'newnote' + this.$utils.generateId(),
        message: '',
        editable: true
      }, ...this.entries.map(v => ({ ...v }))]
    },
    onNoteCancel(note) {
      this.entries = this.entries.filter(v => v.id !== note.id)
    },
    onNoteSuccess({ entry, old_entry, is_create }) {
      this.entries = this.entries.map(v => {
        if (is_create) {
          if (v.id == old_entry.id) {
            return entry
          }
        } else {
          if (v.id == entry.id) {
            return entry
          }
        }

        return v
      })
    },
    onNoteDeleted(entry) {
      this.entries = this.entries.filter(v => v.id !== entry.id)
    }
  }
}
</script>

<style lang="scss">
.page-ticket-single .form-notes {
  > .form-notes-header {
    margin-top: -0.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;

    > .form-notes-action {
      margin-left: auto;

      .q-icon {
        margin-right: 0.25rem;
      }
    }
  }

  .empty-notes {
    min-height: 14vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-style: italic;
    color: #929292;
    > span {
      margin-top: -1rem;
    }
  }
}
</style>
