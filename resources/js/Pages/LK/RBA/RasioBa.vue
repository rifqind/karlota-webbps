<template>
  <Head title="Rasio BA" />
  <SpinnerBorder v-if="triggerSpinner" />
  <LKLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Rasio Biaya Antara
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" id="this-table">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column tabel-width-5">No.</th>
            <th class="text-center th-order" @click="clickToOrder('komoditas_label')">
              Nama Komoditas
            </th>
            <th
              class="text-center th-order tabel-width-10"
              @click="clickToOrder('komoditas_code')"
            >
              Kode Komoditas
            </th>
            <th
              class="text-center th-order tabel-width-10"
              @click="clickToOrder('sut_label')"
            >
              SUT
            </th>
            <th class="text-center th-order" @click="clickToOrder('rasio_ntb')">
              Rasio Biaya Antara
            </th>
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
              <input v-model.trim="searchCode" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input v-model.trim="searchSUT" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchRasio"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
          <tr
            v-if="komoditas_data.length > 0"
            v-for="(data, index) in paginatedData"
            :key="data.id"
          >
            <td class="align-middle">{{ data.number }}</td>
            <td class="align-middle">{{ data.komoditas_label }}</td>
            <td class="align-middle text-right">{{ data.komoditas_code }}</td>
            <td class="align-middle text-right">{{ data.sut_label }}</td>
            <td class="align-middle text-right">{{ data.rasio_ntb }}</td>
            <td class="text-center align-middle deleted space-x-2">
              <a @click="toggleUpdateModal(data)"
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
    <ModalBs
      :-modal-status="updateModalStatus"
      @close="
        () => {
          updateModalStatus = false;
          form.reset();
        }
      "
      :title="'Update Data'"
    >
      <template #modalBody>
        <div class="form-group space-y-3">
          <div class="space-y-2">
            <label class="font-bold">Pilih SUT :</label>
            <Multiselect
              v-model="form.sut_id"
              :options="props.sut"
              :searchable="true"
              placeholder="-- Pilih SUT --"
            />
          </div>
          <div class="space-y-2">
            <label class="font-bold">Rasio Biaya Antara</label>
            <input
              type="text"
              v-model="form.rasio_ntb"
              class="input-fordone w-full"
              placeholder="Isikan rasio biaya antara"
            />
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button class="btn btn-sm btn-warning-fordone" @click="form.reset()">
          Reset
        </button>
        <button class="btn btn-sm btn-success-fordone" @click="submit">Simpan</button>
      </template>
    </ModalBs>
  </LKLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import ModalBs from "@/Components/ModalBs.vue";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import LKLayout from "@/Layouts/LKLayout.vue";
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

const props = defineProps({
  rasioba: Object,
  countData: Number,
  sut: Object,
});

const searchLabel = ref(null);
const searchCode = ref(null);
const searchRasio = ref(null);
const searchSUT = ref(null);
const ArrayBigObjects = [
  { key: "komoditas_label", valueFilter: searchLabel },
  { key: "komoditas_code", valueFilter: searchCode },
  { key: "sut_label", valueFilter: searchSUT },
  { key: "rasio_ntb", valueFilter: searchRasio },
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
  return komoditas_data.value;
});
const komoditas_data = ref(props.rasioba.data);
watch(
  () => props.rasioba.data,
  (value) => {
    komoditas_data.value = value;
  }
);
const fetchData = async () => {
  try {
    const { data } = await axios.get(route("rba.index"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          komoditas_label: searchLabel.value,
          komoditas_code: searchCode.value,
          rasio_ntb: searchRasio.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    komoditas_data.value = data.rasioba.data;
    totalItems.value = data.countData;
  } catch (error) {
    console.error("Error Fetching Data: ", error);
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

//update
const form = useForm({
  mrn_id: null,
  _token: null,
  sut_id: null,
  rasio_ntb: null,
  komoditas_id: null,
});
const updateModalStatus = ref(false);
const toggleUpdateModal = (data) => {
  form.sut_id = data.sut_id;
  form.rasio_ntb = data.rasio_ntb;
  form.komoditas_id = data.komoditas_id;
  form.mrn_id = data.mrn_id;
  updateModalStatus.value = true;
};
const submit = async () => {
  const { token } = await axios.get(route("token"));
  form._token = token;
  try {
    if (form.mrn_id == null) {
      form.post(route("rba.store"), {
        onSuccess: (response) => {
          showNotification(response.props.notification);
          form.reset();
          fetchData();
          updateModalStatus.value = false;
        },
        onError: (errors) => {
          let errorList = [];
          if (errors?.notification) {
            errorList.push(errors?.notification);
            showNotification(errorList);
          }
        },
      });
    } else {
      form.patch(route("rba.update"), {
        onSuccess: (response) => {
          showNotification(response.props.notification);
          form.reset();
          fetchData();
          updateModalStatus.value = false;
        },
        onError: (errors) => {
          let errorList = [];
          if (errors?.notification) {
            errorList.push(errors?.notification);
            showNotification(errorList);
          }
        },
      });
    }
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped></style>
