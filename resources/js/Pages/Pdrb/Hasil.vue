<template>
  <Head title="Hasil Adjustment" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Hasil PDRB</label>
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
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.year }}
            </div>
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
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.quarter }}
            </div>
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
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.description }}
            </div>
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
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.regions }}
            </div>
          </div>
          <div class="flex items-center space-x-2 justify-end">
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
      <div
        v-if="showTabPanel"
        class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3"
      >
        <div class="p-5">
          <div class="flex flex-wrap gap-2">
            <button @click="showSummary('full')" :class="setActiveTab('full')">
              Tabel Lengkap
            </button>
            <button @click="showSummary('summary')" :class="setActiveTab('summary')">
              Tabel Summary
            </button>
            <button
              @click="downloadHasil('tabel-entry')"
              class="btn btn-warning-fordone ml-auto"
            >
              Download
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
            <LapusHasilSummary
              v-show="showPdrbAndResult['adhb'] && !isFull"
              :data-contents="dataContentsSummary"
              :type="'adhb'"
              :onDemandType="'adhb_summary_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <LapusHasil
              v-show="showPdrbAndResult['adhb'] && isFull"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->
            <!-- #region Section: ADHK -->
            <LapusHasilSummary
              v-show="showPdrbAndResult['adhk'] && !isFull"
              :data-contents="dataContentsSummary"
              :type="'adhk'"
              :onDemandType="'adhk_summary_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <LapusHasil
              v-show="showPdrbAndResult['adhk'] && isFull"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->
            <!-- #region Section: RESULT -->
            <LapusHasilSummaryResult
              v-show="showPdrbAndResult['result'] && !isFull"
              :type="'distribusi'"
              :quarter-cap="quarterCap"
              :computed-data="computedSummaryData"
            />
            <LapusHasilResult
              v-show="showPdrbAndResult['result'] && isFull"
              :subsectors="subsectors"
              :type="'distribusi'"
              :quarter-cap="quarterCap"
              :computed-data="computedData"
            />
            <!-- #endregion -->

            <!-- #region Section: PREV_DATA -->
            <!-- #region Section: ADHB -->
            <LapusHasilSummary
              v-show="false"
              :data-contents="dataBeforeSummary"
              :type="'adhb'"
              :onDemandType="'adhb_summary_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <LapusHasil
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->

            <!-- #region Section: ADHK -->
            <LapusHasilSummary
              v-show="false"
              :data-contents="dataBeforeSummary"
              :type="'adhk'"
              :onDemandType="'adhk_summary_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <LapusHasil
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->
            <!-- #endregion -->
          </template>
          <template v-if="page.props.type == 'Pengeluaran'">
            <!-- #region Section: ADHB -->
            <PengHasilSummary
              v-show="showPdrbAndResult['adhb'] && !isFull"
              :data-contents="dataContentsSummary"
              :type="'adhb'"
              :onDemandType="'adhb_summary_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <PengHasil
              v-show="showPdrbAndResult['adhb'] && isFull"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->
            <!-- #region Section: ADHK -->
            <PengHasilSummary
              v-show="showPdrbAndResult['adhk'] && !isFull"
              :data-contents="dataContentsSummary"
              :type="'adhk'"
              :onDemandType="'adhk_summary_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <PengHasil
              v-show="showPdrbAndResult['adhk'] && isFull"
              :data-contents="dataContents"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_now'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->
            <!-- #region Section: RESULT -->
            <PengHasilSummaryResult
              v-show="showPdrbAndResult['result'] && !isFull"
              :type="'distribusi'"
              :quarter-cap="quarterCap"
              :computed-data="computedSummaryData"
            />
            <PengHasilResult
              v-show="showPdrbAndResult['result'] && isFull"
              :subsectors="subsectors"
              :type="'distribusi'"
              :quarter-cap="quarterCap"
              :computed-data="computedData"
            />
            <!-- #endregion -->

            <!-- #region Section: PREV_DATA -->
            <!-- #region Section: ADHB -->
            <PengHasilSummary
              v-show="false"
              :data-contents="dataBeforeSummary"
              :type="'adhb'"
              :onDemandType="'adhb_summary_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <PengHasil
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhb'"
              :onDemandType="'adhb_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->

            <!-- #region Section: ADHK -->
            <PengHasilSummary
              v-show="false"
              :data-contents="dataBeforeSummary"
              :type="'adhk'"
              :onDemandType="'adhk_summary_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <PengHasil
              v-show="false"
              :data-contents="dataBefore"
              :subsectors="subsectors"
              :type="'adhk'"
              :onDemandType="'adhk_prev'"
              :quarter-cap="quarterCap"
              @update:update-d-o-d="updateDOD"
            />
            <!-- #endregion -->
            <!-- #endregion -->
          </template>
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import LapusHasil from "@/Components/LapusHasil.vue";
import LapusHasilResult from "@/Components/LapusHasilResult.vue";
import LapusHasilSummary from "@/Components/LapusHasilSummary.vue";
import LapusHasilSummaryResult from "@/Components/LapusHasilSummaryResult.vue";
import PengHasil from "@/Components/PengHasil.vue";
import PengHasilResult from "@/Components/PengHasilResult.vue";
import PengHasilSummary from "@/Components/PengHasilSummary.vue";
import PengHasilSummaryResult from "@/Components/PengHasilSummaryResult.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { tableToJson, theDownload } from "@/download";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { nextTick, onMounted, ref } from "vue";

const page = usePage();
const form = useForm({
  _token: null,
  type: page.props.type,
  year: null,
  quarter: null,
  description: null,
  dataBefore: null,
  regions: null,
});
const formError = ref({
  year: null,
  quarter: null,
  description: null,
  regions: null,
});
const dataContents = ref([]);
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
const dataOnDemand = ref({
  adhb_summary_now: {},
  adhb_summary_prev: {},
  adhk_summary_now: {},
  adhk_summary_prev: {},
  adhb_now: {},
  adhb_prev: {},
  adhk_now: {},
  adhk_prev: {},
});
const showTabPanel = ref(false);
const quarterCap = ref("4");
const subsectors = ref(page.props.subsectors);
const dataBefore = ref([]);
const computedData = ref({});
const computedSummaryData = ref({});
const dataContentsSummary = ref([]);
const dataBeforeSummary = ref([]);

const updateDOD = (data) => {
  dataOnDemand.value[data.type] = data.data;
};
// #region Section: FETCH
onMounted(() => {
  fetchYear();
});
const fetchYear = async () => {
  form.quarter = null;
  form.description = null;
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
  form.quarter = null;
  form.description = null;
  if (value) {
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
  }
};
const fetchPeriod = async (value) => {
  form.description = null;
  if (value) {
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
const isFull = ref(true);
const submit = async () => {
  try {
    const response = await axios.get(route("pdrb.get-hasil"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: form.quarter,
        description: form.description,
        dataBefore: form.dataBefore,
        regions: form.regions,
      },
    });
    formError.value = {
      year: null,
      quarter: null,
      description: null,
      regions: null,
    };
    quarterCap.value = form.quarter;
    dataContents.value = response.data.current_data;
    dataBefore.value = response.data.previous_data;
    dataContentsSummary.value = response.data.current_summary_set;
    dataBeforeSummary.value = response.data.previous_summary_set;
    showPdrbAndResult.value.result = false;
    showPdrbAndResult.value.adhb = true;
    showTabPanel.value = true;
    showNotification(response.data.notification);
    showTab("adhb");
    showSummary("full");
  } catch (error) {
    // Display notification if available
    if (error.response.data.notification) {
      showNotification(error.response.data.notification);
    }

    // Handle validation errors if they exist
    if (error.response.data.errors) {
      formError.value = Object.keys(error.response.data.errors).reduce((acc, key) => {
        acc[key] = error.response.data.errors[key][0];
        return acc;
      }, {});
    }
  }
};
// #endregion

// #region Section: CHANGE NAV TAB
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
  full: def,
  summary: def,
});
const setActiveTab = (value) => {
  return activeTab.value[value];
};

const resetShowTable = () => {
  Object.keys(showPdrbAndResult.value).forEach((key) => {
    showPdrbAndResult.value[key] = false;
  });
};
const showTab = (tab) => {
  Object.keys(activeTab.value).forEach((key) => {
    if (key == "full" || key == "summary") return;
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
    computedData.value = showDist("adhb_now");
    computedSummaryData.value = showDist("adhb_summary_now");
  }
  if (tab == "g_qtoq") {
    showPdrbAndResult.value.result = true;
    computedData.value = showGQtoQ("adhk_now", "adhk_prev");
    computedSummaryData.value = showGQtoQ("adhk_summary_now", "adhk_summary_prev");
  }
  if (tab == "g_ytoy") {
    showPdrbAndResult.value.result = true;
    computedData.value = showGYtoY("adhk_now", "adhk_prev");
    computedSummaryData.value = showGYtoY("adhk_summary_now", "adhk_summary_prev");
  }
  if (tab == "g_ctoc") {
    showPdrbAndResult.value.result = true;
    computedData.value = showGCtoC("adhk_now", "adhk_prev");
    computedSummaryData.value = showGCtoC("adhk_summary_now", "adhk_summary_prev");
  }
  if (tab == "indeks") {
    showPdrbAndResult.value.result = true;
    computedData.value = showIndeks("adhb_now", "adhk_now");
    computedSummaryData.value = showIndeks("adhb_summary_now", "adhk_summary_now");
  }
  if (tab == "gi_qtoq") {
    showPdrbAndResult.value.result = true;
    computedData.value = showGIQtoQ("adhb_now", "adhb_prev", "adhk_now", "adhk_prev");
    computedSummaryData.value = showGIQtoQ(
      "adhb_summary_now",
      "adhb_summary_prev",
      "adhk_summary_now",
      "adhk_summary_prev"
    );
  }
  if (tab == "gi_ytoy") {
    showPdrbAndResult.value.result = true;
    computedData.value = showGIYtoY("adhb_now", "adhb_prev", "adhk_now", "adhk_prev");
    computedSummaryData.value = showGIYtoY(
      "adhb_summary_now",
      "adhb_summary_prev",
      "adhk_summary_now",
      "adhk_summary_prev"
    );
  }
};
const showSummary = (tab) => {
  if (tab == "full") activeTab.value["summary"] = def;
  if (tab == "summary") activeTab.value["full"] = def;
  activeTab.value[tab] = "btn-success-fordone";
  let currentActive = Object.entries(activeTab.value).find(
    ([key, value]) => value != def
  );
  showTab(currentActive[0]);
  if (tab == "full") {
    isFull.value = true;
  }
  if (tab == "summary") {
    isFull.value = false;
  }
};
const showDist = (data) => {
  let dataset = dataOnDemand.value[data];
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
  //   computedData.value = removeSpaceOnKomponen(result);
  return removeSpaceOnKomponen(result);
};

const showGQtoQ = (now, prev) => {
  let current_dataset = dataOnDemand.value[now];
  let previous_dataset = dataOnDemand.value[prev];
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
  //   computedData.value = removeSpaceOnKomponen(result);
  return removeSpaceOnKomponen(result);
};

const showGYtoY = (now, prev) => {
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value[now]);
  let previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[prev]);
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

  //   computedData.value = removeSpaceOnKomponen(result);
  return removeSpaceOnKomponen(result);
};

const showGCtoC = (now, prev) => {
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value[now]);
  let previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[prev]);
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
  //   computedData.value = removeSpaceOnKomponen(result);
  return removeSpaceOnKomponen(result);
};

const showIndeks = (now, prev) => {
  // current = ADHB, previous = ADHK
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value[now]);
  let previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[prev]);
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
  //   computedData.value = removeSpaceOnKomponen(result);
  return removeSpaceOnKomponen(result);
};

const showGIQtoQ = (adhbnow, adhbprev, adhknow, adhkprev) => {
  let adhb_current_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhbnow]);
  let adhk_current_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhknow]);
  let adhb_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhbprev]);
  let adhk_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhkprev]);
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
  //   computedData.value = result;
  return result;
};

const showGIYtoY = (adhbnow, adhbprev, adhknow, adhkprev) => {
  let adhb_current_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhbnow]);
  let adhk_current_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhknow]);
  let adhb_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhbprev]);
  let adhk_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhkprev]);
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
  //   computedData.value = result;
  return result;
};

const removeSpaceOnKomponen = (object) => {
  let result;
  result = Object.fromEntries(
    Object.entries(object).map(([key, value]) => [key.trim().replace(/\s+/g, ""), value])
  );
  return result;
};

const isObjectEmpty = (obj) => {
  return !obj || Object.keys(obj).length === 0;
};

const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const downloadHasil = async (id) => {
  let list = {};
  triggerSpinner.value = true;
  try {
    showSummary("full");
    await nextTick();
    for (const key of Object.keys(activeTab.value)) {
      if (key === "full" || key === "summary") continue;
      showTab(key);
      await new Promise((resolve) => setTimeout(resolve, 800));
      list[key] = tableToJson(id);
    }
    showSummary("summary");
    await nextTick();
    for (const key of Object.keys(activeTab.value)) {
      if (key === "full" || key === "summary") continue;
      showTab(key);
      await new Promise((resolve) => setTimeout(resolve, 800));
      list[key + "-summary"] = tableToJson(id);
    }
    theDownload(list);
  } catch (error) {
    console.error(error);
  } finally {
    triggerSpinner.value = false;
  }
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
