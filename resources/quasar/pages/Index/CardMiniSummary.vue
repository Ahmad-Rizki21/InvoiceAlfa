<template>
  <q-card
    class="card-mini-summary"
    :flat="outline"
    :bordered="outline"
    :class="{ outline }"
  >
    <div class="card-mini-summary-inner">
      <div class="card-mini-summary-value">
        <slot name="value">
          {{ value }}
        </slot>
      </div>
      <div class="card-mini-summary-text">
        <slot name="text">
          {{ text }}
        </slot>
      </div>
    </div>
    <div class="card-mini-summary-icon">
      <q-avatar
        color="default"
        :size="$q.screen.gt.md ? '48px' : '36px'"
      >
        <q-icon
          :name="icon"
        />
      </q-avatar>
    </div>
  </q-card>
</template>

<script>
export default {
  name: 'CardMiniSummary',
  props: {
    value: [String, Number],
    text: String,
    icon: String,
    outline: Boolean
  }
}
</script>

<style lang="scss">
.card-mini-summary {
  display: flex;
  align-items: center;

  .card-mini-summary {
    &-inner {
      padding: 0.5rem 0rem 0.5rem 1rem;
      flex: 1;

      @media (min-width: $breakpoint-lg-min) {
        padding: 1rem 0 1rem 1rem;
        font-size: 1.2em;
      }
    }

    &-value {
      font-size: 1.5em;
      font-weight: bold;
    }

    &-text {
      font-size: 0.8em;
      font-weight: 300;
      color: #4d4d4d;
    }

    &-icon {
      margin-left: auto;
      color: #3b3b3b;
      padding: 0 1rem;
    }
  }

  &.outline {
    background-color: transparent;
  }

  &.bolden {
    // border-color: #{$primary};

    .card-mini-summary {
      &-value {
        color: #{$primary};
      }

      &-text {
        font-weight: bold;
        color: #{$primary};
      }

      &-icon {
        .q-icon {
          color: #{$primary};
        }
      }
    }
  }

  &:hover {
    .card-mini-summary {
      &-icon {
        .q-icon {
          transform: scale(1.1);
          transition: all .375s ease;
        }
      }
    }
  }
}
</style>
