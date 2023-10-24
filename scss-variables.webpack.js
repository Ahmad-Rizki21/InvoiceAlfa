module.exports = function (content) {
  return content.indexOf('$') !== -1
    ? `@import 'src/css/quasar.variables.scss', 'quasar/src/css/variables.sass';\n${content}`
    : content
}
