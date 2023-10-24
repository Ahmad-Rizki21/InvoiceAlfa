<template>
  <q-page class="page-tickets page-ticket-single page-ticket-show">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/tickets" />
      <q-toolbar-title>
        {{ $t('Ticket') }}
      </q-toolbar-title>

      <div class="sep" />

      <q-btn
        v-if="$auth.can('create.ticket')"
        flat
        round
        dense
        icon="add"
      />
    </portal>

    <div class="page-header">
      <q-btn flat round dense icon="arrow_back" to="/tickets" />
      <q-toolbar-title>
        {{ $t('{entity} Details', { entity: $t('Ticket') }) }}
      </q-toolbar-title>

      <div class="sep" />


      <small v-if="ticket.id" class="entry-id"><span class="user-select-none">ID: #</span>{{ ticket.id }}</small>

      <div class="q-ml-lg page-ticket-show-clock-status">
        {{ $t('Clock') + ' ' + $t('Status') }}

        <ticket-timer-status-chip :ticket="ticket" :ticket-timer="latestTimer" />
      </div>
      <div class="q-ml-lg page-ticket-show-status">
        {{ $t('Ticket') + ' ' + $t('Status') }}

        <ticket-status-chip :ticket="ticket" />
      </div>
    </div>

    <div class="page-body">
      <div class="row">
        <div class="col-xs-12 col-md-9">
          <form-page :ticket="ticket" @success="onSuccess" />

          <div class="q-pa-sm q-gutter-md">
            <q-card v-if="!isCreate" class="q-pa-sm">
              <q-card-section>
                <form-notes :ticket="ticket" ref="formNotes" />
              </q-card-section>
            </q-card>
          </div>
        </div>
        <div class="col-xs-12 col-md-3">
          <form-timer :ticket="ticket" @success:ticket="onSuccess" @success="onTimerSuccess" @latest-timer:requested="onLatestTimerRequested" />
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import FormPage from './Ticket/FormPage'
import FormNotes from './Ticket/FormNotes'
import FormTimer from './Ticket/FormTimer'
import TicketStatusChip from './Ticket/TicketStatusChip'
import TicketTimerStatusChip from './Ticket/TicketTimerStatusChip'

export default {
  name: 'PageTicketShow',
  components: {
    FormPage,
    FormTimer,
    FormNotes,
    TicketStatusChip,
    TicketTimerStatusChip
  },
  meta() {
    let title = this.$t('Ticket')

    if (this.ticket && this.ticket.no) {
      title = this.$t('Ticket') + ': ' + this.ticket.no
    }

    return {
      title: title + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    const breadcrumbs = [
      'home',
      { text: this.$t('Tickets'), to: '/tickets' },
    ]

    if (this.ticket && this.ticket.no) {
      breadcrumbs.push({
        text: this.$t('{entity} Details', { entity: this.$t('Ticket') }) + ': ' + this.ticket.no
      })
    }

    return breadcrumbs
  },
  data() {
    return {
      isLoading: false,
      ticket: {},
      latestTimer: {}
    }
  },
  computed: {
    isCreate() {
      return !Boolean(this.ticket.id)
    }
  },
  async mounted() {
    await this.onRequest()
    await this.$nextTick()

    if (!this.$store.getters['tourGuide/finishedGroups']['tickets.show']) {
      this.$tourGuide.open('tickets.show')
    }
  },
  methods: {
    async onRequest(props) {
      if (this.isLoading) {
        return
      }

      if (!props) {
        props = { id: this.$route.params.id }
      }

      this.isLoading = true

      const params = {
        table_pagination: { ...(props.pagination || {}) },
        includes: 'remoteLocation.customer|createdBy|closedBy'
      }

      try {
        const { data } = await this.$api.get(`/v1/tickets/${props.id}`, { params })

        if (data.status === 'success') {
          if (data.data.ticket && data.data.ticket.remote_location && data.data.ticket.remote_location.customer_id) {
            data.data.ticket.customer_id = data.data.ticket.remote_location.customer_id
          }
          this.ticket = data.data.ticket
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isLoading = false
    },
    async onSuccess(ticket) {
      if (ticket) {
        this.ticket = { ...ticket }
      } else {
        await this.onRequest()
      }
      this.$refs.formNotes.onRequest()
    },
    onTimerSuccess() {
      this.$refs.formNotes.onRequest()
    },
    onLatestTimerRequested(latestTimer) {
      this.latestTimer = latestTimer
    }
  }
}
</script>

<style lang="scss">
.page-ticket-show {
  padding-bottom: 3rem;
}
</style>
