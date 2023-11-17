<template>
  <div class="signature-pad">
    <div class="signature-pad-body">
      <canvas ref="canvas"></canvas>
    </div>
    <div class="signature-pad-action">
      <q-btn
        color="white"
        text-color="dark"
        @click="onClear"
      >
        {{ $t('Clear') }}
      </q-btn>
    </div>
  </div>
</template>

<script>
import SignaturePad from 'signature_pad'

export default {
  name: 'SignaturePad',
  props: {
    value: String,
    url: String,
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      instance: null,
      onWindowResizeDebounced: this.$utils.debounce(this.onWindowResize, 350)
    }
  },
  watch: {
    readonly(n, o) {
      if (n !== o) {
        if (n) {
          this.instance.on()
        } else {
          this.instance.off()
        }
      }
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.instance = new SignaturePad(this.$refs.canvas)

      if (this.readonly) {
        this.instance.off()
      }

      window.addEventListener('resize', this.onWindowResizeDebounced)
      this.onWindowResize()
    })
  },
  beforeDestroy() {
    if (this.instance) {
      this.instance.off()
    }

    window.removeEventListener('resize', this.onWindowResizeDebounced)
  },
  methods: {
    onWindowResize() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const canvas = this.$refs.canvas
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);
      this.instance.clear();
    },
    onClear() {
      this.instance.clear()
    },
    isEmpty() {
      return this.instance.isEmpty()
    },
    toDataUrl() {
      return this.instance.toDataURL()
    },
    toBlob() {
      return this.dataUrlToBlob(this.toDataUrl())
    },
    dataUrlToBlob(dataURL) {
      const parts = dataURL.split(';base64,');
      const contentType = parts[0].split(":")[1];
      const raw = window.atob(parts[1]);
      const rawLength = raw.length;
      const uInt8Array = new Uint8Array(rawLength);

      for (let i = 0; i < rawLength; ++i) {
        uInt8Array[i] = raw.charCodeAt(i);
      }

      return new Blob([uInt8Array], { type: contentType });
    }
  }
}
</script>

<style lang="scss">
.signature-pad {
  height: 12rem;
  position: relative;

  &-body {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &-action {
    position: absolute;
    bottom: 0.75em;
    right: 0.25rem;
  }

  canvas {
    width: 25rem;
    height: 100%;
    background-color: white;
  }
}
</style>
