<template>
  <div id="root-copy-container-omc">
    <!-- This div will be populated by the original page's script -->
    <div id="root"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';

// Paths are relative to the public directory
const cssPath = '/copied_page_assets/assets/index-z-83OdTk.css';
const jsPath = '/copied_page_assets/assets/index-FaBNeeNC.js';

let styleElement = null;
let scriptElement = null;

onMounted(() => {
  // Load CSS
  styleElement = document.createElement('link');
  styleElement.rel = 'stylesheet';
  styleElement.href = cssPath;
  document.head.appendChild(styleElement);

  // Load JS
  scriptElement = document.createElement('script');
  scriptElement.type = 'module'; // Original script was type="module"
  scriptElement.src = jsPath;
  document.body.appendChild(scriptElement);
});

onUnmounted(() => {
  if (styleElement && styleElement.parentNode) {
    styleElement.parentNode.removeChild(styleElement);
  }
  if (scriptElement && scriptElement.parentNode) {
    scriptElement.parentNode.removeChild(scriptElement);
  }
});
</script>

<style scoped>
#root-copy-container-omc {
  width: 100%;
  height: 100%;
}
</style>

