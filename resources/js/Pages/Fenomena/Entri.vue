<template>
  <Head title="Entri Fenomena" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />

    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Entri Fenomena</label>
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
                { label: 'Tahunan', value: '5' },
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
              <th v-if="!isYear">Fenomena Q-to-Q</th>
              <th>Fenomena Y-on-Y</th>
              <th>Fenomena Implisit</th>
            </tr>
          </thead>
          <LapusFenomena
            v-if="page.props.type == 'Lapangan Usaha'"
            v-show="showTabPanel"
            :subsectors="page.props.subsectors"
            :data-contents="dataContents"
            :fenomena-status="fenomenasets.status"
            :is-year="isYear"
            @update:update-data-contents="updateDataContents"
            @update:handle-input="handleInput"
            @update:handle-paste="handlePaste"
            @update:set-default-data="setDefaultData"
          />
          <PengFenomena
            v-if="page.props.type == 'Pengeluaran'"
            v-show="showTabPanel"
            :subsectors="page.props.subsectors"
            :data-contents="dataContents"
            :fenomena-status="fenomenasets.status"
            :is-year="isYear"
            @update:update-data-contents="updateDataContents"
            @update:handle-input="handleInput"
            @update:handle-paste="handlePaste"
          />
        </table>
      </div>
      <div
        v-if="showTabPanel"
        class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3"
      >
        <div class="p-5">
          <div class="flex justify-end space-x-2">
            <button
              v-if="fenomenasets.status != 'Submitted'"
              @click.prevent="saveEntri"
              class="btn-info-fordone"
            >
              <font-awesome-icon icon="fa fa-save" />
              Simpan Data
            </button>
            <button
              v-if="fenomenasets.status == 'Entry'"
              @click.prevent="submitEntri"
              class="btn-success-fordone"
            >
              <font-awesome-icon icon="fa fa-check" />
              Submit Data
            </button>
            <button
              v-if="fenomenasets.status == 'Submitted'"
              @click.prevent="unsubmitEntri"
              class="btn-red-fordone"
            >
              <font-awesome-icon icon="fa-solid fa-circle-xmark" />
              Unsubmit Data
            </button>
          </div>
        </div>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import LapusFenomena from "@/Components/LapusFenomena.vue";
import PengFenomena from "@/Components/PengFenomena.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, onMounted, ref, watch } from "vue";

const page = usePage();
const dataContents = ref({});
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
const isYear = ref(false);
const defaultData = ref([]);
const showTabPanel = ref(false);
const fenomenasets = ref({ status: "Entry" });
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
const updateDataContents = (value) => {
  dataContents.value = value;
};
const handleInput = (value) => {
  dataContents.value[value.theIndex][value.type] = value.value;
};
const setDefaultData = (value) => {
  defaultData.value = value;
};
const handlePaste = (value) => {
  dataContents.value[value.theIndex][value.type] = value.value;
};
onMounted(() => {});
const submit = async () => {
  try {
    dataContents.value = JSON.parse(JSON.stringify(defaultData.value));
    const response = await axios.get(route("fenomena.show"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: form.quarter,
        regions: form.regions,
      },
    });

    response.data.data.forEach((element) => {
      const theIndex = dataContents.value.findIndex((x) => {
        return (
          x.category_id == element.category_id &&
          x.sector_id == element.sector_id &&
          x.subsector_id == element.subsector_id
        );
      });
      if (theIndex !== -1) {
        dataContents.value[theIndex].id = element.id;
        dataContents.value[theIndex].fenomena_sets = element.fenomena_sets;
        dataContents.value[theIndex].qtoq = element.qtoq;
        dataContents.value[theIndex].yony = element.yony;
        dataContents.value[theIndex].implisit = element.implisit;
      }
    });
    showTabPanel.value = true;
    fenomenasets.value = response.data.fenomena_set;
    formError.value = {
      year: null,
      quarter: null,
      regions: null,
    };
    isYear.value = form.quarter == 5 ? true : false;
  } catch (error) {
    if (error.response.data.errors) {
      formError.value = Object.keys(error.response.data.errors).reduce((acc, key) => {
        acc[key] = error.response.data.errors[key][0];
        return acc;
      }, {});
    }
  }
};
const saveEntri = async () => {
  const thisForm = useForm({
    dataContents: dataContents.value,
    type: page.props.type,
    fenomena_sets: fenomenasets.value.id,
    _token: null,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("fenomena.save-fenomena"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
    },
  });
};
const submitEntri = async () => {
  const thisForm = useForm({
    dataContents: dataContents.value,
    type: page.props.type,
    id: fenomenasets.value.id,
    _token: null,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("fenomena.submit-fenomena"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
      if (response.props.notification[0].type == "success")
        fenomenasets.value.status = "Submitted";
    },
  });
};
const unsubmitEntri = async () => {
  const thisForm = useForm({
    id: fenomenasets.value.id,
    type: page.props.type,
    _token: null,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("fenomena.unsubmit-fenomena"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
      if (response.props.notification[0].type == "success")
        fenomenasets.value.status = "Entry";
    },
  });
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
