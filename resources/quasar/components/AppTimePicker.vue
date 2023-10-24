<template>
  <smooth-picker
    v-show="isVisible"
    ref="smoothPicker"
    :data="data"
    :change="onChange"
  />
</template>

<script>
import { date } from 'quasar'

export default {
  name: 'AppTimePicker',
  props: {
    format24h: {
      type: Boolean,
      default: true
    },
    defaultTime: {
      type: String,
      default: '08:00'
    },
    showSecond: {
      type: Boolean,
      default: false
    }
  },
  data() {

    return {
      isVisible: false,
      options: this.buildOptions()
    }
  },
  methods: {
    buildOptions() {
      const options = [
        {
          currentIndex: parseInt((nowYear - 1991) / 2),
          flex: 3,
          list: this.generateArrayRange(this.format24h ? 24 : 12),
          textAlign: 'center',
          className: 'row-group'
        },
        {
          currentIndex: parseInt((nowYear - 1991) / 2),
          flex: 3,
          list: this.generateArrayRange(59),
          textAlign: 'center',
          className: 'row-group'
        },
      ]

      if (this.showSecond) {
        options.push({
          currentIndex: parseInt((nowYear - 1991) / 2),
          flex: 3,
          list: this.generateArrayRange(59),
          textAlign: 'center',
          className: 'row-group'
        })
      }

      return options
    },
    generateArrayRange(n) {
      return Array.apply(null, { length: n }).map(Number.call, Number)
    },
    parseTimeString(time) {
      time = String(time).split(':')

      if (typeof time[0] !== 'undefined' && typeof time[1] !== 'undefined') {
        return {
          hour: time[0],
          minute: time[1],
          second: time[2] || 0
        }
      }
    },
    onChange(gIndex, iIndex) {
      console.info('list', gIndex, iIndex)
      const ciList = this.$refs.smoothPicker.getCurrentIndexList()

      if (gIndex === 0 || gIndex === 1) { // year or month changed
        let currentIndex = 15
        let monthCount = 30

        let month = iIndex + 1 // month
        if (gIndex === 0) { // year
          month = this.data[1].list[ciList[1]]
        }
        switch (month) {
          case 2:
            let selectedYear = this.data[0].list[ciList[0]] // month
            if (gIndex === 0) { // year
              selectedYear = this.data[0].list[iIndex]
            }

            let isLeapYear = false
            if (this.isLeapYear(selectedYear)) {
              isLeapYear = true
            }

            monthCount = 28
            currentIndex = 14
            if (isLeapYear) {
              monthCount = 29
              currentIndex = 15
            }
            break
          case 4:
          case 6:
          case 9:
          case 11:
            monthCount = 30
            currentIndex = 15
            break
          default:
            monthCount = 31
            currentIndex = 16
        }
        const list = [...Array(monthCount)].map((d, i) => i + 1)
        this.$refs.smoothPicker.setGroupData(2, { ...this.data[2], ...{ currentIndex, list } })
      }
    },
  }
}
</script>
