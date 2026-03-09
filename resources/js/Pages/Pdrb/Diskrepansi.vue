<template>
  <Head title="Diskrepansi" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout :entri="mountThis">
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div class="px-[5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="p-3">
          <div class="flex flex-items items-center gap-5">
            <div class="w-full flex gap-5">
              <div class="w-1/5 space-y-2">
                <label class="whitespace-nowrap w-28 text-sm font-medium" for="type"
                  >Pilih PDRB<span class="text-danger">*</span></label
                >
                <Multiselect v-model="form.type" :placeholder="form.type" disabled />
                <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
              </div>
              <div class="w-1/5 space-y-2">
                <label class="whitespace-nowrap w-28 text-sm font-medium" for="year"
                  >Pilih Tahun<span class="text-danger">*</span></label
                >
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
              <div class="w-1/5 space-y-2">
                <label class="whitespace-nowrap w-28 text-sm font-medium" for="year"
                  >Pilih Triwulan<span class="text-danger">*</span></label
                >
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
              <div class="w-1/5 space-y-2">
                <label class="whitespace-nowrap w-28 text-sm font-medium" for="year"
                  >Pilih Periode Putaran<span class="text-danger">*</span></label
                >
                <Multiselect
                  v-model="form.description"
                  :options="descDrop"
                  :searchable="true"
                  placeholder="-- Pilih Periode --"
                  @change="fetchYearBefore"
                />
                <div class="text-danger text-left" v-if="true" id="error-dinas">
                  {{ formError.description }}
                </div>
              </div>
              <div class="w-1/5 space-y-2">
                <label class="whitespace-nowrap w-28 text-sm font-medium" for="year"
                  >Pilih Data Tahun Sebelumnya</label
                >
                <Multiselect
                  v-model="form.dataBefore"
                  :options="dataBeforeDrop"
                  :searchable="true"
                  placeholder="-- Pilih Periode Sebelumnya --"
                />
                <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-end">
            <button @click.prevent="submit" class="btn btn-info-fordone">
              Lihat Data
            </button>
          </div>
        </div>
      </div>
      <div
        v-if="showTabPanel"
        class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3"
      >
        <div class="p-3">
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
        <div class="p-3">
          <div class="flex flex-wrap gap-2">
            <button
              v-if="listTab[1]"
              @click="quartersTab('1')"
              :class="setActiveQuarter('1')"
            >
              Triwulan I
            </button>
            <button
              v-if="listTab[2]"
              @click="quartersTab('2')"
              :class="setActiveQuarter('2')"
            >
              Triwulan II
            </button>
            <button
              v-if="listTab[3]"
              @click="quartersTab('3')"
              :class="setActiveQuarter('3')"
            >
              Triwulan III
            </button>
            <button
              v-if="listTab[4]"
              @click="quartersTab('4')"
              :class="setActiveQuarter('4')"
            >
              Triwulan IV
            </button>
            <button
              v-if="listTab['t']"
              @click="quartersTab('t')"
              :class="setActiveQuarter('t')"
            >
              Tahunan
            </button>
            <button
              class="btn btn-success-fordone ml-auto"
              @click="addToFixedValue(true)"
            >
              0,00 ->>
            </button>
            <button class="btn btn-red-fordone" @click="addToFixedValue(false)">
              <<- 0,00
            </button>
            <button @click="downloadModalStatus = true" class="btn btn-warning-fordone">
              Download
            </button>
          </div>
        </div>
      </div>
      <div class="overflow-x-scroll">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="fixed-thead">Komponen</th>
              <th class="value-thead" v-for="(node, index) in tableColumn" :key="index">
                {{ node.label }}
              </th>
            </tr>
          </thead>
          <template v-if="page.props.type == 'Lapangan Usaha'">
            <DiskrepansiLapus
              v-show="showPdrbAndResult['adhb']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataContents"
              :type="'adhb'"
              :on-demand-type="'adhb_now'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
            <DiskrepansiLapus
              v-show="showPdrbAndResult['adhk']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataContents"
              :type="'adhk'"
              :on-demand-type="'adhk_now'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
            <DiskrepansiLapusResult
              v-show="showPdrbAndResult['result']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :quarter="quarterCap"
              :type="'distribusi'"
              :computed-data="computedData"
              @update:update-d-o-d="updateDOD"
            />
            <DiskrepansiLapus
              v-show="false"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataBefore"
              :type="'adhb'"
              :on-demand-type="'adhb_prev'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
            <DiskrepansiLapus
              v-show="false"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataBefore"
              :type="'adhk'"
              :on-demand-type="'adhk_prev'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
          </template>
          <template v-if="page.props.type == 'Pengeluaran'">
            <DiskrepansiPeng
              v-show="showPdrbAndResult['adhb']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataContents"
              :type="'adhb'"
              :on-demand-type="'adhb_now'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
            <DiskrepansiPeng
              v-show="showPdrbAndResult['adhk']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataContents"
              :type="'adhk'"
              :on-demand-type="'adhk_now'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
            <DiskrepansiPengResult
              v-show="showPdrbAndResult['result']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :quarter="quarterCap"
              :type="'distribusi'"
              :computed-data="computedData"
              @update:update-d-o-d="updateDOD"
            />
            <DiskrepansiPeng
              v-show="false"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataBefore"
              :type="'adhb'"
              :on-demand-type="'adhb_prev'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
            <DiskrepansiPeng
              v-show="false"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataBefore"
              :type="'adhk'"
              :on-demand-type="'adhk_prev'"
              :quarter="quarterCap"
              @update:update-d-o-d="updateDOD"
              :to-fixed="toFixed"
              :regions="page.props.regions"
            />
          </template>
        </table>
        <div v-if="loadingWarn" class="text-center">
          Data masih loading . . . dan cukup lama
        </div>
      </div>
    </div>
    <ModalBs
      :-modal-status="downloadModalStatus"
      @close="downloadModalStatus = false"
      :title="'Download Data'"
    >
      <template #modalBody>
        <div class="mb-3 space-y-2">
          <label>Masukkan Judul File</label>
          <input type="text" v-model="downloadTitle" class="input-fordone w-full" />
        </div>
      </template>
      <template #modalFunction>
        <button
          type="button"
          class="btn-success-fordone btn-sm"
          @click.prevent="downloadHasil('tabel-entry', downloadTitle, downloadType)"
        >
          Download
        </button>
      </template>
    </ModalBs>
  </GeneralLayout>
</template>
<script setup>
import { triggerSpinner } from "@/axiosSetup";
import DiskrepansiLapus from "@/Components/DiskrepansiLapus.vue";
import DiskrepansiLapusResult from "@/Components/DiskrepansiLapusResult.vue";
import DiskrepansiPeng from "@/Components/DiskrepansiPeng.vue";
import DiskrepansiPengResult from "@/Components/DiskrepansiPengResult.vue";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import ModalBs from "@/Components/ModalBs.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import {
  buildAOADiskrepansi,
  buildRowDefsLapus,
  buildRowDefsPeng,
  tableToJson,
  theDownload,
} from "@/download";
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
});
const formError = ref({
  year: null,
  quarter: null,
  description: null,
});
const loadingWarn = ref(false);
const notifications = ref([]);
const showNotification = (notification, delay = 200) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * delay); // Delay based on index
  });
};
const listTab = ref({
  1: false,
  2: false,
  3: false,
  4: false,
  t: false,
});
const mountThis = ref(false);
const dataContents = ref([]);
const dataBefore = ref([]);
const computedData = ref({});
const dataOnDemand = ref({
  adhb_now: {},
  adhb_now_disk: {},
  adhb_prev: {},
  adhk_now: {},
  adhk_now_disk: {},
  adhk_prev: {},
  computed_diff: {},
});
const calculateData = ref({
  adhb: [],
  adhk: [],
});
const showTabPanel = ref(false);
const yearDrop = ref([]);
const quarterDrop = ref([]);
const descDrop = ref([]);
const dataBeforeDrop = ref([]);
const tableColumn = ref([]);
const quarterCap = ref("4");
onMounted(() => {
  fetchYear();
  let tempData = [];
  page.props.regions.forEach((element, index) => {
    if (index == 0) {
      tempData.push({ label: "Calculate", value: "calculate" }, element, {
        label: "Total Kabupaten/Kota",
        value: "total",
      });
    } else {
      tempData.push(element);
    }
  });
  tableColumn.value = tempData;
});
const updateDOD = (data) => {
  dataOnDemand.value[data.type] = data.data;
};
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
const submit = async () => {
  loadingWarn.value = true;
  try {
    const response = await axios.get(route("pdrb.get-diskrepansi"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: form.quarter,
        description: form.description,
        dataBefore: form.dataBefore,
      },
    });
    for (let index = 1; index <= Number(form.quarter); index++) {
      if (index == 4) {
        listTab.value.t = true;
      }
      listTab.value[index] = true;
    }

    dataBefore.value = response.data.previous_data;
    dataContents.value = response.data.current_data;
    formError.value = {
      year: null,
      quarter: null,
      description: null,
      regions: null,
    };
    showTabPanel.value = true;
    loadingWarn.value = false;
    showTab("adhb");
    showNotification(response.data.notification);
    quartersTab(form.quarter);
  } catch (error) {
    if (error.response) {
      if (error.response.data.notification) {
        showNotification(error.response.data.notification, 500);
      }
      if (error.response.data.errors) {
        formError.value = Object.keys(error.response.data.errors).reduce((acc, key) => {
          acc[key] = error.response.data.errors[key][0];
          return acc;
        }, {});
      }
    }
  }
};
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
const activeQuarters = ref({
  1: def,
  2: def,
  3: def,
  4: def,
  t: def,
});
const showPdrbAndResult = ref({
  adhb: false,
  adhk: false,
  result: false,
});
const setActiveTab = (value) => {
  return activeTab.value[value];
};
const setActiveQuarter = (value) => {
  return activeQuarters.value[value];
};
const quartersTab = (quarter) => {
  quarterCap.value = quarter;
  Object.keys(activeQuarters.value).forEach((key) => {
    activeQuarters.value[key] = def;
  });
  activeQuarters.value[quarter] = "btn-success-fordone";
  let currentActive = Object.entries(activeTab.value).find(
    ([key, value]) => value != def
  );
  showTab(currentActive[0]);
};
const resetShowTable = () => {
  Object.keys(showPdrbAndResult.value).forEach((key) => {
    if (key != "result") {
      showPdrbAndResult.value[key] = false;
    } else {
      showPdrbAndResult.value.result = false;
    }
  });
};
const showTab = async (tab) => {
  Object.keys(activeTab.value).forEach((key) => {
    activeTab.value[key] = def;
  });
  activeTab.value[tab] = "btn-success-fordone";

  resetShowTable();
  if (tab == "adhb") {
    tableColumn.value[0].label = "Diskrepansi";
    showPdrbAndResult.value.adhb = true;
  }
  if (tab == "adhk") {
    tableColumn.value[0].label = "Diskrepansi";
    showPdrbAndResult.value.adhk = true;
  }
  if (tab == "dist") {
    tableColumn.value[0].label = "Selisih";
    showPdrbAndResult.value.result = true;
    computedData.value = showDist("adhb_now");
  }
  if (tab == "g_qtoq") {
    tableColumn.value[0].label = "Selisih";
    if (quarterCap.value == "t") {
      let notif = [{ message: "Tidak ada Growth QtoQ untuk Tahunan", type: "error" }];
      showNotification(notif, 10000);
      showPdrbAndResult.value.result = false;
      return;
    }
    showPdrbAndResult.value.result = true;
    computedData.value = showGQtoQ("adhk_now", "adhk_prev");
  }
  if (tab == "g_ytoy") {
    tableColumn.value[0].label = "Selisih";
    showPdrbAndResult.value.result = true;
    computedData.value = showGYtoY("adhk_now", "adhk_prev");
  }
  if (tab == "g_ctoc") {
    tableColumn.value[0].label = "Selisih";
    if (quarterCap.value == "t") {
      let notif = [
        { message: "Growth CtoC untuk Tahunan sama dengan YonY", type: "error" },
      ];
      showNotification(notif, 10000);
      showPdrbAndResult.value.result = false;
      return;
    }
    showPdrbAndResult.value.result = true;
    computedData.value = showGCtoC("adhk_now", "adhk_prev");
  }
  if (tab == "indeks") {
    tableColumn.value[0].label = "Selisih";
    showPdrbAndResult.value.result = true;
    computedData.value = showIndeks("adhb_now", "adhk_now");
  }
  if (tab == "gi_qtoq") {
    tableColumn.value[0].label = "Selisih";
    if (quarterCap.value == "t") {
      let notif = [{ message: "Tidak ada Growth QtoQ untuk Tahunan", type: "error" }];
      showNotification(notif, 10000);
      showPdrbAndResult.value.result = false;
      return;
    }
    showPdrbAndResult.value.result = true;
    computedData.value = showGIQtoQ("adhb_now", "adhb_prev", "adhk_now", "adhk_prev");
  }
  if (tab == "gi_ytoy") {
    tableColumn.value[0].label = "Selisih";
    showPdrbAndResult.value.result = true;
    computedData.value = showGIYtoY("adhb_now", "adhb_prev", "adhk_now", "adhk_prev");
  }
};
const showDist = (data) => {
  const region = page.props.regions.map((x) => x.value);
  region.push("total");

  let stake = 4;
  let dataset = dataOnDemand.value?.[data] ?? {};
  const rows = dataset?.rows ?? {};
  const footers = dataset?.footer ?? {};
  const pdrb = footers["PDRB"] ?? {};
  let result = {
    rows: {},
    footer: {},
  };
  const calculate = (item, base) => {
    let outQ = new Array(stake);
    const baseQ = base?.q ?? [];
    const itemQ = item?.q ?? [];
    for (let i = 0; i < stake; i++) {
      const dividend = Number(itemQ[i] ?? 0);
      const divisor = Number(baseQ[i] ?? 0);
      outQ[i] = divisor !== 0 ? (dividend / divisor) * 100 : 0;
    }
    const totalDividend = Number(item?.total ?? 0);
    const totalDivisor = Number(base?.total ?? 0);
    const total = totalDivisor !== 0 ? (totalDividend / totalDivisor) * 100 : 0;
    return { q: outQ, total };
  };
  for (const rowKey of Object.keys(rows)) {
    result.rows[rowKey] = {};
    for (const rr of region) {
      const row = rows[rowKey]?.[rr];
      const base = pdrb?.[rr];
      result.rows[rowKey][rr] = calculate(row, base);
    }
  }
  for (const footerKey of Object.keys(footers)) {
    result.footer[footerKey] = {};
    for (const rr of region) {
      const foot = footers[footerKey]?.[rr];
      const base = pdrb?.[rr];
      result.footer[footerKey][rr] = calculate(foot, base);
    }
  }
  return result;
};
const showGQtoQ = (now, prev) => {
  const region = page.props.regions.map((x) => x.value);
  region.push("total");
  let current_dataset = dataOnDemand.value?.[now] ?? {};
  let previous_dataset = dataOnDemand.value?.[prev] ?? {};
  let stake = 4;
  let result = {
    rows: {},
    footer: {},
  };

  const calculate = (n, p) => {
    let outQ = new Array(stake);
    const pQ = p?.q ?? [];
    const nQ = n?.q ?? [];
    for (let i = 0; i < stake; i++) {
      const dividend = Number(nQ[i] ?? 0);
      let divisor = 0;
      if (i == 0) divisor = Number(pQ[3] ?? 0);
      else divisor = Number(nQ[i - 1] ?? 0);
      outQ[i] = divisor !== 0 ? (dividend / divisor) * 100 - 100 : 0;
    }
    const total = "qtoq";
    return { q: outQ, total };
  };

  for (const rowKey of Object.keys(current_dataset?.rows ?? {})) {
    result.rows[rowKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.rows?.[rowKey]?.[rr] ?? {};
      const prevs = previous_dataset?.rows?.[rowKey]?.[rr] ?? {};
      result.rows[rowKey][rr] = calculate(nows, prevs);
    }
  }
  for (const footerKey of Object.keys(current_dataset?.footer ?? {})) {
    result.footer[footerKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.footer?.[footerKey]?.[rr] ?? {};
      const prevs = previous_dataset?.footer?.[footerKey]?.[rr] ?? {};
      result.footer[footerKey][rr] = calculate(nows, prevs);
    }
  }
  return result;
};
const showGYtoY = (now, prev) => {
  const region = page.props.regions.map((x) => x.value);
  region.push("total");
  let current_dataset = dataOnDemand.value?.[now] ?? {};
  let previous_dataset = dataOnDemand.value?.[prev] ?? {};
  let stake = 4;
  let result = {
    rows: {},
    footer: {},
  };
  const calculate = (n, p) => {
    let outQ = new Array(stake);
    const pQ = p?.q ?? [];
    const nQ = n?.q ?? [];
    for (let i = 0; i < stake; i++) {
      const dividend = Number(nQ[i] ?? 0);
      const divisor = Number(pQ[i] ?? 0);
      outQ[i] = divisor !== 0 ? (dividend / divisor) * 100 - 100 : 0;
    }
    const totalDividend = Number(n?.total ?? 0);
    const totalDivisor = Number(p?.total ?? 0);
    const total = totalDivisor !== 0 ? (totalDividend / totalDivisor) * 100 - 100 : 0;
    return { q: outQ, total };
  };

  for (const rowKey of Object.keys(current_dataset?.rows ?? {})) {
    result.rows[rowKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.rows?.[rowKey]?.[rr] ?? {};
      const prevs = previous_dataset?.rows?.[rowKey]?.[rr] ?? {};
      result.rows[rowKey][rr] = calculate(nows, prevs);
    }
  }
  for (const footerKey of Object.keys(current_dataset?.footer ?? {})) {
    result.footer[footerKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.footer?.[footerKey]?.[rr] ?? {};
      const prevs = previous_dataset?.footer?.[footerKey]?.[rr] ?? {};
      result.footer[footerKey][rr] = calculate(nows, prevs);
    }
  }
  return result;
};
const showGCtoC = (now, prev) => {
  const region = page.props.regions.map((x) => x.value);
  region.push("total");
  let current_dataset = dataOnDemand.value?.[now] ?? {};
  let previous_dataset = dataOnDemand.value?.[prev] ?? {};
  let stake = 4;
  let result = {
    rows: {},
    footer: {},
  };
  const calculate = (n, p) => {
    let outQ = new Array(stake);
    const pQ = p?.q ?? [];
    const nQ = n?.q ?? [];
    for (let i = 0; i < stake; i++) {
      let dividend = 0,
        divisor = 0;
      for (let cumulative = 0; cumulative <= i; cumulative++) {
        dividend += Number(nQ[cumulative] ?? 0);
        divisor += Number(pQ[cumulative] ?? 0);
      }
      outQ[i] = divisor !== 0 ? (dividend / divisor) * 100 - 100 : 0;
    }
    const total = "ctoc";
    return { q: outQ, total };
  };
  for (const rowKey of Object.keys(current_dataset?.rows ?? {})) {
    result.rows[rowKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.rows?.[rowKey]?.[rr] ?? {};
      const prevs = previous_dataset?.rows?.[rowKey]?.[rr] ?? {};
      result.rows[rowKey][rr] = calculate(nows, prevs);
    }
  }
  for (const footerKey of Object.keys(current_dataset?.footer ?? {})) {
    result.footer[footerKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.footer?.[footerKey]?.[rr] ?? {};
      const prevs = previous_dataset?.footer?.[footerKey]?.[rr] ?? {};
      result.footer[footerKey][rr] = calculate(nows, prevs);
    }
  }
  return result;
};
const showIndeks = (now, prev) => {
  const region = page.props.regions.map((x) => x.value);
  region.push("total");
  let current_dataset = dataOnDemand.value?.[now] ?? {};
  let previous_dataset = dataOnDemand.value?.[prev] ?? {};
  let stake = 4;
  let result = {
    rows: {},
    footer: {},
  };
  const calculate = (n, p) => {
    let outQ = new Array(stake);
    const pQ = p?.q ?? [];
    const nQ = n?.q ?? [];
    for (let i = 0; i < stake; i++) {
      const dividend = Number(nQ[i] ?? 0);
      const divisor = Number(pQ[i] ?? 0);
      outQ[i] = divisor !== 0 ? (dividend / divisor) * 100 : 0;
    }
    const totalDividend = Number(n?.total ?? 0);
    const totalDivisor = Number(p?.total ?? 0);
    const total = totalDivisor !== 0 ? (totalDividend / totalDivisor) * 100 : 0;
    return { q: outQ, total };
  };
  for (const rowKey of Object.keys(current_dataset?.rows ?? {})) {
    result.rows[rowKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.rows?.[rowKey]?.[rr] ?? {};
      const prevs = previous_dataset?.rows?.[rowKey]?.[rr] ?? {};
      result.rows[rowKey][rr] = calculate(nows, prevs);
    }
  }
  for (const footerKey of Object.keys(current_dataset?.footer ?? {})) {
    result.footer[footerKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.footer?.[footerKey]?.[rr] ?? {};
      const prevs = previous_dataset?.footer?.[footerKey]?.[rr] ?? {};
      result.footer[footerKey][rr] = calculate(nows, prevs);
    }
  }
  return result;
};
const showGIQtoQ = (adhbnow, adhbprev, adhknow, adhkprev) => {
  const region = page.props.regions.map((x) => x.value);
  region.push("total");
  let stake = 4;
  let result = {
    rows: {},
    footer: {},
  };
  let previous_dataset = showIndeks(adhbprev, adhkprev);
  let current_dataset = showIndeks(adhbnow, adhknow);
  const calculate = (n, p) => {
    let outQ = new Array(stake);
    const pQ = p?.q ?? [];
    const nQ = n?.q ?? [];
    for (let i = 0; i < stake; i++) {
      const dividend = Number(nQ[i] ?? 0);
      let divisor = 0;
      if (i == 0) divisor = Number(pQ[3] ?? 0);
      else divisor = Number(nQ[i - 1] ?? 0);
      outQ[i] = divisor !== 0 ? (dividend / divisor) * 100 - 100 : 0;
    }
    const total = "qtoq";
    return { q: outQ, total };
  };
  for (const rowKey of Object.keys(current_dataset?.rows ?? {})) {
    result.rows[rowKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.rows?.[rowKey]?.[rr] ?? {};
      const prevs = previous_dataset?.rows?.[rowKey]?.[rr] ?? {};
      result.rows[rowKey][rr] = calculate(nows, prevs);
    }
  }
  for (const footerKey of Object.keys(current_dataset?.footer ?? {})) {
    result.footer[footerKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.footer?.[footerKey]?.[rr] ?? {};
      const prevs = previous_dataset?.footer?.[footerKey]?.[rr] ?? {};
      result.footer[footerKey][rr] = calculate(nows, prevs);
    }
  }
  return result;
};
const showGIYtoY = (adhbnow, adhbprev, adhknow, adhkprev) => {
  const region = page.props.regions.map((x) => x.value);
  region.push("total");
  let stake = 4;
  let result = {
    rows: {},
    footer: {},
  };
  let previous_dataset = showIndeks(adhbprev, adhkprev);
  let current_dataset = showIndeks(adhbnow, adhknow);
  const calculate = (n, p) => {
    let outQ = new Array(stake);
    const pQ = p?.q ?? [];
    const nQ = n?.q ?? [];
    for (let i = 0; i < stake; i++) {
      const dividend = Number(nQ[i] ?? 0);
      const divisor = Number(pQ[i] ?? 0);
      outQ[i] = divisor !== 0 ? (dividend / divisor) * 100 - 100 : 0;
    }
    const totalDividend = Number(n?.total ?? 0);
    const totalDivisor = Number(p?.total ?? 0);
    const total = totalDivisor !== 0 ? (totalDividend / totalDivisor) * 100 - 100 : 0;
    return { q: outQ, total };
  };
  for (const rowKey of Object.keys(current_dataset?.rows ?? {})) {
    result.rows[rowKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.rows?.[rowKey]?.[rr] ?? {};
      const prevs = previous_dataset?.rows?.[rowKey]?.[rr] ?? {};
      result.rows[rowKey][rr] = calculate(nows, prevs);
    }
  }
  for (const footerKey of Object.keys(current_dataset?.footer ?? {})) {
    result.footer[footerKey] = {};
    for (const rr of region) {
      const nows = current_dataset?.footer?.[footerKey]?.[rr] ?? {};
      const prevs = previous_dataset?.footer?.[footerKey]?.[rr] ?? {};
      result.footer[footerKey][rr] = calculate(nows, prevs);
    }
  }
  return result;
};
const toFixed = ref(4);
const addToFixedValue = (up) => {
  if (up) {
    if (toFixed.value == 8) return;
    else toFixed.value = toFixed.value + 1;
  } else {
    if (toFixed.value == 0) return;
    else toFixed.value = toFixed.value - 1;
  }
};
const downloadModalStatus = ref(false);
const downloadTitle = ref("Download");
const downloadType = ref(null);
const downloadHasil = async (id, title, type) => {
  const rowDefs =
    page.props.type == "Lapangan Usaha"
      ? buildRowDefsLapus(page.props.subsectors)
      : buildRowDefsPeng(page.props.subsectors);
  let list = {};
  let quarter = Number(quarterCap.value);
  let quarterList = [];
  for (let index = 1; index <= quarter; index++) {
    quarterList.push(String(index));
    if (index == 4) quarterList.push("t");
  }
  const space = [[""], [""]];

  for (const key of quarterList) {
    quartersTab(key);
    tableColumn.value[0].label = "Diskrepansi";
    await new Promise((resolve) => setTimeout(resolve, 120));
    let printed = [];
    const resultAdhb = buildAOADiskrepansi({
      tableModel: dataOnDemand.value["adhb_now"],
      secondModel: dataOnDemand.value["adhb_now_disk"],
      rowDefs: rowDefs,
      tableColumn: tableColumn.value,
      quarterCap: key,
      diskrepansi: true,
    });
    printed.push(["ADHB"], ...space[0], ...resultAdhb);
    const resultAdhk = buildAOADiskrepansi({
      tableModel: dataOnDemand.value["adhk_now"],
      secondModel: dataOnDemand.value["adhk_now_disk"],
      rowDefs: rowDefs,
      tableColumn: tableColumn.value,
      quarterCap: key,
      diskrepansi: true,
    });
    printed.push(...space, ["ADHK"], ...space[0], ...resultAdhk);
    for (const keytab of Object.keys(activeTab.value)) {
      if (keytab == "adhb" || keytab == "adhk") continue;
      showTab(keytab);
      tableColumn.value[0].label = "Selisih";
      await new Promise((resolve) => setTimeout(resolve, 120));
      const resultCalculate = buildAOADiskrepansi({
        tableModel: computedData.value,
        secondModel: dataOnDemand.value["computed_diff"],
        rowDefs: rowDefs,
        tableColumn: tableColumn.value,
        quarterCap: key,
      });
      printed.push(...space, [keytab], ...resultCalculate);
    }
    list["Triwulan-" + key] = printed;
  }
  theDownload({ setdata: list, title: title, RULES: page.props.type, diskrepansi: true });
};
</script>
<style scoped>
.table {
  font-size: 13px;
}

.fixed-thead {
  position: sticky;
  min-width: 300px;
  left: 0;
  background-color: #175676;
  color: whitesmoke;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.value-thead {
  min-width: 300px;
  padding: 1rem;
  background-color: #175676;
  color: whitesmoke;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.table {
  /* table-layout: fixed; */
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  /* Avoid extra spacing */
}
</style>
