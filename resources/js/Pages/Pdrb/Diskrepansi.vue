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
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 200); // Delay based on index
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
      tempData.push(element, { label: "Total Kabupaten/Kota", value: "total" });
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
    // Display notification if available
    // console.log(error);
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
  result: true,
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
    computedData.value = showDist("adhb_now");
  }
};
const showDist = (data) => {
  let dataset = dataOnDemand.value[data][quarterCap.value];
  console.log(dataset);
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
  console.log(result);
  return removeSpaceOnKomponen(result);
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
