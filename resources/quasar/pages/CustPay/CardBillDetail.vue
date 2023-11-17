<template>
  <q-card
    flat
    class="card-bill-detail"
  >
    <q-card-section>
      <div class="row-detail invoice-no">
        <div class="key">
          <q-skeleton v-if="fetching" type="text" />
          <template v-else>
            {{ $t('Invoice No') }}
          </template>
        </div>
        <div class="value">
          <q-skeleton v-if="fetching" type="rect" />
          <template v-else>
            {{ invoice.invoice_no }}
          </template>
        </div>
      </div>
      <div class="row-detail">
        <div class="key">
          <q-skeleton v-if="fetching" type="text" />
          <template v-else>
            {{ $t('Receipt No') }}
          </template>
        </div>
        <div class="value">
          <q-skeleton v-if="fetching" type="rect" />
          <template v-else>
            {{ invoice.receipt_no }}
          </template>
        </div>
      </div>
      <!-- <div class="row-detail">
        <div class="key">
          {{ $t('Sub Total') }}
        </div>
        <div class="value">
          {{ formatMoney(invoice.sub_total) }}
        </div>
      </div>
      <div class="row-detail">
        <div class="key">
          {{ $t('VAT') }} (PPN {{ invoice.ppn_percentage }}%)
        </div>
        <div class="value">
          {{ formatMoney(invoice.ppn_total) }}
        </div>
      </div>
      <div class="row-detail">
        <div class="key">
          {{ $t('Stamp Duty') }}
        </div>
        <div class="value">
          {{ formatMoney(invoice.stamp_duty) }}
        </div> -->
      </div>
      <div class="row-detail">
        <div class="key">
          <q-skeleton v-if="fetching" type="text" />
          <template v-else>
            {{ $t('Total') }}
          </template>
        </div>
        <div class="value font-weight-bold">
          <q-skeleton v-if="fetching" type="rect" />
          <template v-else>
            {{ formatMoney(invoice.total) }}

            <q-btn
              size="sm"
              flat
              rounded
              padding="sm"
              icon="content_copy"
              class="btn-copy"
              @click="onTotalCopy(invoice.total)"
            />
          </template>
        </div>
      </div>
      <div class="row-detail">
        <div class="key">
          <q-skeleton v-if="fetching" type="text" />
          <template v-else>
            {{ $t('Due date') }}
          </template>
        </div>
        <div class="value font-weight-bold">
          <q-skeleton v-if="fetching" type="rect" />
          <template v-else>
            {{ formatDate(invoice.due_at) }}
          </template>
        </div>
      </div>

      <div class="row-detail status" :class="{ [`invoice-status-${invoice.status}`]: true }">
        <div class="key">
          <q-skeleton v-if="fetching" type="rect" />
          <template v-else>
            {{ $t('status') }}
          </template>
        </div>
        <div class="value" :class="{ [`status-color-${invoice.status}`]: true }">
          <template v-if="!fetching">
            {{ invoice.status_description }}
          </template>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script>
import { date } from 'quasar'

export default {
  name: 'CardBillDetail',
  props: {
    invoice: {
      type: Object,
      default() {
        return {}
      }
    },
    fetching: Boolean
  },
  methods: {
    formatMonth(entry) {
      const d = date.extractDate(entry.due_at || entry.published_at, 'YYYY-MM-DD')

      return date.formatDate(d, 'MMMM')
    },
    formatDate(value) {
      const d = date.extractDate(value, 'YYYY-MM-DD')

      return date.formatDate(d, 'DD MMMM YYYY')
    },
    formatMoney(value) {
      return value ? this.$utils.currency(value || 0, {
        decimal: '.',
        thousand: ',',
        symbol: 'Rp. '
      }) : 'Rp. -'
    },
    formatTotal(entry) {
      return this.$utils.currency(entry.total, {
        decimal: '.',
        thousand: ',',
      }) || '-'
    },
    onTotalCopy(num) {
      this.$utils.copyToClipboard(num)
      this.$q.notify({ message: this.$t('{entity} copied to clipboard', { entity: this.$t('Payment amount') }) })
    }
  }
}
</script>

<style lang="scss">
.card-bill-detail {
  .q-skeleton {
    width: 60%;
    min-width: 2.5rem;
    margin-bottom: 0.5rem;
  }

  > .q-card__section {
    position: relative;
    padding-bottom: 0.25rem;

    .font-weight-bold {
      font-weight: 500;
    }

    > .row-detail {
      display: flex;
      font-size: 0.9em;
      margin-bottom: 0.5rem;

      > .key {
        width: 25%;
        color: rgba(49, 53, 60, 0.6);
        font-size: 0.9em;
      }

      > .value {
        flex: 1;
        font-size: 1.1em;
        // font-weight: 500;
      }

      &:last-child {
        margin-bottom: 0;
      }

      // &.invoice-no {
      //   > .value {
      //     // font-weight: 500;
      //     // color: var(--q-text-color-default);
      //   }
      // }

      &.status {
        position: absolute;
        right: 1.5rem;
        top: 1rem;

        > .key {
          width: 3rem;
          line-height: 1.35;
        }

        > .value {
          line-height: 1;
          font-weight: 900;
          color: var(--q-text-color-default);
          flex: none;
          font-size: 1.25em;
          // min-width: 10rem;
          border-bottom: 1px dashed;
        }

        &.invoice-status {
          &-1 > .value {
            color: #808080;
            border-bottom-color: #cfd8dc;
          }
          &-2 > .value  {
            color: $warning;
            border-bottom-color: #fffde7;
          }
          &-3 > .value  {
            border-bottom-color: #f2c037;
            border-bottom-color: var(--q-color-warning);
          }
          &-4 > .value  {
            color: $positive;
            border-bottom-color: #21ba45;
            border-bottom-color: var(--q-color-positive);
          }
          &-5 > .value  {
            color: $negative;
            border-bottom-color: #c10015;
            border-bottom-color: var(--q-color-negative);
          }
        }
      }
    }

    .btn-copy {
      .q-icon {
        color: #5d5d5d;
      }
    }
  }
}
</style>
