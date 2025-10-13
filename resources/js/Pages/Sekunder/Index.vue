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
        <Link :href="route('sekunder.create')" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Data Sekunder
        </Link>
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column tabel-width-5">No.</th>
            <th
              class="text-center th-order tabel-width-20"
              @click="clickToOrder('p.nama')"
            >
              Nama Dinas
            </th>
            <th class="text-center th-order" @click="clickToOrder('s.label')">
              Judul Data Sekunder
            </th>
            <th class="text-center th-order tabel-width-20">Data</th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('status_sekunder.tahun')"
            >
              Tahun
            </th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('status_sekunder.status')"
            >
              Status
            </th>
            <th
              class="text-center th-order tabel-width-8"
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
                v-model.trim="searchRowLabel"
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
          <tr
            v-if="sekunder.length > 0"
            v-for="(data, dataIndex) in paginatedData"
            :key="data.id"
          >
            <td class="align-middle">{{ data.number }}</td>
            <td class="align-middle">{{ data.nama_dinas }}</td>
            <td class="align-middle">{{ data.label_data }}</td>
            <td class="align-middle">
              <template v-for="(item, index) in data.rows" :key="index">
                <span v-if="!data.rows.length > 5" class="badge badge-info mr-1">{{
                  hiddenText(item.label)
                }}</span>
                <span
                  v-if="
                    !data.rows.length > 5 ||
                    indexExpandedRow[dataIndex] ||
                    openRowList(index)
                  "
                  class="badge badge-info mr-1"
                  >{{ hiddenText(item.label) }}
                </span>
              </template>
              <span
                v-if="data.rows.length > 5"
                class="badge badge-info mr-1 cursor-pointer"
                @click="openOtherRow(dataIndex)"
              >
                <font-awesome-icon
                  v-if="!indexExpandedRow[dataIndex]"
                  icon="fa-solid fa-angle-down"
                />
                <font-awesome-icon
                  v-if="indexExpandedRow[dataIndex]"
                  icon="fa-solid fa-angle-up"
                />
              </span>
            </td>
            <td class="align-middle">
              <span class="badge badge-info">{{ data.tahun }}</span>
            </td>
            <td class="align-middle">
              <span class="badge" :class="getClass(data.status_id)">{{
                data.label_status
              }}</span>
            </td>
            <td class="align-middle text-center">
              <span class="badge badge-info">{{ data.username }}</span>
              <br />
              <span>{{ data.updated_time }}</span>
            </td>
            <td class="text-center align-middle deleted">
              <Link
                :href="route('sekunder.entri', { id: data.id })"
                class="view-pen mx-1"
              >
                <font-awesome-icon icon="fa-solid fa-eye" title="Entri Data" />
              </Link>
              <Link
                :href="route('master.sekunder.update', { id: data.sekunder_id })"
                class="edit-pen mx-1"
              >
                <font-awesome-icon icon="fa-solid fa-pen" title="Cek/Edit" />
              </Link>
              <a
                class="edit-pen mx-1"
                @click="
                  () => {
                    createModalStatus = true;
                    form.id = data.sekunder_id;
                  }
                "
              >
                <font-awesome-icon icon="fa-solid fa-plus-circle" title="Tambah Tahun" />
              </a>
              <a @click="deleteUpdateModal(data.id)" class="mx-1"
                ><font-awesome-icon
                  icon="fa-solid fa-trash-can"
                  class="icon-trash-color"
                  title="Hapus"
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
  </GeneralLayout>
  <ModalBs
    :-modal-status="createModalStatus"
    @close="
      () => {
        createModalStatus = false;
        form.reset();
      }
    "
    :title="'Tambah Tahun'"
    :modalSize="'min-w-[25vw]'"
    ><template #modalBody>
      <div class="form-group">
        <div class="mb-1 space-y-2">
          <label for="tahun">Pilih Tahun</label>
          <Multiselect
            v-model="form.tahun"
            :options="yearDrop"
            :searchable="true"
            placeholder="-- Pilih Tahun --"
            mode="tags"
          />
        </div>
      </div>
      <div class="text-danger" v-if="page.props.errors['tahun.0']">
        {{ page.props.errors["tahun.0"] }}
      </div>
    </template>
    <template #modalFunction>
      <button type="button" class="btn-success-fordone btn-sm" @click.prevent="addYear">
        Tambah Tahun
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
      <button type="button" class="btn-red-fordone btn-sm" @click.prevent="deleteSubmit">
        Hapus
      </button>
    </template>
  </ModalBs>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import ModalBs from "@/Components/ModalBs.vue";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, ref, watch } from "vue";

const page = usePage();
var dataObject = page.props.sekunder;
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

const getClass = (id) => {
  if (id == 1) return "badge-status-empat";
  if (id == 2) return "badge-status-dua";
};

//fetch series
const searchLabel = ref(null);
const searchNamaDinas = ref(null);
const searchTahun = ref(null);
const searchStatus = ref(null);
const searchUpdated = ref(null);
const searchRowLabel = ref(null);
const ArrayBigObjects = [
  { key: "label_data", valueFilter: searchLabel },
  { key: "nama_dinas", valueFilter: searchNamaDinas },
  { key: "tahun", valueFilter: searchTahun },
  { key: "status", valueFilter: searchStatus },
  { key: "updated_at", valueFilter: searchUpdated },
  { key: "row_label", valueFilter: searchRowLabel },
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
  () => page.props.sekunder,
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
          row_label: searchRowLabel.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    sekunder.value = response.data.sekunder;
    totalItems.value = response.data.countData;
    indexExpandedRow.value = Array(sekunder.value.length).fill(false);
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

const form = useForm({
  _token: null,
  id: null,
  tahun: [],
});
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 6 }, (_, index) => currentYear + 1 - index);
const yearDrop = ref(null);
yearDrop.value = years.map((year) => ({
  label: year.toString(),
  value: year.toString(),
}));
//modal sense
const deleteUpdateModal = (id) => {
  deleteModalStatus.value = true;
  form.id = id;
};
const deleteSubmit = async () => {
  try {
    const token = await axios.get(route("token"));
    form._token = token.data;
    form.delete(route("sekunder.destroy", { id: form.id }), {
      onSuccess: (response) => {
        fetchData();
        form.reset();
        deleteModalStatus.value = false;
        showNotification(response.props.notification);
      },
    });
  } catch (error) {
    console.error(error);
  }
};
const addYear = async () => {
  try {
    const token = await axios.get(route("token"));
    form._token = token.data;
    form.post(route("master.sekunder.addyear"), {
      onSuccess: (response) => {
        fetchData();
        form.reset();
        createModalStatus.value = false;
        showNotification(response.props.notification);
      },
    });
  } catch (error) {
    console.error(error);
  }
};
//row modified
const indexExpandedRow = ref(Array(paginatedData.value.length).fill(false));
const openOtherRow = (index) => {
  indexExpandedRow.value[index] = !indexExpandedRow.value[index];
};
const openRowList = (index) => {
  if (index < 5) return true;
  else return false;
};
const hiddenText = (value) => {
  if (value.length > 50) {
    return value.substring(0, 50) + "...";
  } else return value;
};
</script>

<style scoped>
.view-pen {
  color: #1d845b;
  cursor: pointer;
}
</style>
