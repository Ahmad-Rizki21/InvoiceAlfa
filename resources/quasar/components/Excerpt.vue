<template>
  <div class="excerpt">
    <template v-if="!isCalculated">
      <q-skeleton type="text" class="text-subtitle1" />
      <q-skeleton type="text" width="50%" class="text-subtitle1" />
      <q-skeleton type="text" class="text-caption" />
    </template>
    <span
      ref="content"
      class="excerpt-content"
      :style="contentStyles"
    >
      <slot />

      <div class="excerpt-content-after"></div>
    </span>
    <div v-if="canExpand" class="excerpt-expand">
      <span @click="isExpanded = !isExpanded">{{ $t(isExpanded ? 'Show less' : 'Read more') }}</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Excerpt',
  props: {
    rows: {
      type: Number,
      default: 3
    },
    lineHeight: {
      type: Number,
      default: 20
    }
  },
  data() {
    return {
      isCalculated: false,
      domHeight: 0,
      isExpanded: false,
      debouncedOnResize: this.$utils.debounce(this.calculateHeight, 350)
    }
  },
  computed: {
    canExpand() {
      return this.domHeight > (this.rows * this.lineHeight)
    },
    contentStyles() {
      if (!this.isCalculated) {
        return {
          visibility: 'hidden',
          opacity: '0',
        }
      }

      if (this.isExpanded) {
        return {}
      }

      return {
        maxHeight: (this.rows * this.lineHeight) + 'px',
        webkitLineClamp: this.rows
      }
    }
  },
  mounted() {
    this.calculateHeight()

    window.addEventListener('resize', this.debouncedOnResize)
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.debouncedOnResize)
  },
  methods: {
    calculateHeight() {
      this.isCalculated = false

      const height = this.$utils.dom.height(this.$refs.content)
      this.domHeight = height

      this.$nextTick(() => {
        this.isCalculated = true
      })
    }
  }
}
</script>

<style lang="scss">
$excerpt-rows: 3;
$excerpt-line-height: 20px;

.excerpt {
  > .excerpt-content {
    display: inline-block;
    position: relative;
    overflow: hidden;
    max-height: $excerpt-line-height * $excerpt-rows;
    line-height: $excerpt-line-height;

    > .excerpt-content-after {
      position: absolute;
      right: 0;
      bottom: 0;
      width: 100%;
      height: $excerpt-line-height;
      background: linear-gradient(to right, rgba(#fff, 0) 0%, rgba(#fff, 1) 100%);
    }

    @supports (-webkit-line-clamp: 2) {
      display: -webkit-box;
      -webkit-box-orient: vertical;

      > .excerpt-content-after {
        display: none;
      }
    }

    // &:after {
    //   content: "";
    //   position: absolute;
    //   right: 0;
    //   bottom: 0;
    //   width: 100%;
    //   height: inherit;
    //   height: $excerpt-line-height;
    //   background: linear-gradient(to right, rgba(#fff, 0) 0%, rgba(#fff, 1) 100%);
    // }

    // @supports (-webkit-line-clamp: $excerpt-rows) {
    //   display: -webkit-box;
    //   -webkit-line-clamp: $excerpt-rows;
    //   -webkit-box-orient: vertical;

    //   &:after {
    //     display: none;
    //   }
    // }
  }

  > .excerpt-expand {
    position: relative;
    font-weight: bold;
    color: #606060;

    > span {
      cursor: pointer;
      user-select: none;

      &:hover {
        text-decoration: underline;
      }
    }
  }
}
</style>
