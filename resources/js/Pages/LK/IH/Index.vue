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
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
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
                v-model="searchYear"
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
            v-if="komoditas_data.length > 0"
            v-for="(data, dataIndex) in paginatedData"
            :key="data.id"
          >
            <td class="align-middle">{{ data.number }}</td>
            <td class="align-middle">{{ data.label }}</td>
            <td class="align-middle text-right">{{ data.tahun }}</td>
            <td class="align-middle text-right">{{ formatNumberGerman(data.tw1) }}</td>
            <td class="align-middle text-right">{{ formatNumberGerman(data.tw2) }}</td>
            <td class="align-middle text-right">{{ formatNumberGerman(data.tw3) }}</td>
            <td class="align-middle text-right">{{ formatNumberGerman(data.tw4) }}</td>
            <td class="text-center align-middle deleted space-x-2">
              <a @click="updateData(data.label, data.tahun)" v-if="checkRow(data)"
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
      :-modal-status="createModalStatus"
      @close="createModalStatus = false"
      :title="'Isi Indeks Harga'"
      :modal-size="'min-w-[20vw]'"
    >
      <template #modalBody>
        <div class="form-group">
          <div class="mb-3 space-y-2">
            <div>Download Template di sini</div>
            <button
              @click="thisDownload"
              type="button"
              class="btn-success-fordone btn-sm text-sm"
            >
              Download
            </button>
          </div>
          <div class="space-y-2">
            <div><label>Upload File :</label></div>
            <input type="file" @change="handleUpload" class="form-control w-full" />
          </div>
          <div class="text-danger mt-2 text-sm font-vold">
            {{ form.errors.fileUpload }}
          </div>
        </div>
      </template>
      <template #modalFunction
        ><button type="button" @click="submit" class="btn-sm text-sm btn-success-fordone">
          Kirim
        </button>
      </template>
    </ModalBs>
    <ModalBs
      :-modal-status="updateModalStatus"
      @close="updateModalStatus = false"
      :title="'Update Data'"
      :modal-size="'min-w-[900px]'"
    >
      <template #modalBody>
        <div class="form-group table-responsive-mobile overflow-x-auto">
          <table class="table border-2 mb-2 w-full">
            <thead>
              <tr>
                <th>Nama Komoditas</th>
                <th>Triwulan I</th>
                <th>Triwulan II</th>
                <th>Triwulan III</th>
                <th>Triwulan IV</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="targetUpdatedData">
                <td>{{ targetUpdatedData.label }}</td>
                <td v-for="(node, index) in ['tw1', 'tw2', 'tw3', 'tw4']" :key="index">
                  <input
                    @input="
                      (event) => {
                        debounceHandleInput(event, node);
                      }
                    "
                    type="text"
                    class="text-right w-full p-1 border-gray-300 rounded"
                    :value="getData(node)"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
      <template #modalFunction>
        <button
          type="button"
          @click="updateIndeks"
          class="btn-sm text-sm btn-success-fordone"
        >
          Update
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
import { Head, useForm } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, ref, watch } from "vue";
import * as XLSX from "xlsx";

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
const form = useForm({
  komoditas_id: null,
  tahun: null,
  tw1: null,
  tw2: null,
  tw3: null,
  tw4: null,
  fileUpload: null,
});
const komoditas_data = ref(props.komoditas.data);
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
const formatNumberGerman = (num, min = 2, max = 3) => {
  if (num) {
    return new Intl.NumberFormat("de-DE", {
      minimumFractionDigits: min,
      maximumFractionDigits: max,
    }).format(num);
  } else return;
};
const searchLabel = ref(null);
const searchTahun = ref(null);
const searchUpdatedAt = ref(null);
const searchYear = ref(null);
const ArrayBigObjects = [
  { key: "label", valueFilter: searchLabel },
  { key: "year", valueFilter: searchYear },
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
  return komoditas_data.value;
});
watch(
  () => props.komoditas,
  (value) => {
    komoditas_data.value = value;
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
          year: searchYear.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    komoditas_data.value = response.data.komoditas.data;
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
  try {
    window.location.href = route("ih.template");
  } catch (error) {
    alert("Gagal Download Data");
  }
};
const createModalStatus = ref(false);
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
    form.fileUpload = rowsAsObjects;
  };
  // baca file
  reader.readAsBinaryString(pickedFile);
};
const submit = () => {
  try {
    form.post(route("ih.store"), {
      onSuccess: (response) => {
        showNotification(response.props.notification);
        form.reset();
        fetchData();
        createModalStatus.value = false;
      },
      onError: (error) => {
        let errorList = [];
        if (error?.notification) {
          errorList.push(error.notification);
          showNotification(errorList);
        }
        fetchData();
      },
    });
  } catch (error) {}
};
//row
const checkRow = (data) => {
  if (data.tw1 == null && data.tw2 == null && data.tw3 == null && data.tw4 == null) {
    return false;
  } else {
    return true;
  }
};
//update
const updateModalStatus = ref(false);
const targetUpdatedData = ref([]);
const updateData = async (l, t) => {
  updateModalStatus.value = true;
  const { data } = await axios.get(route("ih.fetch", { label: l, tahun: t }));
  console.log(data);
  targetUpdatedData.value = data;
};
const getData = (node) => {
  let formattedResult = null;
  formattedResult =
    targetUpdatedData.value[node] == "" || targetUpdatedData.value[node] == null
      ? null
      : formatNumberGerman(Number(targetUpdatedData.value[node]), 2, 2);
  return formattedResult;
};
const handleInput = (event, node) => {
  let value = event.target.value;
  value = String(value).replaceAll(".", "").replace(",", ".");
  targetUpdatedData.value[node] = Number(value);
};
const debounceHandleInput = debounce((event, node) => {
  handleInput(event, node);
}, 350);
const updateIndeks = () => {
  try {
    form.komoditas_id = targetUpdatedData.value.id;
    form.tahun = targetUpdatedData.value.tahun;
    form.tw1 = targetUpdatedData.value.tw1;
    form.tw2 = targetUpdatedData.value.tw2;
    form.tw3 = targetUpdatedData.value.tw3;
    form.tw4 = targetUpdatedData.value.tw4;
    form.patch(route("ih.update"), {
      onBefore: () => {
        updateModalStatus.value = false;
      },
      onSuccess: (response) => {
        showNotification(response.props.notification);
        form.reset();
        fetchData();
      },
      onError: (error) => {
        let errorList = [];
        if (error?.notification) {
          errorList.push(error.notification);
          showNotification(errorList);
        }
        fetchData();
        updateModalStatus.value = true;
      },
    });
  } catch (error) {
    console.error("Error updating indeks: ", error);
  }
};
</script>

<style scoped></style>
