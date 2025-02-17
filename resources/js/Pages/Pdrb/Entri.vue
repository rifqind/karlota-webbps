<template>
  <Head title="Entri PDRB" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Entri PDRB</label>
        </div>
        <div class="p-5">
          <div class="mb-3 space-y-2">
            <label for="type">Pilih PDRB<span class="text-danger">*</span></label>
            <Multiselect v-model="form.type" :placeholder="form.type" disabled />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Tahun<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.year"
              :options="yearDrop"
              :searchable="true"
              placeholder="-- Pilih Tahun --"
              @change="fetchQuarter"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Triwulan<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.quarter"
              :options="quarterDrop"
              :searchable="true"
              placeholder="-- Pilih Triwulan --"
              @change="fetchPeriod"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year"
              >Pilih Periode Putaran<span class="text-danger">*</span></label
            >
            <Multiselect
              v-model="form.description"
              :options="descDrop"
              :searchable="true"
              placeholder="-- Pilih Periode Putaran --"
              @change="fetchYearBefore"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Data Tahun Sebelumnya</label>
            <Multiselect
              v-model="form.dataBefore"
              :options="dataBeforeDrop"
              :searchable="true"
              placeholder="-- Pilih Data Tahun Sebelumnya (opsional, jika tidak dipilih maka akan memilih data tahun paling terbaru/terakhir) --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Kabupaten/Kota<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.regions"
              :options="page.props.regions"
              :searchable="true"
              placeholder="-- Pilih Kabupaten/Kota --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="flex items-center space-x-2 justify-end">
            <div
              @click="copyModal = true"
              class="btn-warning-fordone w-[130px] text-center"
              v-if="showTabPanel"
            >
              <font-awesome-icon icon="fa-solid fa-copy" />
              Salin Data
            </div>
            <div
              class="btn-info-fordone ml-auto w-[130px] text-center"
              @click.prevent="submit"
            >
              <font-awesome-icon icon="fa-solid fa-magnifying-glass" />
              Cari Data
            </div>
          </div>
        </div>
      </div>
      <div
        v-if="showTabPanel"
        class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3"
      >
        <div class="p-5">
          <div class="flex flex-wrap gap-2">
            <button @click="showTab('adhb')" :class="setActiveTab('adhb')">ADHB</button>
            <button @click="showTab('adhk')" :class="setActiveTab('adhk')">ADHK</button>
            <button @click="showTab('dist')" :class="setActiveTab('dist')">
              Distribusi
            </button>
            <button @click="showTab('g_qtoq')" :class="setActiveTab('g_qtoq')">
              Growth (Q-to-Q)
            </button>
            <button @click="showTab('g_ytoy')" :class="setActiveTab('g_ytoy')">
              Growth (Y-to-Y)
            </button>
            <button @click="showTab('g_ctoc')" :class="setActiveTab('g_ctoc')">
              Growth (C-to-C)
            </button>
            <button @click="showTab('indeks')" :class="setActiveTab('indeks')">
              Indeks Implisit
            </button>
            <button @click="showTab('gi_qtoq')" :class="setActiveTab('gi_qtoq')">
              Laju Implisit (Q-to-Q)
            </button>
            <button @click="showTab('gi_ytoy')" :class="setActiveTab('gi_ytoy')">
              Laju Implisit (Y-to-Y)
            </button>
          </div>
        </div>
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="fixed-thead">Komponen</th>
              <th>Triwulan I</th>
              <th>Triwulan II</th>
              <th>Triwulan III</th>
              <th>Triwulan IV</th>
              <th>Total</th>
            </tr>
          </thead>
          <template v-if="page.props.type == 'Lapangan Usaha'">
            <!-- #region Section: ADHB -->
            <LapusTable
              v-show="showPdrbAndResult['adhb']"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->

            <!-- #region Section: ADHK -->
            <LapusTable
              v-show="showPdrbAndResult['adhk']"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->

            <!-- #region Section: RESULT -->
            <LapusResultTable
              v-show="showPdrbAndResult['result']"
              :subsectors="subsectors"
              :type="'distribusi'"
              :quarter-cap="quarterCap"
              :computed-data="computedData"
            />
            <!-- #endregion -->

            <!-- #region Section: PREV_DATA -->
            <!-- #region Section: ADHB -->
            <LapusTable
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->

            <!-- #region Section: ADHK -->
            <LapusTable
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->
            <!-- #endregion -->
          </template>
          <template v-if="page.props.type == 'Pengeluaran'">
            <!-- #region Section: ADHB -->
            <PengTable
              v-show="showPdrbAndResult['adhb']"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->

            <!-- #region Section: ADHK -->
            <PengTable
              v-show="showPdrbAndResult['adhk']"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->

            <!-- #region Section: RESULT -->
            <PengResultTable
              v-show="showPdrbAndResult['result']"
              :subsectors="subsectors"
              :type="'distribusi'"
              :quarter-cap="quarterCap"
              :computed-data="computedData"
            />
            <!-- #endregion -->

            <!-- #region Section: PREV_DATA -->
            <!-- #region Section: ADHB -->
            <PengTable
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->

            <!-- #region Section: ADHK -->
            <PengTable
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
              @update:updateDataContents="updateDataContents"
            />
            <!-- #endregion -->
            <!-- #endregion -->
          </template>
        </table>
      </div>
      <div
        v-if="showTabPanel"
        class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3"
      >
        <div class="p-5">
          <div class="flex justify-end space-x-2">
            <button
              v-if="dataset.status != 'Submitted'"
              @click.prevent="saveEntri"
              class="btn-info-fordone"
            >
              <font-awesome-icon icon="fa fa-save" />
              Simpan Data
            </button>
            <button
              v-if="dataset.status == 'Entry'"
              @click.prevent="submitEntri"
              class="btn-success-fordone"
            >
              <font-awesome-icon icon="fa fa-check" />
              Submit Data
            </button>
            <button
              v-if="dataset.status == 'Submitted'"
              @click.prevent="unsubmitEntri"
              class="btn-red-fordone"
            >
              <font-awesome-icon icon="fa-solid fa-circle-xmark" />
              Unsubmit Data
            </button>
          </div>
        </div>
      </div>
    </div>
    <ModalBs
      :-modal-status="copyModal"
      @close="closeCopyModal"
      :modal-size="'min-w-[40vw]'"
      :title="'Salin Data'"
    >
      <template #modalBody>
        <div class="form-group">
          <div class="mb-3 space-y-2">
            <label for="pdrb">Pilih Tahun</label>
            <Multiselect
              v-model="form.year"
              :options="yearDrop"
              :searchable="true"
              placeholder="-- Pilih Tahun --"
              @change="fetchQuarter"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Triwulan<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.quarter"
              :options="quarterDrop"
              :searchable="true"
              placeholder="-- Pilih Triwulan --"
              @change="fetchPeriod"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year"
              >Pilih Periode Putaran<span class="text-danger">*</span></label
            >
            <Multiselect
              v-model="form.description"
              :options="descDrop"
              :searchable="true"
              placeholder="-- Pilih Periode Putaran --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" class="btn-success-fordone btn-sm" @click.prevent="copy">
          Salin
        </button>
      </template>
    </ModalBs>
  </GeneralLayout>
</template>
<script setup>
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import LapusResultTable from "@/Components/LapusResultTable.vue";
import LapusTable from "@/Components/LapusTable.vue";
import ModalBs from "@/Components/ModalBs.vue";
import PengResultTable from "@/Components/PengResultTable.vue";
import PengTable from "@/Components/PengTable.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { onMounted, ref, watch } from "vue";

const page = usePage();
const dataset = ref({});
const subsectors = ref(page.props.subsectors);
const dataContents = ref([]);
const dataBefore = ref([]);
const computedData = ref({});
const quarterCap = ref("4");
const showTabPanel = ref(false);
const copyModal = ref(false);
const dataOnDemand = ref({ adhb_now: {}, adhb_prev: {}, adhk_now: {}, adhk_prev: {} });
const form = useForm({
  dataContents: null,
  _token: null,
  type: page.props.type,
  year: null,
  quarter: null,
  description: null,
  dataBefore: null,
  regions: null,
});
const copyForm = useForm({
  _token: null,
  type: page.props.type,
  year: null,
  quarter: null,
  description: null,
  regions: form.regions,
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
const yearDrop = ref([]);
const quarterDrop = ref([]);
const descDrop = ref([]);
const dataBeforeDrop = ref([]);
onMounted(() => {
  fetchYear();
});
const updateDOD = (data) => {
  dataOnDemand.value[data.type] = data.data;
};
const updateDataContents = (data) => {
  dataContents.value = data;
};
// #region Section: FETCH
const fetchYear = async (value) => {
  try {
    const response = await axios.get(route("period.fetchYear"), {
      params: {
        type: page.props.type,
      },
    });
    let result = response.data;
    yearDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const fetchQuarter = async (value) => {
  try {
    const response = await axios.get(route("period.fetchQuarter"), {
      params: {
        type: page.props.type,
        year: value,
      },
    });
    let result = response.data;
    quarterDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const fetchPeriod = async (value) => {
  try {
    const response = await axios.get(route("period.fetchPeriod"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: value,
      },
    });
    let result = response.data;
    descDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const fetchYearBefore = async (value) => {
  try {
    const response = await axios.get(route("period.fetchYearBefore"), {
      params: {
        type: page.props.type,
        year: form.year,
      },
    });
    let result = response.data;
    dataBeforeDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const showPdrbAndResult = ref({
  adhb: false,
  adhk: false,
  result: true,
});
const submit = async () => {
  try {
    const response = await axios.get(route("pdrb.show"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: form.quarter,
        description: form.description,
        dataBefore: form.dataBefore,
        regions: form.regions,
      },
    });
    quarterCap.value = form.quarter;
    dataContents.value = response.data.current_data;
    dataBefore.value = response.data.previous_data;
    showPdrbAndResult.value.result = false;
    showPdrbAndResult.value.adhb = true;
    showTabPanel.value = true;
    dataset.value = response.data.dataset;
    showNotification(response.data.notification);
    showTab("adhb");
  } catch (error) {}
};
// #endregion

var searchFormDefault = {};
const openCopyModal = () => {
  copyModal.value = true;
  searchFormDefault.value = form;
  copyForm.year = form.year;
  copyForm.quarter = form.quarter;
  copyForm.description = form.description;
};
const triggerSpinner = ref(false);
watch(
  () => dataContents.value,
  (value) => {}
);

var def = "btn-info-fordone";
const activeTab = ref({
  adhb: def,
  adhk: def,
  dist: def,
  g_qtoq: def,
  g_ytoy: def,
  g_ctoc: def,
  indeks: def,
  gi_qtoq: def,
  gi_ytoy: def,
});
const setActiveTab = (value) => {
  return activeTab.value[value];
};

const resetShowTable = () => {
  Object.keys(showPdrbAndResult.value).forEach((key) => {
    showPdrbAndResult.value[key] = false;
  });
};
// #region Section: CHANGE NAV TAB
const showTab = (tab) => {
  Object.keys(activeTab.value).forEach((key) => {
    activeTab.value[key] = def;
  });
  activeTab.value[tab] = "btn-success-fordone";

  resetShowTable();
  if (tab == "adhb") {
    showPdrbAndResult.value.adhb = true;
  }
  if (tab == "adhk") {
    showPdrbAndResult.value.adhk = true;
  }
  if (tab == "dist") {
    showPdrbAndResult.value.result = true;
    showDist();
  }
  if (tab == "g_qtoq") {
    showPdrbAndResult.value.result = true;
    showGQtoQ();
  }
  if (tab == "g_ytoy") {
    showPdrbAndResult.value.result = true;
    showGYtoY();
  }
  if (tab == "g_ctoc") {
    showPdrbAndResult.value.result = true;
    showGCtoC();
  }
  if (tab == "indeks") {
    showPdrbAndResult.value.result = true;
    showIndeks();
  }
  if (tab == "gi_qtoq") {
    showPdrbAndResult.value.result = true;
    showGIQtoQ();
  }
  if (tab == "gi_ytoy") {
    showPdrbAndResult.value.result = true;
    showGIYtoY();
  }
};
const showDist = () => {
  let dataset = dataOnDemand.value["adhb_now"];
  let arrayPDRB = dataset["PDRB"];
  let stake = Number(quarterCap.value); // Current quarter
  // Helper function to parse numbers safely
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let result = {};
  Object.keys(dataset).forEach((key) => {
    result[key] = dataset[key].map((value, index) => {
      // Include index 0 to (stake - 1) and always include index 4
      if (index < stake || index === 4) {
        let divisor = parseNumber(arrayPDRB[index]); // Get corresponding PDRB value
        let dividend = parseNumber(value); // Convert current value to number
        let dist = divisor !== 0 ? (dividend / divisor) * 100 : 0; // Avoid division by zero
        return formatNumberGerman(dist.toFixed(4), 2, 4);
      }
    });
  });
  computedData.value = removeSpaceOnKomponen(result);
};

const showGQtoQ = () => {
  let current_dataset = dataOnDemand.value["adhk_now"];
  let previous_dataset = dataOnDemand.value["adhk_prev"];
  // Clean up spaces
  current_dataset = removeSpaceOnKomponen(current_dataset);
  previous_dataset = removeSpaceOnKomponen(previous_dataset);
  // Get only the 4th index (previous quarter) from `previous_dataset`
  previous_dataset = Object.fromEntries(
    Object.entries(previous_dataset).map(([key, arr]) => [key, arr[3]])
  );
  let result = {};
  let stake = Number(quarterCap.value); // Set correct loop range
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key]
      .slice(0, stake) // Ensures only the required indexes are processed
      .map((value, index) => {
        let dividend = value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
        let divisor;
        if (index === 0) {
          // Use the previous dataset for the first quarter
          divisor = previous_dataset[key]
            ? Number(previous_dataset[key].replaceAll(".", "").replaceAll(",", "."))
            : 0;
        } else {
          // Use the previous index from the same dataset
          divisor = current_dataset[key][index - 1]
            ? Number(
                current_dataset[key][index - 1].replaceAll(".", "").replaceAll(",", ".")
              )
            : 0;
        }
        let growth =
          divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
        return formatNumberGerman(growth.toFixed(4), 2, 4);
      });
  });
  computedData.value = removeSpaceOnKomponen(result);
};

const showGYtoY = () => {
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_now"]);
  let previous_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_prev"]);
  if (isObjectEmpty(previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif);
    showPdrbAndResult.value.result = false;
    return;
  }
  let stake = quarterCap.value == 4 ? 5 : Number(quarterCap.value); // Set max iteration limit

  let result = {};
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key]
      .slice(0, stake) // Only process relevant indexes
      .map((value, index) => {
        let dividend = value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
        let divisor = previous_dataset[key][index]
          ? Number(previous_dataset[key][index].replaceAll(".", "").replaceAll(",", "."))
          : 0;

        let growth =
          divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
        return formatNumberGerman(growth.toFixed(4), 2, 4);
      });
  });

  computedData.value = removeSpaceOnKomponen(result);
};

const showGCtoC = () => {
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_now"]);
  let previous_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_prev"]);
  if (isObjectEmpty(previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif);
    showPdrbAndResult.value.result = false;
    return;
  }
  let stake = Number(quarterCap.value); // Max quarter index to loop through
  // Helper function to convert values to numbers
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let result = {};
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key].slice(0, stake).map((_, index) => {
      let dividend = 0,
        divisor = 0;
      // Cumulative sum for each index
      for (let cumulative = 0; cumulative <= index; cumulative++) {
        dividend += parseNumber(current_dataset[key][cumulative]);
        divisor += parseNumber(previous_dataset[key][cumulative]);
      }
      let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
      return formatNumberGerman(growth.toFixed(4), 2, 4);
    });
  });
  computedData.value = removeSpaceOnKomponen(result);
};

const showIndeks = () => {
  // current = ADHB, previous = ADHK
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhb_now"]);
  let previous_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_now"]);
  let stake = Number(quarterCap.value); // Defines the quarter limit
  // Helper function to parse numbers
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let result = {};
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key].slice(0, stake).map((value, index) => {
      let dividend = parseNumber(value);
      let divisor = parseNumber(previous_dataset[key][index]);
      let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
      return formatNumberGerman(indeks.toFixed(4), 2, 4);
    });
  });
  computedData.value = removeSpaceOnKomponen(result);
};

const showGIQtoQ = () => {
  let adhb_current_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhb_now"]);
  let adhk_current_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_now"]);
  let adhb_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhb_prev"]);
  let adhk_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_prev"]);
  if (isObjectEmpty(adhb_previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif);
    showPdrbAndResult.value.result = false;
    return;
  }
  adhb_previous_dataset = Object.fromEntries(
    Object.entries(adhb_previous_dataset).map(([key, arr]) => [key, arr[3]])
  );
  adhk_previous_dataset = Object.fromEntries(
    Object.entries(adhk_previous_dataset).map(([key, arr]) => [key, arr[3]])
  );
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let stake = Number(quarterCap.value);
  let indeks_implisit_previous = {};
  Object.keys(adhb_previous_dataset).forEach((key) => {
    let dividend = parseNumber(adhb_previous_dataset[key]);
    let divisor = parseNumber(adhk_previous_dataset[key]);
    let indeks = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 : 0;
    indeks_implisit_previous[key] = formatNumberGerman(indeks.toFixed(4), 2, 4);
  });
  let indeks_implisit_current = {};
  Object.keys(adhb_current_dataset).forEach((key) => {
    indeks_implisit_current[key] = adhb_current_dataset[key]
      .slice(0, stake)
      .map((value, index) => {
        let dividend = parseNumber(value);
        let divisor = parseNumber(adhk_current_dataset[key][index]);
        let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
        return formatNumberGerman(indeks.toFixed(4), 2, 4);
      });
  });
  let result = {};
  Object.keys(indeks_implisit_current).forEach((key) => {
    result[key] = indeks_implisit_current[key].map((value, index) => {
      let dividend = parseNumber(value);
      let divisor;
      if (index === 0) {
        // Use the previous dataset for the first quarter
        divisor = parseNumber(indeks_implisit_previous[key]);
      } else {
        // Use the previous index from the same dataset
        divisor = parseNumber(indeks_implisit_current[key][index - 1]);
      }
      let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
      return formatNumberGerman(growth.toFixed(4), 2, 4);
    });
  });
  computedData.value = result;
};
const showGIYtoY = () => {
  let adhb_current_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhb_now"]);
  let adhk_current_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_now"]);
  let adhb_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhb_prev"]);
  let adhk_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value["adhk_prev"]);
  if (isObjectEmpty(adhb_previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif);
    showPdrbAndResult.value.result = false;
    return;
  }
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let stake = quarterCap.value == 4 ? 5 : Number(quarterCap.value);
  let indeks_implisit_previous = {};
  Object.keys(adhb_previous_dataset).forEach((key) => {
    indeks_implisit_previous[key] = adhb_previous_dataset[key]
      .slice(0, stake)
      .map((value, index) => {
        let dividend = parseNumber(value);
        let divisor = parseNumber(adhk_previous_dataset[key][index]);
        let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
        return formatNumberGerman(indeks.toFixed(4), 2, 4);
      });
  });
  let indeks_implisit_current = {};
  Object.keys(adhb_current_dataset).forEach((key) => {
    indeks_implisit_current[key] = adhb_current_dataset[key]
      .slice(0, stake)
      .map((value, index) => {
        let dividend = parseNumber(value);
        let divisor = parseNumber(adhk_current_dataset[key][index]);
        let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
        return formatNumberGerman(indeks.toFixed(4), 2, 4);
      });
  });
  let result = {};
  Object.keys(indeks_implisit_current).forEach((key) => {
    result[key] = indeks_implisit_current[key].map((value, index) => {
      let dividend = parseNumber(value);
      let divisor = parseNumber(indeks_implisit_previous[key][index]);
      let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
      return formatNumberGerman(growth.toFixed(4), 2, 4);
    });
  });
  computedData.value = result;
};
// #endregion
const removeSpaceOnKomponen = (object) => {
  let result;
  result = Object.fromEntries(
    Object.entries(object).map(([key, value]) => [key.trim().replace(/\s+/g, ""), value])
  );
  return result;
};
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const isObjectEmpty = (obj) => {
  return !obj || Object.keys(obj).length === 0;
};
// #region Section: Save & Submit & Unsubmit
const saveEntri = async () => {
  const thisForm = useForm({
    dataContents: dataContents.value,
    type: page.props.type,
    _token: null,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("pdrb.save-entri"), {
    onBefore: () => {
      triggerSpinner.value = true;
    },
    onFinish: () => {
      triggerSpinner.value = false;
    },
    onSuccess: (response) => {
      showNotification(response.props.notification);
    },
  });
};
const submitEntri = async () => {
  const thisForm = useForm({
    id: dataset.value.id,
    type: page.props.type,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("pdrb.submit-entri"), {
    onBefore: () => {
      triggerSpinner.value = true;
    },
    onFinish: () => {
      triggerSpinner.value = false;
    },
    onSuccess: (response) => {
      showNotification(response.props.notification);
      if (response.props.notification[0].type == "success")
        dataset.value.status = "Submitted";
    },
  });
};
const unsubmitEntri = async () => {
  const thisForm = useForm({
    id: dataset.value.id,
    type: page.props.type,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("pdrb.unsubmit-entri"), {
    onBefore: () => {
      triggerSpinner.value = true;
    },
    onFinish: () => {
      triggerSpinner.value = false;
    },
    onSuccess: (response) => {
      showNotification(response.props.notification);
      if (response.props.notification[0].type == "success")
        dataset.value.status = "Entry";
    },
  });
};
// #endregion
</script>

<style scoped>
.fixed-thead {
  position: sticky;
  width: 400px;
  left: 0;
  background-color: #175676;
  color: whitesmoke;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.table {
  table-layout: fixed;
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  /* Avoid extra spacing */
}
</style>
