<template>
  <Head title="Indeks Harga" />
  <SpinnerBorder v-if="triggerSpinner" />
  <LKLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Indeks Harga
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button
          @click="form.reset()"
          class="btn-success-fordone mr-2 mb-2 lg:mb-0"
          title="Download"
        >
          <font-awesome-icon icon="fa-solid fa-circle-down" />
        </button>
        <button @click="thisDownload" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Update Indeks Harga
        </button>
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" id="this-table">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column tabel-width-5">No.</th>
            <th
              class="text-center th-order tabel-width-20"
              @click="clickToOrder('label')"
            >
              Nama Komoditas
            </th>
            <th class="text-center th-order tabel-width-10">Tahun</th>
            <th class="text-center th-order tabel-width-5">I</th>
            <th class="text-center th-order tabel-width-5">II</th>
            <th class="text-center th-order tabel-width-5">III</th>
            <th class="text-center th-order tabel-width-5">IV</th>
            <th class="text-center th-order tabel-width-8 deleted">Edit/Hapus</th>
          </tr>
          <tr>
            <td class="search-header"></td>
            <td class="search-header">
              <input
                v-model.trim="searchLabel"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <Multiselect
                v-model="searchTahun"
                :options="props.tahun"
                :searchable="true"
                mode="tags"
                placeholder="-- Pilih Tahun --"
              />
            </td>
            <td class="search-header"></td>
            <td class="search-header"></td>
            <td class="search-header"></td>
            <td class="search-header"></td>
            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
          <tr
            v-if="komoditas.length > 0"
            v-for="(data, dataIndex) in paginatedData"
            :key="data.id"
          >
            <td class="align-middle">{{ data.number }}</td>
            <td class="align-middle">{{ data.label }}</td>
            <td class="align-middle">{{ data.tahun }}</td>
            <td class="align-middle">{{ data.tw_1 }}</td>
            <td class="align-middle">{{ data.tw_2 }}</td>
            <td class="align-middle">{{ data.tw_3 }}</td>
            <td class="align-middle">{{ data.tw_4 }}</td>
            <td class="text-center align-middle deleted space-x-2">
              <a @click="updateData(data.id)"
                ><font-awesome-icon icon="fa-solid fa-pen" title="Edit" class="edit-pen"
              /></a>
            </td>
          </tr>
          <tr v-else>
            <td colspan="8" class="text-center">Data Tidak Ada</td>
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
  </LKLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import { tableToJson } from "@/download";
import LKLayout from "@/Layouts/LKLayout.vue";
import { Head } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, ref, watch } from "vue";

const props = defineProps({
  komoditas: {
    type: Object,
    required: true,
  },
  countData: {
    type: Number,
    required: true,
  },
  tahun: {
    type: Object,
    required: true,
  },
});
const komoditas = ref(props.komoditas.data);
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
const searchLabel = ref(null);
const searchTahun = ref(null);
const searchUpdatedAt = ref(null);
const ArrayBigObjects = [
  { key: "label", valueFilter: searchLabel },
  { key: "subsektor_updatedAt", valueFilter: searchUpdatedAt },
];
const currentPage = ref(1);
const showItems = ref(10);
const delayedFetchData = debounce(() => {
  fetchData();
});
watch(
  ArrayBigObjects.map((obj) => obj.valueFilter),
  () => {
    currentPage.value = 1;
    delayedFetchData();
  }
);
const updateShowItems = (value) => {
  showItems.value = value;
  fetchData();
};
const updateCurrentPage = (value) => {
  currentPage.value = value;
  fetchData();
};
const totalItems = ref(props.countData);
watch(
  () => props.countData,
  (value) => {
    totalItems.value = value;
  }
);
const paginatedData = computed(() => {
  return komoditas.value;
});
watch(
  () => props.komoditas,
  (value) => {
    komoditas.value = value;
  }
);
const fetchData = async () => {
  try {
    const response = await axios.get(route("ih.index"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          label: searchLabel.value,
          subsector_updatedAt: searchUpdatedAt.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    komoditas.value = response.data.komoditas.data;
    totalItems.value = response.data.countData;
  } catch (error) {
    console.error("Error fetching data:", error);
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
const thisDownload = () => {
  let result = tableToJson("this-table", "not-number");
  console.log(result);
};
</script>

<style scoped></style>
