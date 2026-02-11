<template>
  <Head title="Cek Tahunan" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Cek PDRB</label>
        </div>
        <div class="p-5">
          <div class="mb-3 space-y-2">
            <label for="type">Pilih PDRB<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.type"
              placeholder="-- Pilih PDRB --"
              :options="[
                { label: 'Lapangan Usaha', value: 'Lapangan Usaha' },
                { label: 'Pengeluaran', value: 'Pengeluaran' },
              ]"
              @change="fetchYear"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Tahun<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.year"
              :options="yearDrop"
              :searchable="true"
              placeholder="-- Pilih Tahun --"
              @change="(event) => fetchQuarter(event, 'normal')"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Triwulan<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.quarter"
              :options="quarterDrop"
              :searchable="true"
              placeholder="-- Pilih Triwulan --"
              @change="(event) => fetchPeriod(event, 'normal')"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="year"
              >Pilih Periode Putaran<span class="text-danger">*</span></label
            >
            <Multiselect
              v-model="form.description"
              :options="descDrop"
              :searchable="true"
              placeholder="-- Pilih Periode Putaran --"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="year"
              >Pilih Putaran Pembanding<span class="text-danger">*</span></label
            >
            <Multiselect
              v-model="form.comparison"
              :options="descDrop"
              :searchable="true"
              placeholder="-- Pilih Periode Putaran --"
            />
          </div>
        </div>
      </div>
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="p-5">
          <div class="flex justify-end space-x-2">
            <button @click.prevent="compare" class="btn-info-fordone">
              <font-awesome-icon icon="fa fa-save" />
              Compare
            </button>
            <button
              v-if="resultList"
              @click="showResult('single')"
              class="btn-warning-fordone"
            >
              Single
            </button>
            <button
              v-if="resultList"
              @click="showResult('total')"
              class="btn-warning-fordone"
            >
              Total
            </button>
          </div>
        </div>
      </div>
      <template v-if="currentShow['single']">
        <div class="flex flex-wrap gap-2">
          <template v-for="(n, i) in resultList.single" :key="i">
            <button v-if="n.length > 0" @click="showTab(i)" :class="setActiveTab(i)">
              {{ i }}
            </button>
          </template>
        </div>
        <table class="mt-2 table shadow-md w-full mb-2">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead">Komponen</th>
              <th class="text-center align-middle">Triwulan</th>
              <th class="text-center align-middle not-fixed">Selisih ADHB</th>
              <th class="text-center align-middle not-fixed">Selisih ADHK</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(rows, subsector) in groupedRows" :key="subsector">
              <tr v-for="(r, idx) in rows" :key="subsector + '_' + r.quarter">
                <td v-if="idx === 0" :rowspan="rows.length">
                  {{ subsector }}
                </td>
                <td class="text-center">{{ r.quarter }}</td>
                <td>
                  {{ getValue(r.adhb_current, r.adhb_comparison) }}
                </td>
                <td>
                  {{ getValue(r.adhk_current, r.adhk_comparison) }}
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </template>
      <template v-if="currentShow['total']">
        <table class="mt-2 table shadow-md w-full mb-2">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead">Daerah</th>
              <th class="text-center align-middle fixed-thead">Triwulan</th>
              <th class="text-center align-middle not-fixed">Selisih ADHB</th>
              <th class="text-center align-middle not-fixed">Selisih ADHK</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(rows, daerah) in groupedTotalByDaerah" :key="daerah">
              <tr v-for="(r, idx) in rows" :key="daerah + '_' + r.year + '_' + r.quarter">
                <td v-if="idx === 0" :rowspan="rows.length" class="align-top">
                  {{ daerah }}
                </td>
                <td class="text-center">{{ r.quarter }}</td>
                <td class="text-right">
                  {{ getValue(r.adhb_current, r.adhb_comparison) }}
                </td>
                <td class="text-right">
                  {{ getValue(r.adhk_current, r.adhk_comparison) }}
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </template>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, ref, watch } from "vue";
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
const form = useForm({
  _token: null,
  type: null,
  year: null,
  quarter: null,
  description: null,
  comparison: null,
});
const yearDrop = ref([]);
const quarterDrop = ref([]);
const quarterDrop_comparison = ref([]);
const descDrop = ref([]);
const descDrop_comparison = ref([]);
const fetchYear = async (value) => {
  form.year = null;
  form.quarter = null;
  form.description = null;
  try {
    const response = await axios.get(route("period.fetchYear"), {
      params: {
        type: value,
      },
    });
    let result = response.data;
    yearDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const fetchQuarter = async (value, mode) => {
  if (mode == "comparison") {
    form.quarter_comparison = null;
    form.description_comparison = null;
  } else {
    form.quarter = null;
    form.description = null;
  }
  if (value) {
    try {
      const response = await axios.get(route("period.fetchQuarter"), {
        params: {
          type: form.type,
          year: value,
        },
      });
      let result = response.data;
      if (mode == "comparison") {
        quarterDrop_comparison.value = result;
      } else quarterDrop.value = result;
    } catch (error) {
      console.error(error);
    }
  }
};
const fetchPeriod = async (value, mode) => {
  if (mode == "comparison") {
    form.description_comparison = null;
  } else {
    form.description = null;
  }
  if (value) {
    try {
      const response = await axios.get(route("period.fetchPeriod"), {
        params: {
          type: form.type,
          year: form.year,
          quarter: value,
        },
      });
      let result = response.data;
      if (mode == "comparison") {
        descDrop_comparison.value = result;
      } else descDrop.value = result;
    } catch (error) {
      console.error(error);
    }
  }
};
const resultList = ref(null);
const comparing = ref(false);
const compare = async () => {
  try {
    const { data } = await axios.get(route("pdrb.cek-tahunan"), {
      params: {
        compare: true,
        type: form.type,
        description: form.description,
        comparison: form.comparison,
      },
    });
    resultList.value = data;
    comparing.value = true;
    showNotification(data.notification);
  } catch (error) {}
};
const props = defineProps({
  region: {
    type: Array,
    required: true,
  },
});
const activeTab = ref({});
watch(
  () => props.region,
  (r) => {
    const tabs = {};
    r.forEach((n, index) => {
      tabs[n.name] = "btn-info-fordone";
    });
    activeTab.value = tabs;
  },
  { immediate: true }
);
const setActiveTab = (name) => {
  return activeTab.value[name];
};
const currentTab = ref(null);
const showTab = (i) => {
  Object.keys(activeTab.value).forEach((k) => {
    activeTab.value[k] = "btn-info-fordone";
  });
  activeTab.value[i] = "btn-success-fordone";
  currentTab.value = i;
};
const currentRows = computed(() => {
  const single = resultList.value?.single ?? {};
  return single[currentTab.value];
});
const groupedRows = computed(() => {
  const rows = currentRows.value ?? [];
  const map = {};
  rows.forEach((r) => {
    if (!map[r.subsector]) map[r.subsector] = [];
    map[r.subsector].push(r);
  });
  Object.keys(map).forEach((subsector) => {
    map[subsector].sort((a, b) => {
      // pastikan numeric (TW 1,2,3,4)
      return Number(a.quarter) - Number(b.quarter);
    });
  });
  return map;
});
const groupedTotalByDaerah = computed(() => {
  const total = resultList.value?.total ?? {};

  // pastikan array per daerah terurut year -> quarter
  const sorted = {};
  Object.entries(total).forEach(([daerah, rows]) => {
    const safeRows = Array.isArray(rows) ? rows : [];
    sorted[daerah] = [...safeRows].sort((a, b) => {
      const y = Number(a.year) - Number(b.year);
      if (y !== 0) return y;
      return Number(a.quarter) - Number(b.quarter);
    });
  });

  return sorted;
});
const getValue = (current, comparison) => {
  let result = current - comparison;
  // if (result < 0.00001) return "~0";
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 13,
  }).format(result);
  // return result.toFixed(11);
};
const currentShow = ref({ single: false, total: false });
const showResult = (type) => {
  if (type == "single") {
    currentShow.value.single = true;
    currentShow.value.total = false;
  } else {
    currentShow.value.single = false;
    currentShow.value.total = true;
  }
};
</script>

<style scoped></style>
