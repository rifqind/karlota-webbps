<template>
  <tbody ref="tableRef">
    <template v-for="(nodeRegion, regIndex) in adjustmentVal">
      <tr>
        <td :class="regIndex < 4 ? 'font-bold' : ''" class="fixed-column">
          {{ nodeRegion.region }}
        </td>
        <template v-for="(node, index) in nodeRegion.adjVal">
          <td>{{ node }}</td>
        </template>
      </tr>
    </template>
  </tbody>
</template>

<script setup>
import { onMounted, ref } from "vue";

const props = defineProps({
  regions: {
    type: Array,
    required: true,
  },
});
const adjustmentVal = ref([]);

const createAdjVal = (region) => ({
  region,
  adjVal: {
    adhb_initial: null,
    adhb_adjust: null,
    adhb_berjalan: null,
    adhk_initial: null,
    adhk_adjust: null,
    adhk_berjalan: null,
    qtoq_initial: null,
    qtoq_berjalan: null,
    yony_initial: null,
    yony_berjalan: null,
    ctoc_initial: null,
    ctoc_berjalan: null,
    lajuimpqtoq_initial: null,
    lajuimpqtoq_berjalan: null,
    sogyony_initial: null,
    sogyony_berjalan: null,
    kontribusi_initial: null,
    kontribusi_berjalan: null,
  },
});

onMounted(() => {
  let arrayVal = [];
  props.regions.forEach((element, index) => {
    if (index == 0) {
      arrayVal = [
        createAdjVal(element.label),
        createAdjVal("Total Kabupaten/Kota"),
        createAdjVal("Selisih"),
        createAdjVal("Diskrepansi"),
      ];
    } else {
      arrayVal.push(createAdjVal(element.label));
    }
  });

  adjustmentVal.value = arrayVal; // Assign to reactive state
});
</script>

<style scoped>
.fixed-column {
  position: sticky;
  width: 400px;
  left: 0;
  background-color: white;
  color: black;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}
</style>
