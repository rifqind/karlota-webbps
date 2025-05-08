<template>
  <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
    <div class="p-4">
      <template v-for="(node, index) in reportList">
        <div class="my-2">
          <div>{{ index }}</div>
          <ul v-if="node.length > 0">
            <li v-for="(child, idx) in node">{{ child }}</li>
          </ul>
          <div v-else>Tidak ada masalah</div>
        </div>
      </template>
    </div>
  </div>
</template>
<script setup>
const props = defineProps({
  reportList: {
    type: Object,
    required: true,
  },
});
</script>
