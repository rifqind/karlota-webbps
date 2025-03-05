<template>
  <Head title="Daftar Pengguna" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Daftar Pengguna
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button class="btn-success-fordone mr-2 mb-2 lg:mb-0" title="Download">
          <font-awesome-icon icon="fa-solid fa-circle-down" />
        </button>
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" />
          Tambah Pengguna Baru
        </button>
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column">No.</th>
            <th class="text-center th-order tabel-width-20" @click="clickToOrder('name')">
              Nama
            </th>
            <th class="text-center th-order" @click="clickToOrder('satker')">Satker</th>
            <th class="text-center th-order" @click="clickToOrder('role')">Role</th>
            <th class="text-center th-order deleted">Edit/Hapus</th>
          </tr>
          <tr class="">
            <td class="search-header"></td>
            <td class="search-header">
              <input v-model.trim="searchName" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchSatker"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input v-model.trim="searchRole" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
          <tr v-if="users.length > 0" v-for="data in paginatedData" :key="data.id">
            <td>{{ data.number }}</td>
            <td>{{ data.name }}</td>
            <td>{{ data.satker }}</td>
            <td>{{ data.role }}</td>
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
          form.reset();
        }
      "
      :title="'Tambah Pengguna Baru'"
      :modalSize="'min-w-[40vw]'"
      :modalPosition="'items-start pt-5'"
    >
      <template #modalBody>
        <div class="form-group">
          <div class="mb-3 space-y-2">
            <label for="pdrb">Username</label>
            <input
              v-model="form.name"
              placeholder="Isikan Username Akun"
              class="input-fordone w-full"
              type="text"
            />
          </div>
          <div v-if="form.errors.name" class="mb-3 text-red-500">
            {{ form.errors.name }}
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb">Email<span class="text-danger">*</span></label>
            <input
              v-model="form.email"
              placeholder="Isikan Email"
              class="input-fordone w-full"
              type="email"
            />
          </div>
          <div v-if="form.errors.email" class="mb-3 text-red-500">
            {{ form.errors.email }}
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb">Kabupaten/Kota<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.satker_id"
              :options="page.props.satker"
              :searchable="true"
              placeholder="-- Pilih Kabupaten/Kota --"
            />
          </div>
          <div v-if="form.errors.satker_id" class="mb-3 text-red-500">
            {{ form.errors.satker_id }}
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb">Role<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.role"
              :options="[
                { label: 'User', value: 'user' },
                { label: 'Admin', value: 'admin' },
              ]"
              :searchable="true"
              placeholder="-- Pilih Role --"
            />
          </div>
          <div v-if="form.errors.role" class="mb-3 text-red-500">
            {{ form.errors.role }}
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb">Password<span class="text-danger">*</span></label>
            <input
              type="password"
              placeholder="Boleh dikosongkan jika update user"
              v-model="form.password"
              class="input-fordone w-full"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb"
              >Konfirmasi Password<span class="text-danger">*</span></label
            >
            <input
              type="password"
              placeholder="Boleh dikosongkan jika update user"
              v-model="form.password_confirmation"
              class="input-fordone w-full"
            />
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" class="btn-success-fordone btn-sm" @click.prevent="submit">
          Simpan
        </button></template
      >
    </ModalBs>
    <ModalBs
      :-modal-status="deleteModalStatus"
      @close="
        () => {
          deleteModalStatus = false;
          form.reset();
        }
      "
      :title="'Hapus Periode Putaran ini'"
    >
      <template #modalBody>
        <div class="form-group">
          <div>
            <label>Apakah Anda yakin ingin menghapus user ini?</label>
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
import { computed, ref, watch } from "vue";

const page = usePage();
var dataObject = page.props.user.data;
const users = ref(dataObject);
const createModalStatus = ref(false);
const deleteModalStatus = ref(false);
const searchName = ref(null);
const searchSatker = ref(null);
const searchRole = ref(null);
const form = useForm({
  _token: null,
  id: null,
  name: null,
  email: null,
  satker_id: null,
  role: null,
  password: null,
  password_confirmation: null,
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
  { key: "name", valueFilter: searchName },
  { key: "satker", valueFilter: searchSatker },
  { key: "role", valueFilter: searchRole },
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
  return users.value;
});
watch(
  () => page.props.user.data,
  (value) => {
    users.value = value;
  }
);
const fetchData = async () => {
  try {
    const response = await axios.get(route("user.index"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          name: searchName.value,
          satker: searchSatker.value,
          role: searchRole.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    users.value = response.data.user.data;
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
  const response = await axios.get(route("token"));
  form._token = response.data;
  if (form.processing) return;
  form.post(route("user.store"), {
    onSuccess: (response) => {
      fetchData();
      createModalStatus.value = false;
      showNotification(response.props.notification);
    },
  });
};
const deleteSubmit = async () => {
  const response = await axios.get(route("token"));
  form._token = response.data;
  if (form.processing) return;
  form.delete(route("user.destroy", { id: form.id }), {
    onSuccess: (response) => {
      fetchData();
      deleteModalStatus.value = false;
      showNotification(response.props.notification);
    },
  });
};
const toggleUpdateModal = async (id) => {
  try {
    const response = await axios.get(route("user.fetch", { id }));
    form.id = response.data.data.id;
    form.name = response.data.data.name;
    form.email = response.data.data.email;
    form.satker_id = response.data.data.satker_id;
    form.role = response.data.data.role;
    createModalStatus.value = true;
  } catch (error) {
    console.error(error);
  }
};
const deleteUpdateModal = async (id) => {
  try {
    const response = await axios.get(route("user.fetch", { id }));
    form.id = response.data.data.id;
    deleteModalStatus.value = true;
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped></style>
