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
            <th class="text-center th-order" @click="clickToOrder('label')">SUT</th>
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
              <a @click="toggleUpdateModal(data.id, data.label)">
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
        }
      "
      :modal-size="'min-w-[30vw]'"
      :title="'Master SUT'"
    >
      <template #modalBody>
        <div class="form-group">
          <label>Nama SUT</label>
          <input type="text" class="input-fordone w-full" v-model="form.label" />
          <div class="mt-2 text-danger">{{ page.props.errors?.label }}</div>
        </div>
      </template>
      <template #modalFunction>
        <button class="btn btn-sm btn-success-fordone" @click="submit">Simpan</button>
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
      :title="'Hapus Data'"
    >
      <template #modalBody>
        <div class="form-group">
          <div>
            <label
              >Apakah Anda yakin ingin menghapus data ini? Data akan terhapus
              selamanya</label
            >
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
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const page = usePage();
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
const form = useForm({
  id: null,
  _token: null,
  label: null,
});
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
    sut.value = response.data.sut.data;
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
    const token = await axios.get(route("token"));
    form._token = token.data;
    if (form.id == null) {
      form.post(route("rba.master-sut.store"), {
        onSuccess: (response) => {
          showNotification(response.props.notification);
          form.reset();
          fetchData();
          createModalStatus.value = false;
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
      form.patch(route("rba.master-sut.update"), {
        onSuccess: (response) => {
          showNotification(response.props.notification);
          form.reset();
          fetchData();
          createModalStatus.value = false;
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

//update and delete
const toggleUpdateModal = (id, label) => {
  form.id = id;
  form.label = label;
  createModalStatus.value = true;
};
const deleteModalStatus = ref(false);
const deleteUpdateModal = (id) => {
  form.id = id;
  deleteModalStatus.value = true;
};
const deleteSubmit = async () => {
  try {
    const token = await axios.get(route("token"));
    form._token = token.data;
    form.delete(route("rba.master-sut.destroy", { id: form.id }), {
      onSuccess: (response) => {
        form.reset();
        fetchData();
        showNotification(response.props.notification);
        deleteModalStatus.value = false;
      },
      onError: (error) => {
        let errorList = [];
        if (errors?.notification) {
          errorList.push(errors?.notification);
          showNotification(errorList);
        }
      },
    });
  } catch (error) {
    console.error("Error Fetching Data : ", error);
  }
};
</script>

<style scoped></style>
