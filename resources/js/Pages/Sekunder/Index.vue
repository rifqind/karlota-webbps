<template>
  <Head title="Daftar Data" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Daftar Data Sekunder
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button
          @click="form.reset()"
          class="btn-success-fordone mr-2 mb-2 lg:mb-0"
          title="Download"
        >
          <font-awesome-icon icon="fa-solid fa-circle-down" />
        </button>
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Data Sekunder
        </button>
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column tabel-width-5">No.</th>
            <th class="text-center th-order" @click="clickToOrder('p.nama')">
              Nama Dinas
            </th>
            <th class="text-center th-order" @click="clickToOrder('s.label')">
              Judul Data Sekunder
            </th>
            <th
              class="text-center th-order"
              @click="clickToOrder('status_sekunder.tahun')"
            >
              Tahun
            </th>
            <th
              class="text-center th-order"
              @click="clickToOrder('status_sekunder.status')"
            >
              Status
            </th>
            <th
              class="text-center th-order"
              @click="clickToOrder('status_sekunder.updated_at')"
            >
              Terakhir di-update
            </th>
            <th class="text-center th-order tabel-width-8 deleted">Edit/Hapus</th>
          </tr>
          <tr>
            <td class="search-header"></td>
            <td class="search-header">
              <input
                v-model.trim="searchNamaDinas"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchLabel"
                type="text"
                class="input-fordone w-full"
              />
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
                v-model.trim="searchStatus"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchUpdated"
                type="text"
                class="input-fordone w-full"
              />
            </td>

            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
          <tr v-if="sekunder.length > 0" v-for="data in paginatedData" :key="data.id">
            <td class="align-middle">{{ data.number }}</td>
            <td class="align-middle">{{ data.nama_dinas }}</td>
            <td class="align-middle">{{ data.label_data }}</td>
            <td class="align-middle">
              <span class="badge badge-info">{{ data.tahun }}</span>
            </td>
            <td class="align-middle">
              <span class="badge badge-info">{{ data.status }}</span>
            </td>
            <td class="align-middle text-center">
              <span class="badge badge-info">{{ data.username }}</span>
              <br />
              <span>{{ data.updated_time }}</span>
            </td>
          </tr>
          <tr v-else>
            <td colspan="7" class="text-center">Data Tidak Ada</td>
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
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import ModalBs from "@/Components/ModalBs.vue";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const page = usePage();
var dataObject = page.props.sekunder.data;
const sekunder = ref(dataObject);
const createModalStatus = ref(false);
const deleteModalStatus = ref(false);

const formError = ref([]);
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};

//fetch series
const searchLabel = ref(null);
const searchNamaDinas = ref(null);
const searchTahun = ref(null);
const searchStatus = ref(null);
const searchUpdated = ref(null);
const ArrayBigObjects = [
  { key: "label_data", valueFilter: searchLabel },
  { key: "nama_dinas", valueFilter: searchNamaDinas },
  { key: "tahun", valueFilter: searchTahun },
  { key: "status", valueFilter: searchStatus },
  { key: "updated_at", valueFilter: searchUpdated },
];
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
  return sekunder.value;
});
watch(
  () => page.props.sekunder.data,
  (value) => {
    sekunder.value = value;
  }
);
const fetchData = async () => {
  try {
    const response = await axios.get(route("sekunder.index"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          label_data: searchLabel.value,
          nama_dinas: searchNamaDinas.value,
          tahun: searchTahun.value,
          status: searchStatus.value,
          updated_at: searchUpdated.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    sekunder.value = response.data.sekunder.data;
    totalItems.value = response.data.countData;
  } catch (error) {
    console.error("Error fetching data: ", error);
  }
};
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
</script>

<style scoped></style>
