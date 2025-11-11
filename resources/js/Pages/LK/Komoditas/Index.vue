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
            <th class="text-center th-order tabel-width-8" @click="clickToOrder('code')">
              Kode Komoditas
            </th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('satuan')"
            >
              Satuan
            </th>
            <th class="text-center th-order" @click="clickToOrder('subsector_label')">
              Subsektor
            </th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('subsector_label')"
            >
              Harga Dasar (2010)
            </th>
            <th
              class="text-center th-order tabel-width-8"
              @click="clickToOrder('master_komoditas.updated_at')"
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
              <Multiselect
                :options="props.subsector"
                :searchable="true"
                placeholder="Cari Subsektor"
                v-model="searchSubsektor"
              />
            </td>
            <td class="search-header"></td>
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
            <td class="align-middle">
              Rp {{ formatNumberGerman(data.harga_konstan, 2, 5) }}
            </td>
            <td class="align-middle text-center">
              <span class="badge badge-info">{{ data.username }}</span>
              <br />
              <span>{{ data.updated_time }}</span>
            </td>
            <td class="text-center align-middle deleted space-x-2">
              <a @click="updateData(data.id)"
                ><font-awesome-icon icon="fa-solid fa-pen" title="Edit" class="edit-pen"
              /></a>
              <a @click="deleteData(data.id)"
                ><font-awesome-icon
                  icon="fa-solid fa-trash-can"
                  title="Hapus"
                  class="icon-trash-color"
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
      :modal-size="'min-w-[30vw]'"
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
          <template v-if="modeKomoditas == 1">
            <div class="space-y-2">
              <label>Nama Komoditas</label>
              <input
                type="text"
                v-model="form.manual.label"
                class="input-fordone w-full"
                placeholder="Isikan nama komoditas"
              />
            </div>
            <div class="space-y-2">
              <label>Kode Komoditas</label>
              <input
                type="text"
                v-model="form.manual.code"
                class="input-fordone w-full"
                placeholder="Bisa dikosongkan"
              />
            </div>
            <div class="space-y-2">
              <label>Satuan</label>
              <input
                type="text"
                v-model="form.manual.satuan"
                class="input-fordone w-full"
                placeholder="Isikan satuan komoditas"
              />
            </div>
            <div class="space-y-2">
              <label>Tipe Komoditas</label>
              <Multiselect
                :options="[
                  { label: 'Produksi', value: 1 },
                  { label: 'Output', value: 2 },
                ]"
                :searchable="true"
                v-model="form.manual.type"
                placeholder="Pilih sesuai ketersediaan data, data produksi atau data output"
              />
            </div>
            <div class="space-y-2">
              <label>Subsektor</label>
              <Multiselect
                :options="props.subsector"
                v-model="form.manual.subsector_id"
                placeholder="-- Pilih Subsektor --"
                :searchable="true"
              />
            </div>
            <div class="space-y-2">
              <label>Harga Konstan (2010)</label>
              <input
                type="text"
                v-model="form.manual.harga_konstan"
                class="input-fordone w-full"
                placeholder="Isikan harga konstan (2010), jika belum ada bisa dikosongkan"
              />
            </div>
          </template>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" @click="submit" class="btn-sm btn-success-fordone">
          Simpan
        </button>
      </template>
    </ModalBs>
    <ModalBs
      :-modal-status="updateModalStatus"
      @close="
        () => {
          updateModalStatus = false;
          updateForm.reset();
        }
      "
      :title="'Update Komoditas'"
      :modal-size="'min-w-[25vw]'"
    >
      <template #modalBody>
        <div class="space-y-3 form-group">
          <div class="space-y-2">
            <label>Nama Komoditas</label>
            <input
              type="text"
              v-model="updateForm.label"
              class="input-fordone w-full"
              placeholder="Isikan nama komoditas"
            />
          </div>
          <div class="space-y-2">
            <label>Kode Komoditas</label>
            <input
              type="text"
              v-model="updateForm.code"
              class="input-fordone w-full"
              placeholder="Bisa dikosongkan"
            />
          </div>
          <div class="space-y-2">
            <label>Satuan</label>
            <input
              type="text"
              v-model="updateForm.satuan"
              class="input-fordone w-full"
              placeholder="Isikan satuan komoditas"
            />
          </div>
          <div class="space-y-2">
            <label>Tipe Komoditas</label>
            <Multiselect
              :options="[
                { label: 'Produksi', value: 1 },
                { label: 'Output', value: 2 },
              ]"
              :searchable="true"
              v-model="updateForm.type"
              placeholder="Pilih sesuai ketersediaan data, data produksi atau data output"
            />
          </div>
          <div class="space-y-2">
            <label>Subsektor</label>
            <Multiselect
              :options="props.subsector"
              v-model="updateForm.subsector_id"
              placeholder="-- Pilih Subsektor --"
              :searchable="true"
            />
          </div>
          <div class="space-y-2">
            <label>Harga Konstan (2010)</label>
            <input
              type="text"
              v-model="updateForm.harga_konstan"
              class="input-fordone w-full"
              placeholder="Isikan harga konstan (2010), jika belum ada bisa dikosongkan"
            />
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" @click="updateSubmit" class="btn-sm btn-warning-fordone">
          Update
        </button>
      </template>
    </ModalBs>
    <ModalBs
      :-modal-status="deleteModalStatus"
      @close="
        () => {
          deleteModalStatus = false;
          updateForm.reset();
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
const updateModalStatus = ref(false);
const deleteModalStatus = ref(false);
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
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
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
const downloadTemplate = () => {
  window.location.href = "/komoditas/download-template/komoditas";
};
const dataPreview = ref(null);
const headers = computed(() => {
  if (!dataPreview.value || dataPreview.value.length == 0) return [];
  return Object.keys(dataPreview.value[0]);
});
const rows = computed(() => {
  return dataPreview.value ?? [];
});
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
    const rowsAsObjects = XLSX.utils.sheet_to_json(worksheet, {
      defval: null,
      raw: true,
    });
    // Simpan ke variabel reactive / state form
    // form.fileUpload = data;
    // dataPreview.value = data;
    form.fileUpload = rowsAsObjects;
    dataPreview.value = rowsAsObjects;
  };
  // baca file
  reader.readAsBinaryString(pickedFile);
};
const form = useForm({
  _token: null,
  fileUpload: null,
  headers: null,
  rows: null,
  manual: {
    label: null,
    code: null,
    satuan: null,
    type: null,
    subsector_id: null,
    harga_konstan: null,
  },
  mode: null,
});
const submit = async () => {
  try {
    const token = await axios.get(route("token"));
    form._token = token.data;
    form.mode = modeKomoditas.value;
    form.headers = headers.value;
    form.rows = rows.value;
    form.post(route("komoditas.store"), {
      onSuccess: (response) => {
        form.reset();
        fetchData();
        // let notification = [];
        // notification.push(response.props.notification);
        showNotification(response.props.notification);
      },
      onFinish: () => {
        createModalStatus.value = false;
      },
      onError: (errors) => {
        let errorList = [];
        errorList.push(errors.notifications);
        showNotification(errorList);
      },
    });
  } catch (error) {
    console.error("Error submit : " + error);
  }
};

//update
const updateForm = useForm({
  _token: null,
  id: null,
  label: null,
  code: null,
  satuan: null,
  type: null,
  subsector_id: null,
  harga_konstan: null,
});
const updateData = async (id) => {
  try {
    const komoditas = await axios.get("/komoditas/update/" + id);
    updateForm.id = id;
    updateForm.label = komoditas.data.this_komoditas.label;
    updateForm.code = komoditas.data.this_komoditas.code;
    updateForm.satuan = komoditas.data.this_komoditas.satuan;
    updateForm.type = komoditas.data.this_komoditas.type;
    updateForm.subsector_id = komoditas.data.this_komoditas.subsector_id;
    updateForm.harga_konstan = komoditas.data.this_komoditas.harga_konstan;
    updateModalStatus.value = true;
  } catch (error) {
    console.error("Error fetching komoditas data: ", error);
  }
};
const updateSubmit = async () => {
  try {
    const token = await axios.get(route("token"));
    updateForm._token = token.data;
    updateForm.post(route("komoditas.update"), {
      onSuccess: (response) => {
        updateForm.reset();
        fetchData();
        showNotification(response.props.notification);
        updateModalStatus.value = false;
      },
      onError: (error) => {
        let errorList = [];
        errorList.push(error.notification);
        showNotification(errorList);
      },
    });
  } catch (error) {
    console.error("Error update :" + error);
  }
};

//delete
const deleteData = (id) => {
  updateForm.id = id;
  deleteModalStatus.value = true;
};
const deleteSubmit = async () => {
  try {
    const token = await axios.get(route("token"));
    updateForm._token = token.data;
    updateForm.delete(route("komoditas.destroy", { id: updateForm.id }), {
      onSuccess: (response) => {
        updateForm.reset();
        fetchData();
        showNotification(response.props.notification);
        deleteModalStatus.value = false;
      },
      onError: (error) => {
        let errorList = [];
        errorList.push(error.notification);
        showNotification(errorList);
      },
    });
  } catch (error) {
    console.error("Error Fetching Data : ", error);
  }
};
</script>

<style scoped>
table {
  font-size: smaller;
}
</style>
