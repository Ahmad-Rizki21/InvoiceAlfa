<template>
  <div class="page-body">
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-xs-12 dashboard-card-mini-monthly-stats">
        <div class="row q-col-gutter-md">
          <div class="col-xs-12 col-md-3">
            <card-mini-summary
              icon="credit_card"
              class="unpaid-amount"
            >
              <template #value>
                <span ref="unpaidAmountText" class="unpaid-amount-text">
                    <div class="amount bold">
                      <span class="separator">{{ $t('Unpaid') }}</span>

                      <div class="total-amount">
                        {{ $utils.currency(totalUnpaidAmount, {
                          decimal: '.',
                          thousand: ',',
                          symbol: 'Rp. '
                        }) }}
                      </div>
                    </div>

                  <div class="amount">
                    <span class="separator">{{ $t('Total') }}</span>
                    <div class="total-amount">
                      {{ $utils.currency(totalAmount, {
                        decimal: '.',
                        thousand: ',',
                        symbol: 'Rp. '
                      }) }}
                    </div>
                  </div>
                </span>
              </template>
            </card-mini-summary>
          </div>
          <div class="col-xs-12 col-md-3">
            <card-mini-summary
              :icon="cardMiniSummaryUnpaidCustomer === 'dc' ? 'business_center' : (cardMiniSummaryUnpaidCustomer == 'fr' ? 'storefront' : 'groups')"
              class="unpaid-customer"
            >
              <template #text>
                <div class="row items-center card-mini-summary-time-taken-button">
                  <span v-if="cardMiniSummaryUnpaidCustomer === 'dc'">
                    {{ $t('Unpaid {customer}', { customer: $t('Distribution Center') }) }}
                  </span>
                  <span v-else-if="cardMiniSummaryUnpaidCustomer === 'fr'">
                    {{ $t('Unpaid {customer}', { customer: $t('Franchise') }) }}
                  </span>
                  <span v-else>
                    {{ $t('Unpaid {customer}', { customer: $t('Customer') }) }}
                  </span>
                  <q-btn-group v-if="$q.screen.gt.md" flat class="q-ml-auto">
                    <q-btn
                      flat
                      :label="$t('DC')"
                      size="sm"
                      :color="cardMiniSummaryUnpaidCustomer === 'dc' ? 'default' : undefined"
                      padding="xs"
                      @click="onCardMiniSummaryUnpaidCustomerChange('dc')"
                    />
                    <q-btn
                      flat
                      :label="$t('FR')"
                      size="sm"
                      :color="cardMiniSummaryUnpaidCustomer === 'fr' ? 'default' : undefined"
                      padding="xs"
                      @click="onCardMiniSummaryUnpaidCustomerChange('fr')"
                    />
                  </q-btn-group>
                </div>

              </template>
              <template #value>
                <span class="row">
                  <div>
                    <span class="unpaid-customer">
                      {{ totalUnpaidCustomer }}
                    </span> <span class="separator">/</span> <span class="total-customer">{{ totalCustomer }}</span>
                  </div>

                  <q-btn-group v-if="$q.screen.md" flat class="q-ml-auto">
                    <q-btn
                      flat
                      :label="$t('DC')"
                      size="sm"
                      :color="cardMiniSummaryUnpaidCustomer === 'dc' ? 'default' : undefined"
                      padding="xs"
                      @click="onCardMiniSummaryUnpaidCustomerChange('dc')"
                    />
                    <q-btn
                      flat
                      :label="$t('FR')"
                      size="sm"
                      :color="cardMiniSummaryUnpaidCustomer === 'fr' ? 'default' : undefined"
                      padding="xs"
                      @click="onCardMiniSummaryUnpaidCustomerChange('fr')"
                    />
                  </q-btn-group>
                </span>
              </template>
            </card-mini-summary>
          </div>
          <div class="col-xs-12 col-md-3">
            <card-mini-summary
              :value="$utils.currency(totalPendingReview)"
              :text="$t('Total Pending Review')"
              icon="rate_review"
            />
          </div>
          <div class="col-xs-12 col-md-3">
            <card-mini-summary
              :value="$utils.currency(totalRejected)"
              :text="$t('Total Rejected')"
              icon="disabled_by_default"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="row q-col-gutter-md dashboard-chart-average-total-ticket">
      <div class="col-xs-12">
        <chart-average-timer />
      </div>
    </div>
  </div>
</template>


<script>
import { mapGetters, mapActions } from 'vuex'
import CardMiniSummary from './CardMiniSummary'
import ChartAverageTimer from './ChartAverageTimer'
import ChartTotalTicket from './ChartTotalTicket'

export default {
  name: 'PageIndex',
  meta() {
    return {
      title: this.$t('Dashboard') + ' - ' + this.$t('Web Console')
    }
  },
  breadcrumbs() {
    return ['Dashboard']
  },
  components: {
    CardMiniSummary,
    ChartAverageTimer,
    ChartTotalTicket
  },
  data() {
    return {
      cardMiniSummaryTimeTakenType: 'avg',
      cardMiniSummaryUnpaidCustomer: null,
      totalUnpaidAmount: 0,
      totalAmount: 0,
      totalUnpaidCustomer: 0,
      totalCustomer: 0,
      averageWorkingClock: 0,
      medianWorkingClock: 0,
      totalPendingReview: 0,
      totalRejected: 0,
      isTotalUnpaidAmount: true,
      isTotalUnpaidCustomerLoading: true,
      isAverageStartClockLoading: true,
      isMedianStartClockLoading: true,
      isTotalPendingReviewLoading: true,
      isTotalRejectedLoading: true,
    }
  },
  computed: {
    ...mapGetters({
      appConfig: 'app/settings'
    }),
    avgMedianStartClock() {
      if (this.cardMiniSummaryTimeTakenType === 'avg') {
        return this.averageWorkingClock
      }

      return this.medianWorkingClock
    },
    unpaidAmountResizeText() {
      let minFontSize = '1em'

      return {
        minFontSize
      }
    }
  },
  async mounted() {
    this.cardMiniSummaryTimeTakenType = this.appConfig.dashboardCardMiniSummaryTimeTakenType || 'avg'
    this.cardMiniSummaryUnpaidCustomer = this.appConfig.dashboardCardMiniSummaryUnpaidCustomer || null

    await Promise.all([
      this.getTotalUnpaidAmount(),
      this.getTotalUnpaidCustomer(),
      this.getTotalUnpaidCustomer(),
      this.getTotalPendingReview(),
      this.getTotalRejected()
    ])

    this.$nextTick(() => {
      const store = this.$store
      const finishedGroups = store.getters['tourGuide/finishedGroups']

      if (!finishedGroups.default) {
        this.$tourGuide.open('default')
      } else if (!finishedGroups.index) {
        this.$tourGuide.open('index')
      }
    })
  },
  methods: {
    ...mapActions({
      appSet: 'app/set',
      appSave: 'app/save'
    }),
    onCardMiniSummaryUnpaidCustomerChange(type) {
      if (this.cardMiniSummaryUnpaidCustomer === type) {
        type = null
      }

      this.cardMiniSummaryUnpaidCustomer = type

      this.appSet({
        dashboardCardMiniSummaryUnpaidCustomer: type
      })

      this.appSave()

      this.getTotalUnpaidCustomer()
    },
    async getTotalUnpaidAmount() {
      this.isTotalUnpaidAmount = true

      try {
        const { data } = await this.$api.get('/v1/data/total-unpaid-amount')

        if (data.status === 'success') {
          this.totalUnpaidAmount = parseFloat(data.data.unpaid, 10) || 0
          this.totalAmount = parseFloat(data.data.total, 10) || 0
        }
      } catch (err) {
        //
      }

      this.isTotalUnpaidAmount = false
    },
    async getTotalUnpaidCustomer() {
      this.isTotalUnpaidCustomerLoading = true

      try {
        const { data } = await this.$api.get('/v1/data/total-unpaid-customer', {
          params: {
            type: this.cardMiniSummaryUnpaidCustomer
          }
        })

        if (data.status === 'success') {
          this.totalUnpaidCustomer = parseFloat(data.data.unpaid, 10) || 0
          this.totalCustomer = parseFloat(data.data.total, 10) || 0
        }
      } catch (err) {
        //
      }

      this.isTotalUnpaidCustomerLoading = false
    },
    async getAverageStartClock() {
      this.isAverageStartClockLoading = true

      try {
        const { data } = await this.$api.get('/v1/data/working-clock/average')

        if (data.status === 'success') {
          this.averageWorkingClock = parseFloat(data.data.total, 10) || 0
        }
      } catch (err) {
        //
      }

      this.isAverageStartClockLoading = false
    },
    async getMedianStartClock() {
      this.isMedianStartClockLoading = true

      try {
        const { data } = await this.$api.get('/v1/data/working-clock/median')

        if (data.status === 'success') {
          this.medianWorkingClock = parseFloat(data.data.total, 10) || 0
        }
      } catch (err) {
        //
      }

      this.isMedianStartClockLoading = false
    },
    async getTotalPendingReview() {
      this.isTotalPendingReviewLoading = true

      try {
        const { data } = await this.$api.get('/v1/data/total-pending-review')

        if (data.status === 'success') {
          this.totalPendingReview = parseInt(data.data.total, 10) || 0
        }
      } catch (err) {
        //
      }

      this.isTotalPendingReviewLoading = false
    },
    async getTotalRejected() {
      this.isTotalRejectedLoading = true

      try {
        const { data } = await this.$api.get('/v1/data/total-rejected')

        if (data.status === 'success') {
          this.totalRejected = parseInt(data.data.total, 10) || 0
        }
      } catch (err) {
        //
      }

      this.isTotalRejectedLoading = false
    }
  }
}
</script>

<style lang="scss">
.page-index {
  padding-bottom: 3rem;

  >.page-body {
    padding-top: 2rem;

    .card-mini-summary-time-taken-button {
      .q-btn__wrapper {
        padding-top: 1px !important;
        padding-bottom: 0 !important;
      }
    }

    .card-mini-summary {
      height: 6rem;

      .card-mini-summary-value {
        flex: 1;
        display: flex;
        align-items: center;
      }

      .card-mini-summary-inner {
        height: 100%;
        display: flex;
        flex-direction: column;
      }

      &.unpaid-amount {
        width: 100%;

        .card-mini-summary-inner {
          overflow: hidden;
          flex: 1;
          padding-top: 0.5rem;
          padding-bottom: 0.5rem;
        }

        .card-mini-summary-value {
          font-size: 1em;
          width: 100%;

          .separator {
            margin-left: 0.15rem;
            margin-right: 0.15rem;
            font-weight: 300;
            width: 4rem;
            display: block;
            font-size: 0.6em;
            text-align: left;
          }

          .unpaid-amount-text {
            // display: flex;
            // align-items: center;
            // height: 100%;
            width: 100%;
            max-width: 85%;
          }

          .amount {
            font-weight: 400;
            font-size: 1em;
            display: flex;
            align-items: center;
            width: 100%;

            &.bold {
              font-weight: 600;
            }

            .total-amount {
              flex: 1;
              text-align: right;
            }
          }
        }
      }

      &.unpaid-customer {

        .card-mini-summary-value {
          .total-customer,
          .separator {
            font-weight: 400;
            font-size: 80%;
          }

          .separator {
            margin-left: 0.15rem;
            margin-right: 0.15rem;
          }
        }
      }
    }
  }
}
</style>
