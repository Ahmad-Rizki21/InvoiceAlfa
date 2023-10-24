<template>
  <q-field
    v-bind="fieldProps"
    class="text-editor-wrapper"
  >
    <template v-slot:control>
      <q-editor
        class="q-field__native q-placeholder"
        v-bind="textEditorProps"
        v-on="$listeners"
        :name="$attrs['name']"
        ref="editor"
        @paste.native="evt => pasteCapture(evt)"
      />
    </template>
  </q-field>
</template>

<script>
import QEditor from 'quasar/src/components/editor/QEditor'
import QField from 'quasar/src/components/field/QField'

export default {
  name: 'TextEditor',
  props: {
    ...QField.options.props,
    ...QEditor.options.props,
    paragraphTag: {
      type: String,
      default: 'div'
    },
    toolbar: {
      type: Array,
      default() {
        return []
      }
    },
    value: {
      type: [String, Number],
      required: false
    },
    readonly: {
      type: Boolean,
      default: false
    },
    borderless: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    textEditorProps() {
      const props = QEditor.options.props
      const result = {}

      for (const key in props) {
        if (key !== 'dense' && typeof this.$props[key] !== 'undefined') {
          result[key] = this.$props[key]
        }
      }

      return result
    },
    fieldProps() {
      const props = QField.options.props
      const result = {}

      for (const key in props) {
        if (key !== 'name' && typeof this.$props[key] !== 'undefined') {
          result[key] = this.$props[key]
        }
      }

      return result
    },
  },
  methods: {
    pasteCapture(evt) {
      // Let inputs do their thing, so we don't break pasting of links.
      if (evt.target.nodeName === 'INPUT') return
      let text, onPasteStripFormattingIEPaste
      evt.preventDefault()
      if (evt.originalEvent && evt.originalEvent.clipboardData.getData) {
        text = evt.originalEvent.clipboardData.getData('text/plain')
        this.$refs.editor.runCmd('insertText', text)
      }
      else if (evt.clipboardData && evt.clipboardData.getData) {
        text = evt.clipboardData.getData('text/plain')
        this.$refs.editor.runCmd('insertText', text)
      }
      else if (window.clipboardData && window.clipboardData.getData) {
        if (!onPasteStripFormattingIEPaste) {
          onPasteStripFormattingIEPaste = true
          this.$refs.editor.runCmd('ms-pasteTextOnly', text)
        }
        onPasteStripFormattingIEPaste = false
      }
    }
  }
}
</script>

<style lang="scss">
.text-editor-wrapper {
  .q-field__control {
    height: auto;
  }

  // .q-field__native {
  //   padding: 20px 0 10px;
  // }

  .q-editor__content {
    padding: 6px 0;
  }

  // &.q-field--readonly {
  //   .q-field__native {
  //     padding-top: 28px;
  //     padding-bottom: 8px;
  //   }
  // }
}
</style>
