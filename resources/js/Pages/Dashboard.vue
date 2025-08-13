<template>
  <Head title="Dashboard" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <div class="font-bold text-xl">PERIODE PUTARAN AKTIF (3 TERAKHIR)</div>
    <div class="flex flex-wrap mt-2 items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col h-full border-r md:pr-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">LAPANGAN USAHA</h3>
                <font-awesome-icon
                  icon="fa-solid fa-industry"
                  class="fa-xl text-indigo-500"
                />
              </div>
              <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                <li v-for="(node, index) in props.lapus" :key="'lapus-' + index">
                  {{ node.description }}
                </li>
              </ul>
            </div>
            <div class="flex flex-col h-full md:pl-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">PENGELUARAN</h3>
                <font-awesome-icon
                  icon="fa-solid fa-coins"
                  class="fa-xl text-orange-500"
                />
              </div>
              <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                <li v-for="(node, index) in props.peng" :key="'peng-' + index">
                  {{ node.description }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center mt-3 space-x-2">
      <div class="w-full lg:w-[25%] xl:w-[25%] pr-1">
        <Multiselect
          v-model="regionsFor"
          :options="props.regions"
          :searchable="true"
          placeholder="-- Pilih Kabupaten/Kota --"
        />
      </div>
      <button
        type="submit"
        class="btn-info-fordone w-[40px] md:ml-0"
        @click="searchSummary"
      >
        <font-awesome-icon icon="fa-solid fa-magnifying-glass" />
      </button>
      <button
        v-if="page.props.auth.user.role == 'admin'"
        @click="confirmationSummaries = true"
        class="btn btn-success-fordone"
      >
        Build Summaries
      </button>
    </div>
    <div class="font-bold text-xl mt-2">
      SUMMARY PDRB,
      <span class="text-sm">(update terakhir: {{ lapus.waktu }})</span>
    </div>
    <div class="flex flex-wrap mt-2 items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <div class="grid grid-cols-1 gap-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-bold text-base">ADHB</h3>
                  <font-awesome-icon
                    icon="fa-solid fa-industry"
                    class="fa-xl text-indigo-500"
                  />
                </div>
                <div>{{ summaryData.lapus.adhb.transformed }}</div>
              </div>
              <div class="flex flex-col">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-bold text-base">ADHK</h3>
                  <font-awesome-icon
                    icon="fa-solid fa-coins"
                    class="fa-xl text-orange-500"
                  />
                </div>
                <div>{{ summaryData.lapus.adhk.transformed }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <!-- Row 2: Growth QtoQ, YonY, CtoC -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col">
              <div class="flex items-center space-x-2 mb-2">
                <h3 class="font-bold text-base">Growth QtoQ</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.qtoq.value > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.qtoq.value > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.qtoq.transformed }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center space-x-2 mb-2">
                <h3 class="font-bold text-base">Growth YonY</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.yony.value > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.yony.value > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.yony.transformed }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center space-x-2 mb-2">
                <h3 class="font-bold text-base">Growth CtoC</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.ctoc.value > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.ctoc.value > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.ctoc.transformed }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col">
              <div class="flex items-center space-x-2 mb-2">
                <h3 class="font-bold text-base">Indeks Implisit</h3>
                <font-awesome-icon
                  icon="fa-solid fa-percent"
                  class="fa-xl text-pink-500"
                />
              </div>
              <div>{{ summaryData.lapus.idx.transformed }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center space-x-2 mb-2">
                <h3 class="font-bold text-base">Laju Implisit QtoQ</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.iqtoq.value > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.iqtoq.value > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.iqtoq.transformed }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center space-x-2 mb-2">
                <h3 class="font-bold text-base">Laju Implisit YonY</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.iyony.value > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.iyony.value > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.iyony.transformed }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="font-bold text-xl mt-3">GRAFIK</div>
    <div class="flex flex-wrap">
      <div class="w-full lg:w-[50%] md:w-full xl:w-[50%]">
        <div class="flex flex-wrap items-center my-3 space-x-1">
          <div class="w-full md:w-[75%] lg:w-[50%] xl:w-[50%] pr-1">
            <Multiselect
              v-model="graphData.legend"
              :options="attributeGraph"
              :searchable="true"
              placeholder="-- Pilih Atribut --"
            />
          </div>
          <button @click="buildGraph" class="btn btn-success-fordone">
            Build Grafik
          </button>
          <button @click="sortGraphData" class="btn btn-info-fordone">
            <font-awesome-icon
              v-if="sorted == 'desc' || sorted == 'asc'"
              :icon="sorted == 'desc' ? 'fa-solid fa-angle-down' : 'fa-solid fa-angle-up'"
            />
            Sort
          </button>
          <button @click="sortGraphData('reset')" class="btn btn-warning-fordone">
            <font-awesome-icon icon="fa-solid fa-recycle" />
          </button>
        </div>
        <!-- v-if="showBar" -->
        <BarChart
          :key="barIndex"
          :legend="graphData.legend"
          :chart-label="graphData.label"
          :chart-value="graphData.data"
        />
      </div>
    </div>
    <ModalBs
      :hidden-close="thisHidden"
      :-modal-status="confirmationSummaries"
      @close="
        () => {
          confirmationSummaries = false;
          timestart = null;
        }
      "
      :title="'Konfirmasi'"
      :modal-size="'min-w-[20vw]'"
    >
      <template #modalBody>
        <div>
          Proses Build Summary akan memakan waktu setidaknya satu jam, mohon jangan
          me-reload page ini selama rentang waktu tersebut.
        </div>
        <div class="font-bold">
          Terakhir summary dibuat: {{ lapus.waktu }} oleh {{ lapus.nama }}
        </div>
        <div class="font-vold text-red-500">
          waktu mulai : {{ timestart ? timestart : "-" }}
        </div>
      </template>
      <template #modalFunction>
        <button @click="buildSummaries" class="btn btn-success-fordone">
          Laksanakan!
        </button>
      </template>
    </ModalBs>
  </GeneralLayout>
</template>

<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { onMounted, ref, watch } from "vue";
import Multiselect from "@vueform/multiselect";
import ModalBs from "@/Components/ModalBs.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { triggerSpinner } from "@/axiosSetup";
import BarChart from "@/Components/BarChart.vue";
const page = usePage();
const props = defineProps({
  lapus: {
    type: Array,
    required: false,
  },
  peng: {
    type: Array,
    required: false,
  },
  sumTime: {
    type: Array,
    required: false,
  },
  regions: {
    type: Array,
    required: false,
  },
  default: {
    type: Object,
    required: false,
  },
});
const graphData = ref({
  data: [],
  label: [],
  legend: "qtoq",
});
var defaultGraph = { data: null, label: null };
const attributeGraph = [
  { label: "qtoq", value: "qtoq" },
  { label: "yony", value: "yony" },
  { label: "ctoc", value: "ctoc" },
  { label: "iqtoq", value: "iqtoq" },
  { label: "iyony", value: "iyony" },
  { label: "distribusi", value: "distribusi" },
];
const sorted = ref("desc");
const barIndex = ref(0);
const sortGraphData = (type = "default") => {
  if (type == "reset") {
    setTimeout(() => {
      graphData.value.label = defaultGraph.label;
      graphData.value.data = defaultGraph.data;
      barIndex.value++;
    }, 100);
    return;
  }
  const dataset = graphData.value.data;
  const combined = graphData.value.label.map((label, index) => ({
    label,
    value: dataset[index],
  }));
  if (sorted.value == "desc") {
    sorted.value = "asc";
    combined.sort((a, b) => b.value - a.value);
  } else if (sorted.value == "asc") {
    sorted.value = "desc";
    combined.sort((a, b) => a.value - b.value);
  }
  setTimeout(() => {
    graphData.value.label = combined.map((item) => item.label);
    graphData.value.data = combined.map((item) => item.value);
    barIndex.value++;
  }, 100);
};
const lapus = ref({ quarter: null, description: null, waktu: null, nama: null });
const peng = ref({ quarter: null, description: null, waktu: null, nama: null });
const confirmationSummaries = ref(false);
const summaryData = ref({
  lapus: {
    adhb: { value: null, transformed: null },
    adhk: { value: null, transformed: null },
    qtoq: { value: null, transformed: null },
    yony: { value: null, transformed: null },
    ctoc: { value: null, transformed: null },
    idx: { value: null, transformed: null },
    iqtoq: { value: null, transformed: null },
    iyony: { value: null, transformed: null },
  },
  peng: {
    adhb: { value: null, transformed: null },
    adhk: { value: null, transformed: null },
    qtoq: { value: null, transformed: null },
    yony: { value: null, transformed: null },
    ctoc: { value: null, transformed: null },
    idx: { value: null, transformed: null },
    iqtoq: { value: null, transformed: null },
    iyony: { value: null, transformed: null },
  },
});
const form = useForm({
  _token: null,
  lapus_id: null,
  peng_id: null,
});
const timestart = ref(null);
const thisHidden = ref(false);
const setupArray = ["category", "sector", "subsector", "total"];
const dogArray = ref([]);
const supRegion = [...props.regions, { name: "Total", value: 17 }];
supRegion.forEach((r) => {
  setupArray.forEach((s) => {
    let keys = { region: r.value, cat: s };
    dogArray.value.push(keys);
  });
});
const summariesing = async (element, region_id) => {
  const response = await axios.get(route("home.index"), {
    params: {
      setup: element,
      region_id: region_id,
    },
  });
  return response.data;
};
const dogKey = ref(0);
const buildSummaries = async () => {
  timestart.value = buildTime();
  thisHidden.value = true;
  try {
    for (const d of dogArray.value) {
      const holder = await summariesing(d.cat, d.region);
      form.lapus_id = holder.lapus_period;
      form.peng_id = holder.peng_period;
    }
  } catch (error) {
    console.error(error);
  } finally {
    const _token = await axios.get(route("token"));
    form._token = _token.data;
    form.post(route("home.update-time"));
    confirmationSummaries.value = false;
    thisHidden.value = false;
    window.location.reload();
  }
};
const getSummary = async (region_id, quarter, type) => {
  try {
    const response = await axios.get(route("home.get-summary"), {
      params: {
        region_id: region_id,
        quarter: quarter,
        type: type,
      },
    });
    if (response.data.type == "Lapangan Usaha") {
      Object.keys(response.data.data).forEach((key) => {
        if (summaryData.value.lapus[key]) {
          summaryData.value.lapus[key].value = response.data.data[key];
          summaryData.value.lapus[key].transformed = formatNumberGerman(
            response.data.data[key]
          );
        }
      });
    } else {
      Object.keys(response.data.data).forEach((key) => {
        if (summaryData.value.peng[key]) {
          summaryData.value.peng[key].value = response.data.data[key];
          summaryData.value.peng[key].transformed = formatNumberGerman(
            response.data.data[key]
          );
        }
      });
    }
  } catch (error) {
    console.error(error);
  }
};
const formatNumberGerman = (num, min = 2, max = 2) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const getGraph = async (type, quarter, attribute) => {
  let result;
  try {
    const response = await axios.get(route("home.get-graph"), {
      params: {
        type: type,
        quarter: quarter,
        catAttribute: attribute,
      },
    });
    result = response.data;
    defaultGraph.data = response.data.data;
    defaultGraph.label = response.data.regions;
  } catch (error) {
    console.error(error);
  }
  return result;
};
const buildGraph = async () => {
  try {
    let result = await getGraph(
      "Lapangan Usaha",
      lapus.value.quarter,
      graphData.value.legend
    );
    graphData.value.data = result.data;
    graphData.value.label = result.regions;
    barIndex.value++;
  } catch (error) {
    console.error(error);
  }
};
const regionsFor = ref(props.default.id);
onMounted(async () => {
  lapus.value = props.sumTime.find((x) => {
    return x.type == "Lapangan Usaha";
  });
  peng.value = props.sumTime.find((x) => {
    return x.type == "Pengeluaran";
  });
  getSummary(props.default, lapus.value.quarter, lapus.value.type);
  getSummary(props.default, peng.value.quarter, peng.value.type);
  let result = await getGraph(
    "Lapangan Usaha",
    lapus.value.quarter,
    graphData.value.legend
  );
  graphData.value.data = result.data;
  graphData.value.label = result.regions;
  barIndex.value++;
});
const searchSummary = () => {
  let region_id = regionsFor.value;
  getSummary(region_id, lapus.value.quarter, lapus.value.type);
  getSummary(region_id, peng.value.quarter, peng.value.type);
};

const buildTime = () => {
  const now = new Date();
  const pad = (n) => n.toString().padStart(2, "0");

  const year = now.getFullYear();
  const month = pad(now.getMonth() + 1); // months are 0-indexed
  const day = pad(now.getDate());
  const hours = pad(now.getHours());
  const minutes = pad(now.getMinutes());
  const seconds = pad(now.getSeconds());

  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};
</script>
<style scoped></style>
