<template>
  <router-view />
</template>

<script setup></script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body,
html,
#app {
  height: 100%;
  font-family: "Helvetica Neue", Arial, sans-serif;
  background-color: #f0f2f5;
}

#app {
  display: flex;
  flex-direction: column;
}
</style>
