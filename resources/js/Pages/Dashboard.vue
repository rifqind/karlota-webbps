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
      SUMMARY PDRB LAPANGAN USAHA,
      <span class="text-sm"
        >Triwulan {{ lapus.quarter }} - {{ lapus.description }} (update terakhir:
        {{ lapus.waktu }})</span
      >
    </div>
    <div class="flex flex-wrap mt-2 items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-indigo-500 border-r-4"
      >
        <div class="p-4">
          <div class="grid grid-cols-1 gap-6">
            <!-- Row 1: ADHB & ADHK -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-bold text-base">ADHB</h3>
                </div>
                <div>{{ summaryData.lapus.adhb }}</div>
              </div>
              <div class="flex flex-col">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-bold text-base">ADHK</h3>
                  <font-awesome-icon
                    icon="fa-solid fa-industry"
                    class="fa-xl text-indigo-500"
                  />
                </div>
                <div>{{ summaryData.lapus.adhk }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-indigo-500 border-r-4"
      >
        <div class="p-4">
          <!-- Row 2: Growth QtoQ, YonY, CtoC -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Growth QtoQ</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.qtoq > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.qtoq > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.qtoq }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Growth YonY</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.yony > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.yony > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.yony }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Growth CtoC</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.ctoc > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.ctoc > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.ctoc }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-indigo-500 border-r-4"
      >
        <div class="p-4">
          <!-- Row 3: Indeks Implisit, IQtoQ, IYonY -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Indeks Implisit</h3>
                <font-awesome-icon
                  icon="fa-solid fa-percent"
                  class="fa-xl text-pink-500"
                />
              </div>
              <div>{{ summaryData.lapus.idx }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Laju Implisit QtoQ</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.iqtoq > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.iqtoq > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.iqtoq }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Laju Implisit YonY</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.lapus.iyony > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.lapus.iyony > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.lapus.iyony }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="font-bold text-xl mt-3">
      SUMMARY PDRB PENGELUARAN,
      <span class="text-sm"
        >Triwulan {{ peng.quarter }} - {{ peng.description }} (update terakhir:
        {{ peng.waktu }})</span
      >
    </div>
    <div class="flex flex-wrap mt-2 items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-orange-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <div class="grid grid-cols-1 gap-6">
            <!-- Row 1: ADHB & ADHK -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-bold text-base">ADHB</h3>
                </div>
                <div>{{ summaryData.peng.adhb }}</div>
              </div>
              <div class="flex flex-col">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-bold text-base">ADHK</h3>
                  <font-awesome-icon
                    icon="fa-solid fa-coins"
                    class="fa-xl text-orange-500"
                  />
                </div>
                <div>{{ summaryData.peng.adhk }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-orange-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <!-- Row 2: Growth QtoQ, YonY, CtoC -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Growth QtoQ</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.peng.qtoq > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.peng.qtoq > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.peng.qtoq }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Growth YonY</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.peng.yony > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.peng.yony > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.peng.yony }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Growth CtoC</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.peng.ctoc > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.peng.ctoc > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.peng.ctoc }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex flex-wrap items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-orange-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <!-- Row 3: Indeks Implisit, IQtoQ, IYonY -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Indeks Implisit</h3>
                <font-awesome-icon
                  icon="fa-solid fa-percent"
                  class="fa-xl text-pink-500"
                />
              </div>
              <div>{{ summaryData.peng.idx }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Laju Implisit QtoQ</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.peng.iqtoq > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.peng.iqtoq > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.peng.iqtoq }}</div>
            </div>
            <div class="flex flex-col">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">Laju Implisit YonY</h3>
                <font-awesome-icon
                  :icon="
                    summaryData.peng.iyony > 0
                      ? 'fa-solid fa-arrow-trend-up'
                      : 'fa-solid fa-arrow-trend-down'
                  "
                  :class="
                    summaryData.peng.iyony > 0
                      ? 'fa-xl text-green-500'
                      : 'fa-xl text-red-500'
                  "
                />
              </div>
              <div>{{ summaryData.peng.iyony }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="font-bold text-xl mt-3">GRAFIK</div>
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
import { onMounted, ref } from "vue";
import Multiselect from "@vueform/multiselect";
import ModalBs from "@/Components/ModalBs.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { triggerSpinner } from "@/axiosSetup";
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
    type: String,
    required: false,
  },
});
const confirmationSummaries = ref(false);
const summaryData = ref({
  lapus: {
    adhb: null,
    adhk: null,
    qtoq: null,
    yony: null,
    ctoc: null,
    idx: null,
    iqtoq: null,
    iyony: null,
  },
  peng: {
    adhb: null,
    adhk: null,
    qtoq: null,
    yony: null,
    ctoc: null,
    idx: null,
    iqtoq: null,
    iyony: null,
  },
});
const form = useForm({
  _token: null,
  lapus_id: null,
  peng_id: null,
});
const summaryForm = useForm({
  _token: null,
  region_id: null,
  quarter: null,
});
const timestart = ref(null);
const thisHidden = ref(false);
const buildSummaries = async () => {
  timestart.value = buildTime();
  thisHidden.value = true;
  let setupArray = ["category", "sector", "subsector", "total"];
  setupArray.forEach(async (element) => {
    try {
      const response = await axios.get(route("home.index"), {
        params: {
          setup: element,
        },
      });
      form.lapus_id = response.data.lapus_period;
      form.peng_id = response.data.peng_period;
    } catch (error) {
      console.error(error);
    }
  });
  const _token = await axios.get(route("token"));
  form._token = _token.data;
  form.post(route("home.update-time"));
  confirmationSummaries.value = false;
  thisHidden.value = false;
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
    if (response.data.type == "Lapangan Usaha")
      summaryData.value.lapus = response.data.data;
    else summaryData.value.peng = response.data.data;
  } catch (error) {
    console.error(error);
  }
};
const lapus = ref({ quarter: null, description: null, waktu: null, nama: null });
const peng = ref({ quarter: null, description: null, waktu: null, nama: null });
const regionsFor = ref(1);
onMounted(() => {
  lapus.value = props.sumTime.find((x) => {
    return x.type == "Lapangan Usaha";
  });
  peng.value = props.sumTime.find((x) => {
    return x.type == "Pengeluaran";
  });
  getSummary(props.default, lapus.value.quarter, lapus.value.type);
  getSummary(props.default, peng.value.quarter, peng.value.type);
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
