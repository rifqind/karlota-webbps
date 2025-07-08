<template>
  <Head title="Summary" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        PDRB Lapangan Usaha, ({{ sumTime.description }}, {{ sumTime.status }},
        {{ sumTime.waktu }}, {{ sumTime.nama }})
      </div>
    </div>
    <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
      <div class="p-3">
        <div class="flex flex-wrap gap-2">
          <button @click="showTab('adhb')" :class="setActiveTab('adhb')">ADHB</button>
          <button @click="showTab('adhk')" :class="setActiveTab('adhk')">ADHK</button>
          <button @click="showTab('dist')" :class="setActiveTab('dist')">
            Distribusi
          </button>
          <button @click="showTab('qtoq')" :class="setActiveTab('qtoq')">
            Growth (Q-to-Q)
          </button>
          <button @click="showTab('yony')" :class="setActiveTab('yony')">
            Growth (Y-to-Y)
          </button>
          <button @click="showTab('ctoc')" :class="setActiveTab('ctoc')">
            Growth (C-to-C)
          </button>
          <button @click="showTab('idx')" :class="setActiveTab('idx')">
            Indeks Implisit
          </button>
          <button @click="showTab('iqtoq')" :class="setActiveTab('iqtoq')">
            Laju Implisit (Q-to-Q)
          </button>
          <button @click="showTab('iyony')" :class="setActiveTab('iyony')">
            Laju Implisit (Y-to-Y)
          </button>
        </div>
      </div>
    </div>
    <div class="overflow-x-scroll mb-2">
      <table class="table shadow-md w-full mb-2" id="tabel-entry">
        <thead>
          <tr>
            <th class="fixed-thead">Komponen</th>
            <th class="value-thead" v-for="(node, index) in tableColumn" :key="index">
              {{ node.name }}
            </th>
          </tr>
        </thead>
        <template v-if="type == 'Lapangan Usaha'">
          <SpvLapus
            :data="data"
            :regions="tableColumn"
            :subsectors="subsectors"
            :tab="currentTab"
          />
        </template>
      </table>
    </div>
  </GeneralLayout>
</template>

<script setup>
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import SpvLapus from "@/Components/SpvLapus.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  regions: {
    type: Array,
    required: true,
  },
  subsectors: {
    type: Array,
    required: true,
  },
  type: {
    type: String,
    required: true,
  },
  sumTime: {
    type: Object,
    required: true,
  },
});
const notifications = ref([]);
const tableColumn = ref([]);
var def = "btn-info-fordone";
const activeTab = ref({
  adhb: def,
  adhk: def,
  dist: def,
  qtoq: def,
  yony: def,
  ctoc: def,
  idx: def,
  iqtoq: def,
  iyony: def,
});
const setActiveTab = (value) => {
  return activeTab.value[value];
};
const resetShowTable = () => {};
const currentTab = ref("adhb");
const showTab = async (tab) => {
  Object.keys(activeTab.value).forEach((key) => {
    activeTab.value[key] = def;
  });
  activeTab.value[tab] = "btn-success-fordone";
  currentTab.value = tab;
  resetShowTable();
  if (tab == "adhb" || tab == "adhk") {
    if (tableColumn.value) tableColumn.value[0].name = "Diskrepansi";
  } else {
    if (tableColumn.value) tableColumn.value[0].name = "Selisih";
  }
};
onMounted(() => {
  showTab("adhb");
  let tempData = [];
  props.regions.forEach((element, index) => {
    if (index == 0) {
      tempData.push({ name: "Diskrepansi", value: "calculate" }, element, {
        name: "Total Kabupaten/Kota",
        value: "total",
      });
    } else {
      tempData.push(element);
    }
  });
  tableColumn.value = tempData;
});
</script>

<style scoped>
.table {
  font-size: 13px;
}

.fixed-thead {
  position: sticky;
  min-width: 300px;
  left: 0;
  background-color: #175676;
  color: whitesmoke;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.value-thead {
  min-width: 300px;
  padding: 1rem;
  background-color: #175676;
  color: whitesmoke;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.table {
  /* table-layout: fixed; */
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  /* Avoid extra spacing */
}
</style>
