<template>
  <Head title="Master SUT" />
  <SpinnerBorder v-if="triggerSpinner" />
  <LKLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Master SUT
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah SUT Baru
        </button>
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column tabel-width-5">No.</th>
            <th class="text-center th-order" @click="clickToOrder('label')">Rows</th>
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
            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
          <tr v-if="sut.length > 0" v-for="data in paginatedData" :key="data.id">
            <td>{{ data.number }}</td>
            <td>{{ data.label }}</td>
            <td class="text-center">
              <a @click="toggleUpdateModal(data.id)">
                <font-awesome-icon
                  icon="fa-solid fa-pencil"
                  class="edit-pen mx-2"
                  title="Cek/Edit"
                />
              </a>
              <a @click="deleteUpdateModal(data.id)">
                <font-awesome-icon
                  icon="fa-solid fa-trash-can"
                  class="icon-trash-color mx-2"
                  title="Hapus"
                />
              </a>
            </td>
          </tr>
          <tr v-else>
            <td colspan="3" class="text-center">Data Tidak Ada</td>
          </tr>
        </tbody>
      </table>
    </div>
  </LKLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import LKLayout from "@/Layouts/LKLayout.vue";
import { Head } from "@inertiajs/vue3";
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
const createModalStatus = ref(false);
const props = defineProps({
  sut: Object,
  countData: Number,
});
var sutObject = props.sut.data;
const sut = ref(sutObject);

//search
const searchLabel = ref(null);
const ArrayBigObjects = [{ key: "label", valueFilter: searchLabel }];
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
const totalItems = ref(props.countData);
watch(
  () => props.countData,
  (value) => {
    totalItems.value = value;
  }
);
const paginatedData = computed(() => {
  return sut.value;
});
const fetchData = async () => {
  try {
    const response = await axios.get(route("rba.master-sut"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          label: searchLabel.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    rows.value = response.data.row.data;
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
