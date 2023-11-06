<template>
  <div
    v-if="!(!addable && empty)"
    class="bill-attachment"
    :class="{ readonly, empty }"
    v-ripple="!readonly"
    v-show="!(readonly && empty)"
    @click="onWrapperClick"
  >
    <template v-if="preview || internalPreview">
      <a v-if="url" :href="url" target="_blank" class="attachment-image">
        <img :src="preview || internalPreview" />
      </a>
      <div v-else class="attachment-image">
        <img :src="preview || internalPreview" />
      </div>
    </template>
    <div v-else class="icon-add" @click="onClick">
      <q-icon
        name="add_a_photo"
      />
    </div>

    <div v-if="(!readonly && !empty && !url) || (editable && !empty)" class="action">
      <q-btn
        flat
        icon="cancel"
        :ripple="false"
        @click.prevent="onDelete"
      />
    </div>

    <div v-if="uploading" class="upload-loading">
      <q-spinner-dots
        color="primary"
        size="2em"
      />
    </div>

    <!-- <q-avatar :clickable="!readonly" v-ripple="!readonly" class="bg-primary text-white" :size="avatarSize" @click="onClick">
      <img v-if="preview || internalPreview" :src="preview || internalPreview" />
      <template v-else-if="initial">{{ initial }}</template>
    </q-avatar> -->

    <q-file
      ref="file"
      v-show="false"
      v-model="internalValue"
      filled
      rounded
      label="Restricted to images"
      :max-file-size="maxFileSize"
      :accept="acceptRules"
      @input="onChange"
      @rejected="onRejected"
    />
  </div>
</template>

<script>
export default {
  name: 'BillAttachment',
  props: {
    value: {
      type: [String, Object, Array, File],
      default() {
        return null
      }
    },
    url: {
      type: String,
      default: null
    },
    preview: {
      type: String,
      required: false
    },
    initial: {
      type: String,
      required: false
    },
    addable: {
      type: Boolean,
      default: true
    },
    editable: {
      type: Boolean,
      default: false
    },
    accept: {
      type: String,
      required: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    size: {
      type: String,
      required: false
    },
    maxFileSize: {
      type: Number
    },
    minDimension: {
      type: Number
    },
    uploading: {
      type: Boolean
    }
  },
  data() {
    return {
      internalSize: '64px',
      internalValue: null,
      internalPreview: null,
      errorMessages: []
    }
  },
  computed: {
    acceptRules() {
      if (!this.accept) {
        return 'image/png, image/jpeg, image/jpg, image/svg+xml, image/heic, image/heif';
      }

      return this.accept;
    },
    avatarSize() {
      return this.size || this.internalSize
    },
    empty() {
      return !(this.preview || this.internalPreview)
    }
  },
  methods: {
    onWrapperClick(e) {
      if (this.url) {
        this.$emit('click', e)
      }
    },
    onClick() {
      if (this.readonly) {
        return
      }
      const $file = this.$refs.file

      document.getElementById($file.inputAttrs.id).click()
    },
    async onChange(file) {
      this.errorMessages = [];

      if (!(file && file.type)) {
        this.internalValue = null
        return
      }

      const isDimensionValid = await this.checkDimension(file)

      if (!isDimensionValid) {
        return
      }

      this.internalPreview = await this.generateFilePreview(file)
      this.internalValue = file;

      this.$emit('update:preview', this.internalPreview)
      this.$emit('input', file);
    },
    generateFilePreview(file) {
      return new Promise((resolve) => {
        const reader = new FileReader();

        reader.onload = (e) => {
          resolve( e.target.result)
        };

        reader.readAsDataURL(file);
      })
    },
    async checkDimension(file) {
      const isDimensionValid = await this.validateDimension(file)

      if (!isDimensionValid) {
        this.onRejected([{
          failedPropValidation: 'max-dimension',
          file
        }])
      }

      return isDimensionValid
    },
    async validateDimension(file, minDimension) {
      const _URL = window.URL || window.webkitURL

      if (!(_URL && _URL.createObjectURL && 'Image' in window)) {
        return Promise.resolve(true)
      }

      minDimension = minDimension || this.minDimension

      return new Promise((resolve) => {
        try {
          const img = new Image()
          const objectUrl = _URL.createObjectURL(file)

          img.onload = () => {
            const width = img.width
            const height = img.height

            console.log(minDimension)

            _URL.revokeObjectURL(objectUrl)

            if (!(width >= minDimension && height >= minDimension)) {
              resolve(false)
            } else {
              resolve(true)
            }
          }

          img.src = objectUrl
        } catch (err) {
          resolve(false)

          try {
            _URL.revokeObjectURL(objectUrl)
          } catch (_) {}
        }
      })
    },
    onRejected(rejectedEntries) {
      let errMsg = this.$t('Selected file is not accepted. Please use another one.')
      let prop

      if (rejectedEntries && rejectedEntries[0] && rejectedEntries[0].failedPropValidation) {
        prop = rejectedEntries[0].failedPropValidation

        if (prop === 'accept') {
          errMsg = this.$t('The selected file is unsupported. Supported files: {entity}', { entity: '.png, .jpg, .jpeg, .svg, .heic' })
        } else if (prop === 'max-file-size') {
          errMsg = this.$t('File size exceeds maximum limit: {max}', { max: this.$utils.humanStorageSize(this.maxFileSize) })
        } else if (prop === 'max-dimension') {
          errMsg = this.$t('Image resolution must be greater than {resolution}', { resolution: `${this.minDimension}x${this.minDimension}` })
        }
      }

      this.$q.notify({
        type: 'negative',
        message: errMsg
      })
    },
    onDelete() {
      this.internalValue = null
      this.internalPreview = null

      this.$emit('deleted')
    }
  }
}
</script>

<style lang="scss">
.bill-attachment {
  width: 6rem;
  height: 6rem;
  border: 1px solid rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;

  &.empty {
    border-style: dashed;
  }

  > .attachment-image,
  > .icon-add {
    background-color: rgba(0, 0, 0, 0.1);
    width: calc(100% - 0.5rem);
    height: calc(100% - 0.5rem);
  }

  > .icon-add {
    display: flex;
    align-items: center;
    justify-content: center;

    > .q-icon {
      font-size: 3rem;
      color: rgba(0, 0, 0, 0.2);
    }
  }

  .attachment-image {
    overflow: hidden;

    > img {
      width: 100%;
      height: auto;
      min-height: 1px;
    }
  }

  > .q-avatar,
  > .attachment-image {
    user-select: none;
    cursor: pointer;
  }

  > .action {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease;

    &:hover {
      background-color: rgba(255, 255, 255, 0.8);
    }

    .q-btn {
      .q-focus-helper {
        display: none;
      }
    }
  }

  > .upload-loading {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.6);
  }

  &.readonly {
    > .q-avatar,
    > .attachment-image {
      cursor: default;
    }
  }
}
</style>
