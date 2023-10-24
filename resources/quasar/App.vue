<template>
  <div id="q-app">
    <!-- <div style="position: fixed;width: 100%;height:100%;top:0;left: 0;display:flex;justify-content: center;flex-direction: column;z-index: 99999999999;background-color: #fff;padding:10rem 10rem 0 20rem;overflow:scroll">
      <h1 class="text-h1">Headline1</h1>
      <h2 class="text-h2">Headline2</h2>
      <h3 class="text-h3">Headline3</h3>
      <h4 class="text-h4">Headline4</h4>
      <h5 class="text-h5">Headline5</h5>
      <h6 class="text-h6">Headline6</h6>
      <p class="text-subtitle1">Headline sub1</p>
      <p class="text-subtitle2">Headline sub2</p>
      <p class="text-body1">Headline body1</p>
      <p class="text-body2">Headline body2</p>
      <p class="text-caption">Headline caption</p>
      <p class="text-overline">Headline overline</p>
    </div> -->

    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App',
  async preFetch({ store }) {
    await store.dispatch('app/initStore')
  }
}
</script>
