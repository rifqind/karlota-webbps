<template>
  <Head title="Daftar Putaran" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Jadwal Rekon PDRB / Putaran
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button class="btn-success-fordone mr-2 mb-2 lg:mb-0" title="Download">
          <font-awesome-icon icon="fa-solid fa-circle-down" />
        </button>
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Putaran Baru
        </button>
      </div>
    </div>
    <FlashMessage
      :toggleFlash="toggleFlash"
      @close="flashHandle"
      :flashObject="flashObject"
    />
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column">No.</th>
            <th class="text-center th-order tabel-width-20" @click="clickToOrder('type')">
              PDRB
            </th>
            <th class="text-center th-order" @click="clickToOrder('year')">Tahun</th>
            <th class="text-center th-order" @click="clickToOrder('quarter')">
              Triwulan
            </th>
            <th class="text-center th-order" @click="clickToOrder('description')">
              Periode/Putaran
            </th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('status')"
            >
              Status
            </th>
            <th
              class="text-center th-order tabel-width-10"
              @click="clickToOrder('started_at')"
            >
              Tanggal Mulai
            </th>
            <th
              class="text-center th-order tabel-width-10"
              @click="clickToOrder('ended_at')"
            >
              Tanggal Selesai
            </th>
            <th class="text-center th-order deleted">Edit/Hapus</th>
          </tr>
          <tr class="">
            <td class="search-header"></td>
            <td class="search-header">
              <input v-model.trim="searchPDRB" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchTahun"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchTriwulan"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchPutaran"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchStatus"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchTglMulai"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchTglEnd"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
          <tr v-if="periods.length > 0" v-for="data in paginatedData" :key="data.id">
            <td>{{ data.number }}</td>
            <td>{{ data.type }}</td>
            <td>{{ data.year }}</td>
            <td>{{ data.quarter }}</td>
            <td>{{ data.description }}</td>
            <td>
              <span class="badge badge-info">
                {{ data.status }}
              </span>
            </td>
            <td>{{ data.started_at }}</td>
            <td>{{ data.ended_at }}</td>
            <td class="text-center">
            <a @click="toggleUpdateModal(data.id)">
              <font-awesome-icon icon="fa-solid fa-pencil" class="edit-pen mx-2" title="Cek/Edit" />
            </a>
              <font-awesome-icon
                  icon="fa-solid fa-trash-can"
                  class="icon-trash-color mx-2"
                  title="Hapus"
                />
            </td>
          </tr>
          <tr v-else>
            <td colspan="9" class="text-center">Data Tidak Ada</td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination
      @update:currentPage="updateCurrentPage"
      @update:showItems="updateShowItems"
      :show-items="showItems"
      :total-items="totalItems"
      :current-page="currentPage"
      :current-show-items="paginatedData.length"
    />
    <ModalBs
      :ModalStatus="createModalStatus"
      @close="createModalStatus = false"
      :title="'Tambah Putaran Baru'"
      :modalSize="'min-w-[40vw]'"
      :modalPosition="'items-start pt-5'"
    >
      <template #modalBody>
        <div class="form-group">
          <div class="mb-3 space-y-2">
            <label for="pdrb">Pilih PDRB</label>
            <Multiselect
              v-model="form.type"
              :options="[
                { label: 'Lapangan Usaha', value: 'Lapangan Usaha' },
                { label: 'Pengeluaran', value: 'Pengeluaran' },
              ]"
              :searchable="true"
              placeholder="-- Pilih PDRB--"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="tahun">Pilih Tahun</label>
            <Multiselect
              v-model="form.year"
              :options="yearDrop.options"
              :searchable="true"
              placeholder="-- Pilih Tahun--"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="triwulan">Pilih Triwulan</label>
            <Multiselect
              v-model="form.quarter"
              :options="[
                { label: 'Triwulan 1', value: '1' },
                { label: 'Triwulan 2', value: '2' },
                { label: 'Triwulan 3', value: '3' },
                { label: 'Triwulan 4', value: '4' },
              ]"
              :searchable="true"
              placeholder="-- Pilih Triwulan--"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="description">Putaran</label>
            <input v-model="form.description" placeholder="Isikan Putaran ke-" class="input-fordone w-full"></input>
          </div>
          <div class="mb-3 space-y-2">
            <label for="daterange">Rentang Waktu</label>
            <vue-tailwind-datepicker v-model="form.datepicker" />  
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" class="btn-success-fordone btn-sm" @click.prevent="submit">Simpan</button>
      </template>
    </ModalBs>
  </GeneralLayout>
</template>

<script setup>
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { computed, ref, watch } from "vue";
import { debounce } from "@/debounce";
import Pagination from "@/Components/Pagination.vue";
import ModalBs from "@/Components/ModalBs.vue";
import Multiselect from "@vueform/multiselect";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";

const page = usePage();
var dataObject = page.props.period.data;
const periods = ref(dataObject);
const createModalStatus = ref(false);
const toggleFlash = ref(false);
const flashObject = ref(page.props.flash);
const triggerSpinner = ref(false);
const searchPDRB = ref(null);
const searchTahun = ref(null);
const searchTriwulan = ref(null);
const searchPutaran = ref(null);
const searchStatus = ref(null);
const searchTglMulai = ref(null);
const searchTglEnd = ref(null);
const form = useForm({
  _token: null,
  id: null,
  type: null,
  year: null,
  quarter: null,
  description: null,
  datepicker: {
    startDate: "",
    endDate: "",
  },
});

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 11 }, (_, index) => currentYear - index);
const yearDrop = ref({
  value: null,
  options: years.map((year) => ({
    label: year.toString(),
    value: year.toString(),
  })),
});
watch(
  () => page.props.flash,
  (value) => {
    flashObject.value = value;
  }
);
const flashHandle = () => {
  toggleFlash.value = false;
  flashObject.value = {
    message: null,
    error: null,
  };
};
const ArrayBigObjects = [
  { key: "type", valueFilter: searchPDRB },
  { key: "year", valueFilter: searchTahun },
  { key: "quarter", valueFilter: searchTriwulan },
  { key: "description", valueFilter: searchPutaran },
  { key: "status", valueFilter: searchStatus },
  { key: "started_at", valueFilter: searchTglMulai },
  { key: "ended_at", valueFilter: searchTglEnd },
];
//watch filter
watch(
  ArrayBigObjects.map((obj) => obj.valueFilter),
  function () {
    currentPage.value = 1;
    delayedFetchData();
  }
);
const delayedFetchData = debounce(() => {
  fetchData();
});

//paginated
const showItems = ref(10);
const currentPage = ref(1);
const updateShowItems = (value) => {
  showItems.value = value;
  fetchData();
};
const updateCurrentPage = (value) => {
  currentPage.value = value;
  fetchData();
};
const totalItems = ref(page.props.countData);
watch(
  () => page.props.countData,
  (value) => {
    totalItems.value = value;
  }
);
const paginatedData = computed(() => {
  return periods.value;
});
watch(
  () => page.props.period.data,
  (value) => {
    periods.value = value;
  }
);

//fetchData
const fetchData = async () => {
  try {
    const response = await axios.get(route("period.index"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          type: searchPDRB.value,
          year: searchTahun.value,
          quarter: searchTriwulan.value,
          description: searchPutaran.value,
          status: searchStatus.value,
          started_at: searchTglMulai.value,
          ended_at: searchTglEnd.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    console.log(response.data);
    periods.value = response.data.period.data;
    totalItems.value = response.data.countData;
  } catch (error) {
    console.error("Error fetching data: ", error);
  }
};

//order specific
const orderAttribute = ref({
  before: null,
  label: null,
  value: "asc",
});
const clickToOrder = (value) => {
  orderAttribute.value.label = value;
  if (orderAttribute.value.before == null || orderAttribute.value.before == value) {
    if (orderAttribute.value.value == "asc") orderAttribute.value.value = "desc";
    else if (orderAttribute.value.value == "desc") orderAttribute.value.value = null;
    else orderAttribute.value.value = "asc";
  } else orderAttribute.value.value = "asc";
  orderAttribute.value.before = value;
  fetchData();
};

//post function
const submit = async function () {
  const response = await axios.get(route("token"));
  form._token = response.data;
  if (form.processing) return;
  form.post(route('period.store'), {
    onBefore: () => {
      triggerSpinner.value=false
      createModalStatus.value=false
    },
    onFinish: () => {
      triggerSpinner.value=false
    },
    onSuccess: () => {
      if (flashObject.value) toggleFlash.value=true
      form.reset()
      fetchData()
    },
    onError: () => {
      createModalStatus.value=true
    }
  })
};
</script>

<style scoped></style>
