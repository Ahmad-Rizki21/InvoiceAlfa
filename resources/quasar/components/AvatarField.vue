<template>
  <div class="avatar-field" :class="{ readonly }">
    <q-avatar :clickable="!readonly" v-ripple="!readonly" class="bg-primary text-white" :size="avatarSize" @click="onClick">
      <img v-if="preview || internalPreview" :src="preview || internalPreview" />
      <template v-else-if="initial">{{ initial }}</template>
      <!-- <q-badge floating color="teal">new</q-badge> -->
    </q-avatar>

    <q-file
      ref="file"
      v-show="false"
      v-model="internalValue"
      filled
      rounded
      label="Restricted to images"
      :max-file-size="1024 * 1024 * 2"
      :accept="acceptRules"
      @input="onChange"
      @rejected="onRejected"
    />
  </div>
</template>

<script>
export default {
  name: 'AvatarField',
  props: {
    value: {
      type: [String, Object, Array, File],
      default() {
        return null
      }
    },
    preview: {
      type: String,
      required: false
    },
    initial: {
      type: String,
      required: false
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
        return 'image/png, image/jpeg, image/jpg, image/svg+xml';
      }

      return this.accept;
    },
    avatarSize() {
      return this.size || this.internalSize
    }
  },
  methods: {
    onClick() {
      if (this.readonly) {
        return
      }
      const $file = this.$refs.file

      document.getElementById($file.inputAttrs.id).click()
    },
    onChange(file) {
      this.errorMessages = [];

      if (!(file && file.type)) {
        this.internalValue = null
        return
      }

      const reader = new FileReader();

      reader.onload = (e) => {
        this.internalPreview = e.target.result;

        this.$emit('update:preview', this.internalPreview)
        this.$emit('input', file);
      };

      reader.readAsDataURL(file);

      this.internalValue = file;
    },
    onRejected(rejectedEntries) {
      console.log(rejectedEntries)
      this.$q.notify({
        type: 'negative',
        message: `${rejectedEntries.length} file(s) did not pass validation constraints`
      })
    }
  }
}
</script>

<style lang="scss">
.avatar-field {
  > .q-avatar {
    user-select: none;
    cursor: pointer;
  }

  &.readonly {
    > .q-avatar {
      cursor: default;
    }
  }
}
</style>
