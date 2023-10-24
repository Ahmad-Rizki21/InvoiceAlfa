import { debounce } from 'quasar'

export default {
  computed: {
    storedViewColumns() {
      return this.$store.getters['datatable/viewColumns'][this.$options.name] || []
    }
  },
  watch: {
    storedViewColumns: {
      immediate: true,
      handler(n, o) {
        if (Array.isArray(n) && n.length) {
          this.visibleColumns = [...n]
        }
      }
    },
    visibleColumns(n, o) {
      const storedViewColumns = this.storedViewColumns

      if (!storedViewColumns.length || (Array.isArray(n) && n.filter(v => storedViewColumns.includes(v)).length)) {
        this.storeViewColumns(n)
      }
    }
  },
  methods: {
    storeViewColumns: debounce(function (columns) {
      this.$store.dispatch('datatable/setViewColumn', {
        page: this.$options.name,
        columns
      })
    }, 1500)
  }
}
