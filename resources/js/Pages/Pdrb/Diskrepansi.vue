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
                <label for="type">Pilih PDRB<span class="text-danger">*</span></label>
                <Multiselect v-model="form.type" :placeholder="form.type" disabled />
                <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
              </div>
              <div class="w-1/5 space-y-2">
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
              <div class="w-1/5 space-y-2">
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
              <div class="w-1/5 space-y-2">
                <label for="year"
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
                <label for="year">Pilih Data Tahun Sebelumnya</label>
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
          </div>
        </div>
      </div>
      <div class="overflow-x-scroll">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <th class="fixed-thead">Komponen</th>
            <th class="value-thead" v-for="(node, index) in tableColumn" :key="index">
              {{ node.label }}
            </th>
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
              :calculate="calculateData.adhb"
              @update:update-d-o-d="updateDOD"
            />
            <DiskrepansiLapus
              v-show="showPdrbAndResult['adhk']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :data-contents="dataContents"
              :type="'adhk'"
              :on-demand-type="'adhk_now'"
              :quarter="quarterCap"
              :calculate="calculateData.adhk"
              @update:update-d-o-d="updateDOD"
            />
            <DiskrepansiLapusResult
              v-show="showPdrbAndResult['result']"
              :subsectors="page.props.subsectors"
              :table-column="tableColumn"
              :quarter="quarterCap"
              :type="'distribusi'"
              :computed-data="computedData"
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
            />
          </template>
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>
<script setup>
import { triggerSpinner } from "@/axiosSetup";
import DiskrepansiLapus from "@/Components/DiskrepansiLapus.vue";
import DiskrepansiLapusResult from "@/Components/DiskrepansiLapusResult.vue";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
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
const dataOnDemand = ref({ adhb_now: {}, adhb_prev: {}, adhk_now: {}, adhk_prev: {} });
const calculateData = ref({ adhb: {}, adhk: {} });
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
  dataOnDemand.value[data.type][quarterCap.value] = data.data;
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
    for (let index = 1; index <= Number(quarterCap.value); index++) {
      if (index == 4) {
        listTab.value.t = true;
      }
      listTab.value[index] = true;
    }
    dataContents.value = response.data.current_data;
    dataBefore.value = response.data.previous_data;
    formError.value = {
      year: null,
      quarter: null,
      description: null,
      regions: null,
    };
    showTabPanel.value = true;
    await nextTick();
    for (const key of Object.keys(listTab.value)) {
      quarterCap.value = key;
      await new Promise((resolve) => setTimeout(resolve, 300));
    }
    showTab("adhb");
    showNotification(response.data.notification);
    quartersTab(form.quarter);
  } catch (error) {
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
    showPdrbAndResult.value[key] = false;
  });
};
const showTab = async (tab) => {
  Object.keys(activeTab.value).forEach((key) => {
    activeTab.value[key] = def;
  });
  activeTab.value[tab] = "btn-success-fordone";

  resetShowTable();
  if (tab == "adhb") {
    showPdrbAndResult.value.adhb = true;
    await nextTick();
    await new Promise((resolve) => setTimeout(resolve, 300));
    let disk = calculateDiskrepansi();
    calculateData.value.adhb = disk.adhb;
    calculateData.value.adhk = disk.adhk;
  }
  if (tab == "adhk") {
    showPdrbAndResult.value.adhk = true;
    // console.log(calculateDiskrepansi());
  }
  if (tab == "dist") {
    showPdrbAndResult.value.result = true;
    computedData.value = showDist("adhb_now");
  }
  if (tab == "g_qtoq") {
    if (quarterCap.value == "t") {
      let notif = [{ message: "Tidak ada Growth QtoQ untuk Tahunan", type: "error" }];
      showNotification(notif, 1500);
      showPdrbAndResult.value.result = false;
      return;
    }
    showPdrbAndResult.value.result = true;
    computedData.value = showGQtoQ("adhk_now", "adhk_prev");
  }
  if (tab == "g_ytoy") {
    showPdrbAndResult.value.result = true;
    computedData.value = showGYtoY("adhk_now", "adhk_prev");
  }
  if (tab == "g_ctoc") {
    if (quarterCap.value == "t") {
      let notif = [
        { message: "Growth CtoC untuk Tahunan sama dengan YonY", type: "error" },
      ];
      showNotification(notif, 1500);
      showPdrbAndResult.value.result = false;
      return;
    }
    showPdrbAndResult.value.result = true;
    computedData.value = showGCtoC("adhk_now", "adhk_prev");
  }
  if (tab == "indeks") {
    showPdrbAndResult.value.result = true;
    computedData.value = showIndeks("adhb_now", "adhk_now");
  }
  if (tab == "gi_qtoq") {
    if (quarterCap.value == "t") {
      let notif = [{ message: "Tidak ada Growth QtoQ untuk Tahunan", type: "error" }];
      showNotification(notif, 1500);
      showPdrbAndResult.value.result = false;
      return;
    }
    showPdrbAndResult.value.result = true;
    computedData.value = showGIQtoQ("adhb_now", "adhb_prev", "adhk_now", "adhk_prev");
  }
  if (tab == "gi_ytoy") {
    showPdrbAndResult.value.result = true;
    computedData.value = showGIYtoY("adhb_now", "adhb_prev", "adhk_now", "adhk_prev");
  }
};
const showDist = (data) => {
  let dataset = dataOnDemand.value[data][quarterCap.value];
  let arrayPDRB = dataset["PDRB"];
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let result = {};
  Object.keys(dataset).forEach((key) => {
    result[key] = dataset[key].map((value, index) => {
      let divisor = parseNumber(arrayPDRB[index]); // Get corresponding PDRB value
      let dividend = parseNumber(value); // Convert current value to number
      let dist = divisor !== 0 ? (dividend / divisor) * 100 : 0; // Avoid division by zero
      return formatNumberGerman(dist.toFixed(4), 2, 4);
    });
  });
  return removeSpaceOnKomponen(result);
};
const showGQtoQ = (now, prev) => {
  let current_dataset = dataOnDemand.value[now][quarterCap.value];
  let previous_dataset = dataOnDemand.value[prev][4];
  current_dataset = removeSpaceOnKomponen(current_dataset);
  previous_dataset = removeSpaceOnKomponen(previous_dataset);
  let result = {};
  let previous_quarter;
  if (quarterCap.value > 1) {
    previous_quarter = dataOnDemand.value[now][Number(quarterCap.value) - 1];
    previous_quarter = removeSpaceOnKomponen(previous_quarter);
  }
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key].map((value, index) => {
      let dividend = value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
      let divisor;
      if (quarterCap.value == 1) {
        divisor = previous_dataset[key][index]
          ? Number(previous_dataset[key][index].replaceAll(".", "").replaceAll(",", "."))
          : 0;
      } else {
        divisor = previous_quarter[key][index]
          ? Number(previous_quarter[key][index].replaceAll(".", "").replaceAll(",", "."))
          : 0;
      }
      let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
      return formatNumberGerman(growth.toFixed(4), 2, 4);
    });
  });
  return removeSpaceOnKomponen(result);
};
const showGYtoY = (now, prev) => {
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value[now][quarterCap.value]);
  let previous_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[prev][quarterCap.value]
  );
  if (isObjectEmpty(previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif, 1500);
    showPdrbAndResult.value.result = false;
    return;
  }
  let result = {};
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key].map((value, index) => {
      let dividend = value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
      let divisor = previous_dataset[key][index]
        ? Number(previous_dataset[key][index].replaceAll(".", "").replaceAll(",", "."))
        : 0;
      let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
      return formatNumberGerman(growth.toFixed(4), 2, 4);
    });
  });
  return removeSpaceOnKomponen(result);
};
const showGCtoC = (now, prev) => {
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value[now][quarterCap.value]);
  let previous_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[prev][quarterCap.value]
  );
  if (isObjectEmpty(previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif, 1500);
    showPdrbAndResult.value.result = false;
    return;
  }
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let result = {};
  let current_quarter = {},
    previous_quarter = {};
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key].map((_, index) => {
      let dividend = 0,
        divisor = 0;
      for (let cumulative = 1; cumulative <= quarterCap.value; cumulative++) {
        current_quarter = removeSpaceOnKomponen(dataOnDemand.value[now][cumulative]);
        previous_quarter = removeSpaceOnKomponen(dataOnDemand.value[prev][cumulative]);
        dividend += parseNumber(current_quarter[key][index]);
        divisor += parseNumber(previous_quarter[key][index]);
      }
      let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
      return formatNumberGerman(growth.toFixed(4), 2, 4);
    });
  });
  return removeSpaceOnKomponen(result);
};
const showIndeks = (now, prev) => {
  let current_dataset = removeSpaceOnKomponen(dataOnDemand.value[now][quarterCap.value]);
  let previous_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[prev][quarterCap.value]
  );
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let result = {};
  Object.keys(current_dataset).forEach((key) => {
    result[key] = current_dataset[key].map((value, index) => {
      let dividend = parseNumber(value);
      let divisor = parseNumber(previous_dataset[key][index]);
      let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
      return formatNumberGerman(indeks.toFixed(4), 2, 4);
    });
  });
  return removeSpaceOnKomponen(result);
};
const showGIQtoQ = (adhbnow, adhbprev, adhknow, adhkprev) => {
  let adhb_current_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[adhbnow][quarterCap.value]
  );
  let adhk_current_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[adhknow][quarterCap.value]
  );
  let adhb_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhbprev][4]);
  let adhk_previous_dataset = removeSpaceOnKomponen(dataOnDemand.value[adhkprev][4]);
  if (isObjectEmpty(adhb_previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif, 1500);
    showPdrbAndResult.value.result = false;
    return;
  }
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let indeks_implisit_previous = {};
  Object.keys(adhb_previous_dataset).forEach((key) => {
    indeks_implisit_previous[key] = adhb_previous_dataset[key].map((_, index) => {
      let dividend = parseNumber(adhb_previous_dataset[key][index]);
      let divisor = parseNumber(adhk_previous_dataset[key][index]);
      let indeks = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 : 0;
      return formatNumberGerman(indeks.toFixed(4), 2, 4);
    });
  });
  let indeks_implisit_current = {};
  Object.keys(adhb_current_dataset).forEach((key) => {
    indeks_implisit_current[key] = adhb_current_dataset[key].map((value, index) => {
      let dividend = parseNumber(value);
      let divisor = parseNumber(adhk_current_dataset[key][index]);
      let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
      return formatNumberGerman(indeks.toFixed(4), 2, 4);
    });
  });
  let result = {};
  let indeks_implisit_quarter_previous = {},
    previous_quarter_adhb = {},
    previous_quarter_adhk = {};
  if (quarterCap.value > 1) {
    previous_quarter_adhb = removeSpaceOnKomponen(
      dataOnDemand.value[adhbnow][Number(quarterCap.value) - 1]
    );
    previous_quarter_adhk = removeSpaceOnKomponen(
      dataOnDemand.value[adhknow][Number(quarterCap.value) - 1]
    );
    Object.keys(previous_quarter_adhb).forEach((key) => {
      indeks_implisit_quarter_previous[key] = previous_quarter_adhb[key].map(
        (value, index) => {
          let dividend = parseNumber(value);
          let divisor = parseNumber(previous_quarter_adhk[key][index]);
          let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
          return formatNumberGerman(indeks.toFixed(4), 2, 4);
        }
      );
    });
  }
  Object.keys(indeks_implisit_current).forEach((key) => {
    result[key] = indeks_implisit_current[key].map((value, index) => {
      let dividend = parseNumber(value);
      let divisor;
      if (quarterCap.value == 1) {
        divisor = parseNumber(indeks_implisit_previous[key][index]);
      } else {
        divisor = parseNumber(indeks_implisit_quarter_previous[key][index]);
      }
      let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
      return formatNumberGerman(growth.toFixed(4), 2, 4);
    });
  });
  return result;
};
const showGIYtoY = (adhbnow, adhbprev, adhknow, adhkprev) => {
  let adhb_current_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[adhbnow][quarterCap.value]
  );
  let adhk_current_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[adhknow][quarterCap.value]
  );
  let adhb_previous_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[adhbprev][quarterCap.value]
  );
  let adhk_previous_dataset = removeSpaceOnKomponen(
    dataOnDemand.value[adhkprev][quarterCap.value]
  );
  if (isObjectEmpty(adhb_previous_dataset)) {
    let notif = [{ message: "Data Tahun sebelumnya masih kosong", type: "error" }];
    showNotification(notif, 1500);
    showPdrbAndResult.value.result = false;
    return;
  }
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let indeks_implisit_previous = {};
  Object.keys(adhb_previous_dataset).forEach((key) => {
    indeks_implisit_previous[key] = adhb_previous_dataset[key].map((value, index) => {
      let dividend = parseNumber(value);
      let divisor = parseNumber(adhk_previous_dataset[key][index]);
      let indeks = divisor !== 0 ? (dividend / divisor) * 100 : 0;
      return formatNumberGerman(indeks.toFixed(4), 2, 4);
    });
  });
  let indeks_implisit_current = {};
  Object.keys(adhb_current_dataset).forEach((key) => {
    indeks_implisit_current[key] = adhb_current_dataset[key].map((value, index) => {
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
  return result;
};
const calculateDiskrepansi = () => {
  tableColumn.value[0].label = "Diskrepansi";
  let adhbDisk = removeSpaceOnKomponen(dataOnDemand.value["adhb_now"][quarterCap.value]);
  let adhkDisk = removeSpaceOnKomponen(dataOnDemand.value["adhk_now"][quarterCap.value]);
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  let resultAdhb = {},
    resultAdhk = {},
    prov,
    selisih;
  Object.keys(adhbDisk).forEach((key) => {
    resultAdhb[key] = adhbDisk[key].slice(0, 2).map((value, index) => {
      if (index > 0) {
        prov = parseNumber(adhbDisk[key][1]);
        selisih = prov - parseNumber(adhbDisk[key][2]);
        let disk = selisih !== 0 && prov !== 0 ? (selisih / prov) * 100 : 0;
        return formatNumberGerman(disk, 2, 4);
      }
    });
  });
  Object.keys(adhkDisk).forEach((key) => {
    resultAdhk[key] = adhkDisk[key].slice(0, 2).map((value, index) => {
      if (index > 0) {
        prov = parseNumber(adhkDisk[key][1]);
        selisih = prov - parseNumber(adhkDisk[key][2]);
        let disk = selisih !== 0 && prov !== 0 ? (selisih / prov) * 100 : 0;
        return formatNumberGerman(disk, 2, 4);
      }
    });
  });
  return { adhb: resultAdhb, adhk: resultAdhk };
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
