export default ({ Vue, store }) => {
  let updateId;

  function hasBreadcrumbs(vm) {
    return vm.$options.breadcrumbs !== void 0 &&
      vm.$options.breadcrumbs !== null
  }

  function triggerBreadcrumbs() {
    hasBreadcrumbs(this) === true && this.__qBreadcrumbsUpdate()
  }

  Vue.mixin({
    beforeCreate() {
      if (typeof this.$options.breadcrumbs === 'function') {
        if (this.$options.computed === void 0) {
          this.$options.computed = {}
        }
        this.$options.computed.__qBreadcrumbs = this.$options.breadcrumbs
        this.$store.dispatch('breadcrumbs/set', this.$options.breadcrumbs.call(this))
      }
      else if (hasBreadcrumbs(this) === true) {
        this.__qBreadcrumbs = this.$options.breadcrumbs
        this.$store.dispatch('breadcrumbs/set', this.$options.breadcrumbs)
      }
    },
    created() {
      if (hasBreadcrumbs(this) === true) {
        this.__qBreadcrumbsUnwatch = this.$watch('__qBreadcrumbs', this.__qBreadcrumbsUpdate)
      }
    },
    activated: triggerBreadcrumbs,
    deactivated: triggerBreadcrumbs,
    beforeMount: triggerBreadcrumbs,
    destroyed() {
      if (hasBreadcrumbs(this) === true) {
        this.__qBreadcrumbsUnwatch()
        // this.__qBreadcrumbsUpdate()
        this.$store.dispatch('breadcrumbs/set', [])
      }
    },
    methods: {
      __qBreadcrumbsUpdate() {
        clearTimeout(updateId)
        updateId = setTimeout(() => {
          this.$store.dispatch('breadcrumbs/set', this.__qBreadcrumbs.map(v => typeof v === 'string' ? v : { ...v }))
        }, 50)

      }
    }
  })
}
