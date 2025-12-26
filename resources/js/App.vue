<template>
  <Layout v-if="showLayout">
    <RouterView />
  </Layout>
  <RouterView v-else />
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { RouterView } from 'vue-router'
import Layout from './components/Layout.vue'

const route = useRoute()
const showLayout = computed(() => route.meta.layout !== false)
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background-color: #f9fafb;
}
</style>
