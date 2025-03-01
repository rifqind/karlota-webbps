<template>
  <Head title="Entri Fenomena" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />

    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Entri PDRB</label>
        </div>
        <div class="p-5">
          <div class="mb-3 space-y-2">
            <label for="type">Pilih PDRB<span class="text-danger">*</span></label>
            <Multiselect v-model="form.type" :placeholder="form.type" disabled />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Tahun<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.year"
              :options="yearDrop.options"
              :searchable="true"
              placeholder="-- Pilih Tahun --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.year }}
            </div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Triwulan<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.quarter"
              :options="[
                { label: 'Triwulan 1', value: '1' },
                { label: 'Triwulan 2', value: '2' },
                { label: 'Triwulan 3', value: '3' },
                { label: 'Triwulan 4', value: '4' },
              ]"
              :searchable="true"
              placeholder="-- Pilih Triwulan --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.quarter }}
            </div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Kabupaten/Kota<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.regions"
              :options="page.props.regions"
              :searchable="true"
              placeholder="-- Pilih Kabupaten/Kota --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.regions }}
            </div>
          </div>
          <div class="flex items-center space-x-2 justify-end">
            <div
              class="btn-info-fordone ml-auto w-[130px] text-center"
              @click.prevent="submit"
            >
              <font-awesome-icon icon="fa-solid fa-magnifying-glass" />
              Cari Data
            </div>
          </div>
        </div>
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="fixed-thead">Komponen</th>
              <th>Fenomena Q-to-Q</th>
              <th>Fenomena Y-on-Y</th>
              <th>Fenomena Implisit</th>
            </tr>
          </thead>
          <template v-if="page.props.type == 'Lapangan Usaha'">
            <LapusFenomena
              :subsectors="page.props.subsectors"
              :key-data="keyData"
              :data-contents="dataContents"
            />
          </template>
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import LapusFenomena from "@/Components/LapusFenomena.vue";
import ModalBs from "@/Components/ModalBs.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { onMounted, ref, watch } from "vue";

const page = usePage();
const dataContents = ref({});
const keyData = ref({
  category: page.props.category_id,
  sector: page.props.sector_id,
  subsector: page.props.subsector_id,
});
const form = useForm({
  dataContents: null,
  _token: null,
  type: page.props.type,
  year: null,
  quarter: null,
  regions: null,
});
const formError = ref({
  year: null,
  quarter: null,
  regions: null,
});
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 11 }, (_, index) => currentYear - index);
const yearDrop = ref({
  value: null,
  options: years.map((year) => ({
    label: year.toString(),
    value: year.toString(),
  })),
});
onMounted(() => {});
const submit = async () => {
  try {
    const response = await axios.get(route("fenomena.show"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: form.quarter,
        regions: form.regions,
      },
    });
    dataContents.value = response.data.data;
  } catch (error) {}
};
</script>

<style scoped>
.fixed-thead {
  position: sticky;
  width: 400px;
  left: 0;
  background-color: #175676;
  color: whitesmoke;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.table {
  table-layout: fixed;
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  /* Avoid extra spacing */
}
</style>
