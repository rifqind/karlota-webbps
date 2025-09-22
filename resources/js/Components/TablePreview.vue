<template>
  <div class="flex items-center my-2">
    <div>
      <span class="font-bold">Judul :</span> {{ label }}<br />
      <span class="font-bold">Dinas :</span>
      {{ produsen.label }}
    </div>
    <button @click="emits('close')" class="ml-auto btn-red-fordone">Tutup Preview</button>
  </div>
  <div class="flex items-center my-2"></div>
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
  <div class="flex items-center font-bold justify-center">
    Ubah urutan baris dengan menggeser cell ke baris yang diinginkan
  </div>
</template>

<script setup>
import draggable from "vuedraggable";
import { ref, watch } from "vue";
const props = defineProps({
  rows: {
    type: Array,
    required: true,
  },
  label: {
    type: String,
    required: true,
    default: "Belum ada judul",
  },
  produsen: {
    type: Object,
    required: true,
    default: "Belum ada dinas",
  },
});
const datas = ref(props.rows);
watch(
  () => props.rows,
  (value) => {
    datas.value = value;
  }
);
defineExpose({
  datas,
});
const emits = defineEmits(["close"]);
</script>

<style scoped></style>
