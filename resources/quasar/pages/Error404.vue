<template>
 <q-page class="page-error">
    <div class="content">
      <div class="code">
        404
      </div>

      <div class="text">
        Oops. Nothing here...
      </div>
    </div>
  </q-page>
</template>

<script>
export default {
  name: 'ErrorNotFound'
}
</script>

<style lang="scss">
.page-error {
  display: flex;
  align-items: center;
  justify-content: center;

  .content {
    margin-top: -3em;
    text-align: center;
    font-size: 2em;

    .code {
      font-size: 2.25em;
    }
    .text {
      opacity: 0.8;
    }
  }
}
</style>
