<template>
  <Head title="Daftar Rows" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Daftar Rows
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
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Rows
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
          <tr v-if="rows.length > 0" v-for="data in paginatedData" :key="data.id">
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
    <Pagination
      @update:currentPage="updateCurrentPage"
      @update:showItems="updateShowItems"
      :show-items="showItems"
      :total-items="totalItems"
      :current-page="currentPage"
      :current-show-items="paginatedData.length"
    />
    <ModalBs
      :-modal-status="createModalStatus"
      @close="
        () => {
          createModalStatus = false;
          form.reset();
          formError = [];
        }
      "
      :modal-size="'min-w-[30vw]'"
      :title="modalTitle"
    >
      <template #modalBody>
        <div class="form-group">
          <div class="mb-3 space-y-2">
            <label>Rows</label>
            <input
              v-model="form.label"
              type="text"
              class="input-fordone w-full"
              placeholder="Isi Nama Rows"
            />
          </div>
          <div class="text-danger" v-if="form.errors.label">{{ form.errors.label }}</div>
          <div class="text-danger" v-if="formError.length > 0" v-for="node in formError">
            {{ node }}
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
          formError = [];
        }
      "
      :title="'Hapus Rows'"
    >
      <template #modalBody>
        <div class="form-group">
          <div>
            <label>Apakah Anda yakin ingin menghapus rows ini?</label>
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
import { computed, ref, watch } from "vue";

const page = usePage();
var dataObject = page.props.row.data;
const rows = ref(dataObject);
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
const ArrayBigObjects = [{ key: "nama", valueFilter: searchLabel }];
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
  return rows.value;
});
watch(
  () => page.props.row.data,
  (value) => {
    rows.value = value;
  }
);
const fetchData = async () => {
  try {
    const response = await axios.get(route("master.rows.index"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          nama: searchLabel.value,
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

//submit
const form = useForm({
  id: null,
  _token: null,
  label: null,
});
const submit = async () => {
  const response = await axios.get(route("token"));
  form._token = response.data;
  form.post(route("master.rows.store"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
      if (response.props.notification[0].type == "success") {
        fetchData();
        form
          .defaults({
            id: null,
            _token: null,
            label: null,
          })
          .reset();
        formError.value = [];
        createModalStatus.value = false;
      } else {
        response.props.notification.forEach((element) => {
          formError.value.push(element.error);
        });
      }
    },
    onFinish: () => {
      modalTitle.value = "Tambah Rows Baru";
    },
  });
};
const deleteSubmit = async () => {
  const response = await axios.get(route("token"));
  form._token = response.data;
  form.delete(route("master.rows.destroy", { id: form.id }), {
    onSuccess: (response) => {
      form.reset();
      fetchData();
      deleteModalStatus.value = false;
      formError.value = [];
      showNotification(response.props.notification);
    },
  });
};

//modal
const modalTitle = ref("Tambah Rows Baru");
const toggleUpdateModal = async (id) => {
  try {
    modalTitle.value = "Update Rows";
    const response = await axios.get(route("master.rows.fetch", { id }));
    form.id = response.data.data.id;
    form.label = response.data.data.label;
    createModalStatus.value = true;
  } catch (error) {
    console.error(error);
  }
};
const deleteUpdateModal = async (id) => {
  try {
    const response = await axios.get(route("master.rows.fetch", { id }));
    form.id = response.data.data.id;
    deleteModalStatus.value = true;
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped></style>
