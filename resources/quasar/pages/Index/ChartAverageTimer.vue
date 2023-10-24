<template>
  <q-card
    class="chart-average-time-taken"
  >
    <div class="chart-header">
      <div class="chart-title">{{ $t('Total Timer') }}</div>
      <div class="chart-description">{{ $t('Last {entity} days', { entity: 7 }) }}</div>
    </div>
    <div ref="chart" class="chart-wrapper" />
  </q-card>
</template>

<script>
import { date } from 'quasar'
import * as echarts from 'echarts/core'
import {
  DataZoomComponent,
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  MarkLineComponent,
  MarkPointComponent
} from 'echarts/components'
import { LineChart, BarChart } from 'echarts/charts'
import { UniversalTransition } from 'echarts/features'
import { SVGRenderer } from 'echarts/renderers'

echarts.use([
  BarChart,
  DataZoomComponent,
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  MarkLineComponent,
  MarkPointComponent,
  LineChart,
  SVGRenderer,
  UniversalTransition
]);

export default {
  name: 'ChartAverageTimer',
  data() {
    return {
      chart: null,
      chartOptions: {
        // title: {
        //   text: 'Temperature Change in the Coming Week'
        // },
        tooltip: {
          trigger: 'axis'
        },
        legend: {
          show: false
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: [],
          axisLabel: {
            show: this.$q.screen.gt.md || true,
            formatter: v => date.formatDate(v, 'D MMM'),
            width: 100,
            height: 30,
            overflow: 'break',
            lineOverflow: 'truncate',
            interval: 0,
            showMinLabel: false,
            align: 'right',
            rotate: 30,
            fontSize: this.$q.screen.gt.lg ? 12 : 9,
          }
        },
        yAxis: {
          type: 'value',
          axisLabel: {
            fontSize: this.$q.screen.gt.lg ? 12 : 10,
            formatter: v => this.$utils.formatSecondsToTime(v, false, false),
            rotate: 30,
          }
        },
        darkMode: this.$q.dark.mode,
        dataZoom: [
          {
            type: 'inside',
            show: true,
            xAxisIndex: [0],
            start: 0,
            end: 100
          },
        ],
        series: [
          {
            name: this.$t('Working Clock'),
            type: 'line',
            data: [],
            tooltip: {
              valueFormatter: v => this.$utils.formatSecondsToTime(v, false, false),
            }
          },
          {
            name: this.$t('Pending Clock'),
            type: 'line',
            data: [],
            tooltip: {
              valueFormatter: v => this.$utils.formatSecondsToTime(v, false, false),
            }
          }
        ]
      }
    }
  },
  mounted() {
    this.chart = echarts.init(this.$refs.chart, null, {
      renderer: 'svg'
    })

    this.chart.setOption(this.chartOptions)

    this.getChartTicketTimer()
  },
  methods: {
    async getChartTicketTimer() {
      try {
        const { data } = await this.$api.get('/v1/data/chart-ticket-timer')

        if (data.status === 'success') {
          const chartData = data.data.chart || {};
          const xAxisData = []
          const workingClockData = []
          const pendingClockData = []
          let hasValue = false

          for (const key in chartData) {
            xAxisData.push(key)
            workingClockData.push(chartData[key].working_clock)
            pendingClockData.push(chartData[key].pending_clock)

            if (chartData[key].working_clock || chartData[key].pending_clock) {
              hasValue = true
            }
          }
          const options = {
            xAxis: {
              data: xAxisData
            },
            series: [
              {
                data: workingClockData
              },
              {
                data: pendingClockData
              }
            ]
          }

          if (!hasValue) {
            options.yAxis = {
              interval: 1
            }
          } else {
            options.yAxis = {
              type: 'value'
            }
          }

          this.chart.setOption(options)
        }
      } catch (err) {}
    }
  }
}
</script>

<style lang="scss">
.chart-average-time-taken {
  position: relative;

  > .chart-header {
    position: absolute;
    top: 1rem;
    left: 1rem;

    @media (min-width: $breakpoint-lg-min) {
      display: flex;
    }

    > .chart-title {
      font-weight: bold;
    }

    > .chart-description {
      font-size: 0.8em;
      font-weight: 300;
      margin-top: -0.25rem;

      @media (min-width: $breakpoint-lg-min) {
        margin-left: 0.25rem;
      }
    }
  }
  > .chart-wrapper {
    height: 45vh;
  }
}
</style>
