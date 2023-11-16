<template>
  <q-card
    class="card-active-invoice"
    :class="{ 'empty-invoice': emptyInvoice, 'multiple': entries.length > 1}"
    :flat="emptyInvoice"
    :outline="emptyInvoice"
  >
    <q-card-section>
      <div class="card-label">
        <div class="card-label-text">
          Tagihan Saat Ini
        </div>

        <transition
          apprear
          enter-active-class="animated fadeIn"
          leave-active-class="animated fadeOut"
        >
          <div v-show="!emptyInvoice" class="notification-animation">
            <img src="/img/active-bill-notification.gif" alt="icon notification" />
          </div>
        </transition>

      </div>

      <div v-if="isFetching" class="bill-item fetching">
        <div class="date">
          <span><q-skeleton type="text" /></span>
          <div><q-skeleton type="rect" /></div>
        </div>
        <div class="invoice">
          <span><q-skeleton type="text" /></span>
          <div><q-skeleton type="rect" /></div>
        </div>
        <div class="status">
          <span><q-skeleton type="text" /></span>
          <div><q-skeleton type="rect" /></div>
        </div>
        <div class="due-at">
          <span><q-skeleton type="text" /></span>
          <div><q-skeleton type="rect" /></div>
        </div>
        <div class="total-bill">
          <span class="total-bill-label">
            <q-skeleton type="text" />
          </span>
          <div class="total-bill-amount">
            <q-skeleton type="rect" />
          </div>
        </div>
      </div>
      <template v-else-if="entries.length">
        <div v-for="entry in entries" class="bill-item" :key="entry.id">
          <div class="date">
            <span class="span">{{ $t('Month') }}</span>
            <span class="span-invoice">{{ entry.invoice_no }}</span>
            <div>{{ formatMonth(entry) }}</div>
          </div>
          <div class="invoice">
            <span class="span">
              Invoice

              <router-link :to="`/c/invoice/${entry.invoice_no}`">
                <q-icon
                  name="chevron_right"
                />
              </router-link>
            </span>
            <div>{{ entry.invoice_no }}</div>
          </div>
          <div class="status">
            <span class="span">{{ $t('Status') }}</span>
            <div>
              {{ entry.status_description }}
              <q-icon v-if="entry.status == $constant.invoice_status.Rejected && entry.reject_reason" name="info" size="xs" style="position: relative; top: -2px">
                <q-tooltip>{{ entry.reject_reason }}</q-tooltip>
              </q-icon>
            </div>
          </div>
          <div class="due-at">
            <span class="span">{{ $t('Due') }}</span>
            <div>{{ formatDueAt(entry) }}</div>
          </div>
          <div class="total-bill">
            <span class="span total-bill-label">
              {{ $t('Total Bill') }}
            </span>
            <div class="total-bill-amount">
              {{ formatTotal(entry) }}
            </div>
          </div>
          <div v-if="entries.length > 1" class="pay">
            <q-btn
              v-if="[$constant.invoice_status.Paid, $constant.invoice_status.Rejected, $constant.invoice_status.PendingReview].includes(entry.status)"
              color="primary"
              outline
              class="q-px-md"
              :to="`/c/pay/${entry.uid}`"
            >
              Lihat
            </q-btn>
            <q-btn
              v-else
              color="primary"
              class="q-px-md"
              :to="`/c/pay/${entry.uid}`"
            >
              Bayar
            </q-btn>
          </div>


        </div>
      </template>
      <div
        v-else
        class="empty-entry"
      >
        <div class="empty">{{ $t('You have no active bill at the moment.') }}</div>
      </div>
    </q-card-section>

    <q-card-actions v-if="isFetching || !!entries.length">
      <q-space />

      <q-skeleton
        v-if="isFetching"
        type="QBtn"
        class="q-btn-lg"
      />
      <q-btn
        v-else-if="entries.length === 1"
        flat
        class="q-btn-lg"
        :to="`/c/invoice/${firstInvoice.uid}`"
        target="_blank"
      >
        Lihat Invoice & Kwitansi
      </q-btn>

      <q-skeleton
        v-if="isFetching"
        type="QBtn"
        class="q-btn-lg q-ml-sm"
      />
      <template v-else-if="entries.length === 1">
        <q-btn
          v-if="[$constant.invoice_status.Paid, $constant.invoice_status.Rejected, $constant.invoice_status.PendingReview].includes(firstInvoice.status)"
          color="primary"
          outline
          class="q-btn-lg q-ml-sm"
          :to="`/c/pay/${firstInvoice.uid}`"
        >
          Lihat
        </q-btn>
        <q-btn
          v-else
          color="primary"
          class="q-btn-lg q-ml-sm"
          :to="`/c/pay/${firstInvoice.uid}`"
        >
          Bayar Sekarang
        </q-btn>
      </template>
    </q-card-actions>
  </q-card>
</template>

<script>
import { date } from 'quasar'

export default {
  name: 'CardActiveInvoice',
  data() {
    return {
      entries: [],
      isFetching: true,
      isLoading: false
    }
  },
  computed: {
    emptyInvoice() {
      return !this.isFetching && !this.entries.length
    },
    firstInvoice() {
      return this.entries[0]
    }
  },
  mounted() {
    this.onRequest()
  },
  methods: {
    async onRequest(props) {
      if (this.isLoading) {
        return
      }

      if (!props) {
        props = { pagination: 999999999 }
      }

      this.isFetching = true
      this.isLoading = true

      const params = {
        table_pagination: { ...(props.pagination || {}) },
      }

      try {
        const { data } = await this.$api.get('/v1/invoices/active', { params })

        if (data.status === 'success') {
          this.entries = data.data.invoices
        }
      } catch (err) {
        this.$q.notify(err)
      }

      this.isFetching = false
      this.isLoading = false
    },
    formatMonth(entry) {
      const d = date.extractDate(entry.due_at || entry.published_at, 'YYYY-MM-DD')

      return date.formatDate(d, 'MMMM')
    },
    formatDueAt(entry) {
      const d = date.extractDate(entry.due_at, 'YYYY-MM-DD')

      return date.formatDate(d, 'DD MMM YYYY')
    },
    formatTotal(entry) {
      return this.$utils.currency(entry.total, {
        decimal: '.',
        thousand: ',',
        symbol: 'Rp. '
      }) || '-'
    }
  }
}
</script>

<style lang="scss">
.card-active-invoice {
  position: relative;
  overflow: hidden;
  margin-bottom: 3rem;
  // border: 1px solid transparent;

  // &:hover {
  //   border: 1px solid $primary;
  // }

  .card-label {
    font-size: 1.2em;
    font-weight: 500;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    color: #333;
  }

  &:not(.empty-invoice) {
    .card-label {
      margin-bottom: 1.5rem;
      padding-bottom: 0.5rem;
      margin-left: -1rem;
      position: relative;

      .card-label-text {
        padding-left: 1rem;
        background-color: #{$primary};
        background-color: #efeff8;
        padding-right: 1rem;
        border-top-right-radius: 1.5rem;
        border-bottom-right-radius: 1.5rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        box-shadow: inset -1px 0px 1px 0px #dcdcdc;
        // color: #fff;
      }
    }
  }

  > .q-card__section {
    //
  }
  > .q-card__actions {
    background-color: #e9f5ee;
  }

  .notification-animation {
    // position: absolute;
    right: -3rem;
    top: -1.75rem;
    top: 0;
    right: 0;
    width: 2.5rem;
    height: 2.5rem;
    margin-top: -0.75rem;
    position: relative;
    top: 0.35rem;

    > img {
      // opacity: 0.1;
      width: 100%;
      height: 100%;
    }
  }


  .bill-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding-bottom: 1.75rem;

    > div {
      padding-left: 1.25rem;
      padding-right: 1.25rem;

      > .span,
      > .span-invoice {
        color: rgba(49, 53, 60, 0.6);
        font-size: 0.75em;
        line-height: 1;

        @media (min-width: 786px) {
          font-size: 0.9em;
        }
      }
      > .span {
        display: none;

        @media (min-width: 786px) {
          display: block;
        }
      }
      > .span-invoice {
        @media (min-width: 786px) {
          display: none;
        }
      }
      > div {
        font-weight: 600;
        font-size: 1.1em;
        line-height: 1;
      }
    }

    .date {
      // width: 9%;
      padding-left: 0;
      flex: 1;
    }

    .invoice {
      width: 34%;
      display: none;

      @media (min-width: 768px) {
        display: block;
      }
    }

    .status {
      width: 13%;
      display: none;

      @media (min-width: 768px) {
        display: block;
      }
    }

    .due-at {
      flex: 1;
      display: none;

      @media (min-width: 768px) {
        display: block;
      }
    }

    .total-bill {
      // text-align: right;
      flex: 1;

      @media (min-width: 768px) {
        min-width: 22%;
      }

      &-label {
        color: rgba(49, 53, 60, 0.6);
      }

      &-amount {
        font-weight: 900;
        line-height: 1;
        font-size: 1em;

        @media (min-width: 768px) {
          font-size: 1.72em;
        }
      }
    }

    &.fetching {
      .date {
        width: 9%;
      }
      .invoice {
        width: 28%;
      }
      .status {
        width: 16%;
      }
      .total-bill {
        width: 28%;
      }
    }
  }

  .empty-entry {
    display: flex;
    align-items: center;
    justify-content: center;

    .empty {
      padding: 2.5rem 0 5rem;
      text-align: center;
      font-style: italic;
      color: rgba(49, 53, 60, 0.6);
    }
  }

  &.multiple {
    font-size: 0.85em;

    .bill-item {
      padding-bottom: 1.5rem;

      > div {
        padding-left: 0;
        padding-right: 0;

        > div {
          font-weight: 500;
        }
      }

      .date {
        min-width: 10%;
      }

      .invoice {
        width: 30%;
      }

      .status {
        width: 15%;
      }

      .due-at {
        flex: none;
        width: 15%;
      }

      .total-bill {
        // text-align: right;
        // flex: 1;

        &-label {
          color: rgba(49, 53, 60, 0.6);
        }

        &-amount {
          font-weight: 900;
          font-size: 1.1em;
          line-height: 1;
        }
      }

      .pay {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: flex-end;

        .q-btn {
          .q-btn__wrapper {
            padding-top: 0;
            padding-bottom: 0;
            min-height: 2rem;
          }
        }
      }

      &.fetching {
        .date {
          width: 9%;
        }
        .invoice {
          width: 28%;
        }
        .status {
          width: 16%;
        }
        .total-bill {
          width: 28%;
        }
      }
    }

  }
}
</style>
