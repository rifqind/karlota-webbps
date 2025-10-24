<template>
  <Head title="Daftar Komoditas" />
  <SpinnerBorder v-if="triggerSpinner" />
  <LKLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Daftar Komoditas
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
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Komoditas
        </button>
      </div>
    </div>
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column tabel-width-5">No.</th>
            <th class="text-center th-order" @click="clickToOrder('label')">
              Nama Komoditas
            </th>
            <th class="text-center th-order" @click="clickToOrder('code')">
              Kode Komoditas
            </th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('satuan')"
            >
              Satuan
            </th>
            <th class="text-center th-order" @click="clickToOrder('subsectors.label')">
              Subsektor
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
                v-model.trim="searchLabel"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input v-model.trim="searchKode" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchSatuan"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchSubsektor"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchUpdatedAt"
                type="text"
                class="input-fordone w-full"
              />
            </td>
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
            <td class="align-middle">{{ data.code }}</td>
            <td class="align-middle">{{ data.satuan }}</td>
            <td class="align-middle">
              {{ data.subsector_label }}
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
    <ModalBs
      :-modal-status="createModalStatus"
      @close="
        () => {
          createModalStatus = false;
          form.reset();
        }
      "
      :title="'Tambah Komoditas'"
      :modal-size="'min-w-[25vw]'"
    >
      <template #modalBody>
        <div class="space-y-3 form-group">
          <div class="space-y-2">
            <label>Upload File atau Manual?</label>
            <Multiselect
              :options="[
                { label: 'Manual', value: 1 },
                { label: 'Upload', value: 2 },
              ]"
              placeholder="-- Pilih Mode --"
              :searchable="true"
              v-model="modeKomoditas"
            />
          </div>
          <template v-if="modeKomoditas == 2">
            <div class="space-y-2">
              <label>Download Template</label>
              <div
                class="btn btn-success-fordone btn-sm w-[130px] text-center"
                @click="downloadTemplate"
              >
                <font-awesome-icon icon="fa-solid fa-file" />
                Download
              </div>
            </div>
            <div class="space-y-2">
              <label>Pilih File</label>
              <div>
                <input type="file" @change="handleUpload" class="form-control" />
              </div>
            </div>
          </template>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" class="btn-sm btn-success-fordone">Simpan</button>
      </template>
    </ModalBs>
  </LKLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import LKLayout from "@/Layouts/LKLayout.vue";
import { watch, ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import ModalBs from "@/Components/ModalBs.vue";
import Multiselect from "@vueform/multiselect";
import * as XLSX from "xlsx";

const createModalStatus = ref(false);
const modeKomoditas = ref(null);

const searchLabel = ref(null);
const searchKode = ref(null);
const searchSatuan = ref(null);
const searchSubsektor = ref(null);
const searchUpdatedAt = ref(null);
const props = defineProps({
  komoditas: {
    type: Object,
    required: true,
  },
  countData: {
    type: Number,
    required: true,
  },
  subsector: {
    type: Array,
    required: true,
  },
});

const komoditas = ref(props.komoditas);
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
  { key: "label", valueFilter: searchLabel },
  { key: "code", valueFilter: searchKode },
  { key: "satuan", valueFilter: searchSatuan },
  { key: "subsector_label", valueFilter: searchSubsektor },
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
    const response = await axios.get(route("komoditas.index"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          label: searchLabel.value,
          code: searchKode.value,
          satuan: searchSatuan.value,
          subsector_label: searchSubsektor.value,
          subsector_updatedAt: searchUpdatedAt.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
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
const downloadTemplate = () => {
  window.location.href = "/komoditas/download-template/komoditas";
};
const dataPreview = ref(null);
const handleUpload = (e) => {
  const pickedFile = e.target.files?.[0];
  if (!pickedFile) return;
  const reader = new FileReader();

  reader.onload = (evt) => {
    const binaryStr = evt.target.result;

    // Baca workbook dari hasil FileReader
    const workbook = XLSX.read(binaryStr, { type: "binary" });

    // Ambil sheet pertama
    const firstSheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[firstSheetName];

    // Ubah isi sheet menjadi array of arrays
    const data = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

    // Simpan ke variabel reactive / state form
    form.fileUpload = data;
    dataPreview.value = data;
  };
  // baca file
  reader.readAsBinaryString(pickedFile);
};
const form = useForm({
  fileUpload: null,
});
</script>

<style lang="scss" scoped></style>
