<template>
  <Head title="Permasalahan Aplikasi" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Permasalahan Aplikasi
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Ajukan Permasalahan
        </button>
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column">No.</th>
            <th
              class="text-center th-order tabel-width-20"
              @click="clickToOrder('problem')"
            >
              Permasalahan
            </th>
            <th class="text-center th-order" @click="clickToOrder('location')">Lokasi</th>
            <th class="text-center th-order" @click="clickToOrder('fix')">
              Fix/Solusi dari Admin
            </th>
            <th class="text-center th-order" @click="clickToOrder('username')">User</th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('updated_at')"
            >
              Tanggal
            </th>
            <th class="text-center th-order deleted" v-if="page.props.auth.user == 'niu'">
              Edit/Hapus
            </th>
          </tr>
          <tr class="">
            <td class="search-header"></td>
            <td class="search-header">
              <input
                v-model.trim="searchProblem"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchLocation"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input v-model.trim="searchFix" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input v-model.trim="searchUser" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input v-model.trim="searchDate" type="text" class="input-fordone w-full" />
            </td>
            <td
              v-if="page.props.auth.user.name == 'niu'"
              class="search-header deleted"
            ></td>
          </tr>
        </thead>
        <tbody>
          <tr v-if="questions.length > 0" v-for="data in paginatedData" :key="data.id">
            <td>{{ data.number }}</td>
            <td>{{ data.location }}</td>
            <td>{{ data.problem }}</td>
            <td>{{ data.fix }}</td>
            <td>
              <span class="badge badge-info">
                {{ data.username }}
              </span>
            </td>
            <td>{{ data.time }}</td>
            <td v-if="page.props.auth.user.name == 'niu'" class="text-center">
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
      @close="
        () => {
          createModalStatus = false;
          isUpdate == true ? form.reset() : null;
          isUpdate = false;
          modalTitle = 'Ajukan Permasalahan Baru';
        }
      "
      :title="modalTitle"
      :modalSize="'min-w-[40vw]'"
      :modalPosition="'items-start pt-5'"
    >
      <template #modalBody>
        <div class="form-group">
          <div class="mb-3 space-y-2">
            <label for="pdrb">Lokasi Bug/Error/Permasalahan</label>
            <Multiselect
              v-model="form.location"
              :options="[
                { label: 'Entri PDRB', value: 'Entri PDRB' },
                { label: 'Adjustment', value: 'Adjustment' },
                { label: 'Hasil Adjustment', value: 'Hasil Adjustment' },
                { label: 'Lihat PDRB se-Provinsi', value: 'Lihat PDRB se-Provinsi' },
                { label: 'Entri Fenomena', value: 'Entri Fenomena' },
                { label: 'Lihat Fenomena', value: 'Lihat Fenomena' },
                { label: 'Kelola Akun', value: 'Kelola Akun' },
              ]"
              :searchable="true"
              placeholder="-- Pilih Menu--"
            />
            <div v-if="form.errors.location" class="text-danger">
              {{ form.errors.location }}
            </div>
          </div>
          <div class="space-y-2">
            <label for="tahun">Masalah</label>
            <textarea
              class="input-fordone w-full"
              v-model="form.problem"
              placeholder="Isikan permasalahan yang ditemukan user"
            />
            <div v-if="form.errors.problem" class="text-danger">
              {{ form.errors.problem }}
            </div>
          </div>
          <div class="mb-3 space-y-2" v-if="isUpdate">
            <label for="triwulan">Fix</label>
            <textarea class="input-fordone w-full" v-model="form.fix" />
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" class="btn-success-fordone btn-sm" @click.prevent="submit">
          Simpan
        </button>
      </template>
    </ModalBs>
    <ModalBs
      :-modal-status="deleteModalStatus"
      @close="
        () => {
          deleteModalStatus = false;
          form.reset();
        }
      "
      :title="'Hapus ini'"
    >
      <template #modalBody>
        <div class="form-group">
          <div>
            <label>Apakah Anda yakin ingin menghapus ini?</label>
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button
          type="button"
          class="btn-red-fordone btn-sm"
          @click.prevent="deleteSubmit"
        >
          Hapus
        </button>
      </template>
    </ModalBs>
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
import Multiselect from "@vueform/multiselect";
import { ref, watch, computed } from "vue";

const page = usePage();
var dataObject = page.props.question.data;
const questions = ref(dataObject);
const createModalStatus = ref(false);
const modalTitle = ref("Ajukan Permasalahan Baru");
const isUpdate = ref(false);
const deleteModalStatus = ref(false);
const searchLocation = ref(null);
const searchProblem = ref(null);
// const searchReason = ref(null);
const searchFix = ref(null);
const searchUser = ref(null);
const searchDate = ref(null);
const form = useForm({
  _token: null,
  id: null,
  location: null,
  problem: null,
  reason: null,
  fix: null,
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
const ArrayBigObjects = [
  { key: "location", valueFilter: searchLocation },
  { key: "problem", valueFilter: searchProblem },
  // { key: "reason", valueFilter: searchReason },
  { key: "fix", valueFilter: searchFix },
  { key: "username", valueFilter: searchUser },
  { key: "updated_at", valueFilter: searchDate },
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
  return questions.value;
});
watch(
  () => page.props.question.data,
  (value) => {
    questions.value = value;
  }
);
const fetchData = async () => {
  try {
    const response = await axios.get(route("user.question"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          location: searchLocation.value,
          problem: searchProblem.value,
          // reason: searchReason.value,
          fix: searchFix.value,
          username: searchUser.value,
          updated_at: searchDate.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    questions.value = response.data.question.data;
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
const submit = async () => {
  try {
    const response = await axios.get(route("token"));
    form._token = response.data;
    if (form.processing) return;
    form.post("/user/question", {
      onSuccess: (response) => {
        showNotification(response.props.notification);
        if (response.props.notification[0].type == "message") {
          createModalStatus.value = false;
          fetchData();
          form.reset();
        }
      },
    });
  } catch (error) {
    console.error(error);
  }
};
const deleteSubmit = async () => {
  try {
    const response = await axios.get(route("token"));
    form._token = response.data;
    if (form.processing) return;
    form.delete("/user/delete-question/" + form.id, {
      onSuccess: (response) => {
        showNotification(response.props.notification);
        if (response.props.notification[0].type == "message") {
          deleteModalStatus.value = false;
          fetchData();
          form.reset();
        }
      },
    });
  } catch (error) {
    console.error(error);
  }
};
const toggleUpdateModal = async (id) => {
  try {
    const response = await axios.get(route("user.fetch-question", { id }));
    form.id = response.data.data.id;
    form.location = response.data.data.location;
    form.problem = response.data.data.problem;
    form.fix = response.data.data.fix;
    createModalStatus.value = true;
    isUpdate.value = true;
  } catch (error) {
    console.error(error);
  }
};
const deleteUpdateModal = async (id) => {
  try {
    const response = await axios.get(route("user.fetch-question", { id }));
    form.id = response.data.data.id;
    deleteModalStatus.value = true;
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped></style>
