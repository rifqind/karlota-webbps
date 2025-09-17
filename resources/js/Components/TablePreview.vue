<template>
  <table class="table border-2 mb-2 w-full">
    <thead>
      <tr class="bg-info-fordone">
        <th class="tabel-width-5">#</th>
        <th>Data</th>
        <th class="tabel-width-8" v-for="data in [1, 2, 3, 4]">{{ data }}</th>
      </tr>
    </thead>
    <draggable v-model="datas" tag="tbody" item-key="label">
      <template #item="{ element, index }">
        <tr>
          <td>{{ index + 1 }}</td>
          <td>{{ element.label }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      </template>
    </draggable>
  </table>
</template>

<script setup>
import draggable from "vuedraggable";
import { ref, watch } from "vue";
const props = defineProps({
  rows: {
    type: Array,
    required: true,
  },
});
const datas = ref(props.rows);
watch(
  () => props.rows,
  (value) => {
    datas.value = value;
  }
);
</script>

<style scoped></style>
