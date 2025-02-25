<template>
  <Head title="Monitoring" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FloatScrollDown />
    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Monitoring PDRB</label>
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
              @change="fetchQuarter"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.year }}
            </div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Triwulan<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.quarter"
              :options="quarterDrop"
              :searchable="true"
              placeholder="-- Pilih Triwulan --"
              @change="fetchPeriod"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.quarter }}
            </div>
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
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.description }}
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
              <th>Kabupaten/Kota</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(node, index) in page.props.regions" :key="index">
              <tr>
                <td>{{ node.label }}</td>
                <td class="text-center">
                  <span class="badge" :class="getClass(node.value)">{{
                    getStatus(node.value)
                  }}</span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { onMounted, ref } from "vue";

const page = usePage();
const form = useForm({
  _token: null,
  type: null,
  year: null,
  quarter: null,
  description: null,
});
const formError = ref({
  year: null,
  quarter: null,
  description: null,
});
const yearDrop = ref([]);
const quarterDrop = ref([]);
const descDrop = ref([]);
const dataMonitoring = ref([]);
onMounted(() => {
  page.props.regions.forEach((element) => {
    dataMonitoring.value[element.value] = null;
  });
});
const getStatus = (region_id) => {
  const theIndex = dataMonitoring.value.findIndex((x, index) => index == region_id);
  return dataMonitoring.value[theIndex];
};
const getClass = (region_id) => {
  const theIndex = dataMonitoring.value.findIndex((x, index) => index == region_id);
  let status = dataMonitoring.value[theIndex];
  if (status == "Belum") return "badge-status-empat";
  if (status == "Entry") return "badge-info";
  if (status == "Submitted") return "badge-status-dua";
};
// #region Section: FETCHING
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
const fetchQuarter = async (value) => {
  form.quarter = null;
  form.description = null;
  if (value) {
    try {
      const response = await axios.get(route("period.fetchQuarter"), {
        params: {
          type: form.type,
          year: value,
        },
      });
      let result = response.data;
      quarterDrop.value = result;
    } catch (error) {
      console.error(error);
    }
  }
};
const fetchPeriod = async (value) => {
  form.description = null;
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
      descDrop.value = result;
    } catch (error) {
      console.error(error);
    }
  }
};
const submit = async () => {
  try {
    const response = await axios.get(route("pdrb.get-monitoring"), {
      params: {
        type: form.type,
        year: form.year,
        quarter: form.quarter,
        description: form.description,
      },
    });
    let result = response.data.monitoring;
    Object.entries(result).forEach((element) => {
      dataMonitoring.value[element[0]] = element[1].status;
    });
  } catch (error) {
    if (error.response.data.errors) {
      formError.value = Object.keys(error.response.data.errors).reduce((acc, key) => {
        acc[key] = error.response.data.errors[key][0];
        return acc;
      }, {});
    }
  }
};
// #endregion
</script>

<style scoped>
.table {
  table-layout: fixed;
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  /* Avoid extra spacing */
}
</style>
