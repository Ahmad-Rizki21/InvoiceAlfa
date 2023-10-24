<template>
  <q-breadcrumbs class="breadcrumbs" gutter="xs">
    <template #separator>
      <q-icon size="xs" name="chevron_right" />
    </template>

    <template v-for="(item, i) in breadcrumbs">
      <template v-if="item.type === 'home' || item.type === 'dashboard'">
        <q-breadcrumbs-el to="/" :key="i">
          <q-icon class="dashboard" name="home" />
        </q-breadcrumbs-el>
      </template>
      <template v-else>
        <q-breadcrumbs-el :to="item.to" :exact="item.exact" :disable="item.disabled" :key="i">
          {{ item.text }}
        </q-breadcrumbs-el>
      </template>
    </template>
  </q-breadcrumbs>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'Breadcrumbs',
  props: {
    items: {
      type: Array,
      default() {
        return []
      }
    }
  },
  data() {
    return {
      breadcrumbs: []
    }
  },
  computed: {
    ...mapGetters({
      storedBreadcrumbs: 'breadcrumbs/breadcrumbs'
    }),
    breadcrumbItems() {
      if (this.items.length) {
        return this.items
      }

      return this.storedBreadcrumbs
    },
  },
  watch: {
    breadcrumbItems: {
      immediate: true,
      deep: true,
      handler (n) {
        this.generateBreadcrumbs(n)
      }
    },
    $breadcrumbs(n, p) {
      this.$nextTick(() => {
        this.$forceUpdate();
      })
    }
  },
  methods: {
    generateBreadcrumbs(items) {
      this.breadcrumbs = items.map(v => {
        if (typeof v === 'string') {
          return {
            type: v
          }
        }

        return v
      })
    }
  }
}
</script>

<style lang="scss">
.breadcrumbs {
  .q-breadcrumbs__el {
    font-size: 0.75em;

    > .q-icon {
      line-height: 0.25;

      &.dashboard {
        position: relative;
        font-size: 1.5em;
        margin-right: 1px;
      }
    }
  }
  .q-breadcrumbs__separator {
    margin: 0;
  }
}
</style>
