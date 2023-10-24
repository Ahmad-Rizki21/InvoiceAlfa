<template>
  <q-page class="page-tickets page-ticket-single page-ticket-create">
    <portal to="app-breadcrumbs">
      <breadcrumbs />
    </portal>

    <portal to="app-toolbar">
      <q-btn flat round dense icon="arrow_back" to="/tickets" />
      <q-toolbar-title>
        {{ $t('Create New {entity}', { entity: $t('Ticket') }) }}
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
        {{ $t('Create New {entity}', { entity: $t('Ticket') }) }}
      </q-toolbar-title>

      <div class="sep" />
    </div>

    <div class="page-body">
      <div class="row">
        <div class="col-xs-12 col-md-9">
          <form-page :ticket="ticket" @success="onSuccess" />
        </div>
        <div class="col-xs-12 col-md-3">
          <form-timer :ticket="ticket" />
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import FormPage from './Ticket/FormPage'
import FormTimer from './Ticket/FormTimer'

export default {
  name: 'PageTicketCreate',
  components: {
    FormPage,
    FormTimer
  },
  meta() {
    return {
      title: this.$t('Create New {entity}', { entity: this.$t('Ticket') }) + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return [
      'home',
      { text: this.$t('Tickets'),  to: '/tickets' },
      { text: this.$t('New {entity}', { entity: this.$t('Ticket') }) },
    ]
  },
  data() {
    return {
      isLoading: false,
      ticket: {}
    }
  },
  mounted() {
    this.$nextTick(() => {
      if (!this.$store.getters['tourGuide/finishedGroups']['tickets.create']) {
        setTimeout(() => {
          this.$tourGuide.open('tickets.create')
        }, 150);
      }
    })
  },
  methods: {
    onSuccess(ticket) {
      this.$router.push(`/tickets/${ticket.id}`)
    },
  }
}
</script>

<style lang="scss">
.page-ticket-create {
  padding-bottom: 3rem;
}
</style>
