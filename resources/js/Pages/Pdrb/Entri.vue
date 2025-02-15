<template>
  <Head title="Entri PDRB" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
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
          <div class="btn-info-fordone ml-auto w-[120px]" @click.prevent="submit">
            <font-awesome-icon icon="fa fa-save" /> Entri Data
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
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>
<script setup>
import LapusResultTable from "@/Components/LapusResultTable.vue";
import LapusTable from "@/Components/LapusTable.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { onMounted, ref, watch } from "vue";

const page = usePage();
const subsectors = ref(page.props.subsectors);
const dataContents = ref([]);
const dataBefore = ref([]);
const computedData = ref({});
const quarterCap = ref("4");
const showTabPanel = ref(false);
const dataOnDemand = ref({ adhb_now: {}, adhb_prev: {}, adhk_now: {}, adhk_prev: {} });
const form = useForm({
  _token: null,
  dataContents: null,
  type: page.props.type,
  year: null,
  quarter: null,
  description: null,
  dataBefore: null,
  regions: null,
});
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
    showTab("adhb");
  } catch (error) {}
};
// #endregion

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
    showDist();
    showPdrbAndResult.value.result = true;
  }
};
const showDist = () => {
  let dataset = dataOnDemand.value["adhb_now"];
  let arrayPDRB = dataset["PDRB"];
  let result = {};
  Object.keys(dataset).forEach((key) => {
    result[key] = dataset[key].map((value, index) => {
      let divisor = Number(arrayPDRB[index].replaceAll(".", "").replaceAll(",", ".")); // Get corresponding pdrb value
      let dividend = Number(value.replaceAll(".", "").replaceAll(",", ".")); // Convert current value to number

      let dist = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 : 0; // Avoid division by zero
      return formatNumberGerman(dist.toFixed(4), 2, 4);
      // return dist;
    });
  });
  result = Object.fromEntries(
    Object.entries(result).map(([key, value]) => [key.trim().replace(/\s+/g, ""), value])
  );
  // console.log(result);
  computedData.value = result;
};
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
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
