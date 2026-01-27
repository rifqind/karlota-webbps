<template>
  <Head title="View" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <div class="flex flex-wrap gap-2">
      <button
        @click="changeYear(data)"
        v-for="data in props.listOfYear"
        class="btn-sm btn"
        :class="setActiveYear(data)"
      >
        {{ data }}
      </button>
    </div>
    <div class="my-4 space-y-2" v-for="(node, index) in currentData" :key="index">
      <div class="bg-white shadow-md rounded-md border border-gray-200">
        <div class="py-4 px-6">
          <h3 class="font-bold text-2xl">
            {{ node.label }}, Tahun
            {{ node.tahun }}
          </h3>
          <h4 class="mt-2 flex text-2xl items-center">
            <span class="ml-auto text-xl text-right" id="">
              Terakhir diupdate :
              {{ new Date(node.status.updated_at).toLocaleString("id-ID") }}
            </span>
          </h4>
        </div>
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead table-info">Data</th>
              <th class="text-center align-middle not-fixed" v-for="tw in [1, 2, 3, 4]">
                Triwulan {{ tw }}
              </th>
              <th class="text-center align-middle not-fixed">Jumlah/Tahunan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in node.row">
              <td class="fixed-column">{{ r.label }}</td>
              <td v-for="(item, idx) in [1, 2, 3, 4]" :key="idx" class="text-right">
                {{ getData(r.id, item, node.data) }}
              </td>
              <td class="text-right text-md font-bold">
                {{ getTahunan(node.data, r.id) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex flex-wrap gap-2">
        <button
          @click="showTabButton(index)"
          class="btn btn-sm"
          :class="arrayTabStatus[index] ? 'btn-red-fordone' : 'btn-warning-fordone'"
        >
          {{ arrayTabStatus[index] ? "Hide Growth" : "Show Growth" }}
        </button>
        <template v-if="arrayTabStatus[index]">
          <button
            @click="showTab(index, 'g_qtoq')"
            :class="setActiveTab(index, 'g_qtoq')"
          >
            Growth (Q-to-Q)
          </button>
          <button
            @click="showTab(index, 'g_ytoy')"
            :class="setActiveTab(index, 'g_ytoy')"
          >
            Growth (Y-to-Y)
          </button>
        </template>
      </div>
      <div v-if="arrayTabStatus[index]" class="overflow-x-scroll">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead table-success">Growth</th>
              <th
                class="text-center table-success align-middle not-fixed"
                v-for="(ng, ing) in [1, 2, 3, 4]"
                :key="ing"
              >
                Triwulan {{ ng }}
              </th>
              <th class="text-center table-success align-middle not-fixed">Tahunan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in node.row">
              <td class="fixed-column">{{ r.label }}</td>
              <td v-for="(item, idx) in [1, 2, 3, 4]" :key="idx" class="text-right">
                <span
                  :class="setClass(growthMap[`${node.sekunder_id}-${r.id}-${item}`])"
                  class="badge"
                  >{{ growthMap[`${node.sekunder_id}-${r.id}-${item}`] }}</span
                >
              </td>
              <td class="text-right text-md font-bold">
                <span
                  class="badge"
                  :class="setClass(growthMap[`${node.sekunder_id}-${r.id}-tahun`])"
                  >{{ growthMap[`${node.sekunder_id}-${r.id}-tahun`] }}</span
                >
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import { computed, onMounted, ref, watch } from "vue";

const props = defineProps({
  listOfYear: { type: Array },
  data: { type: Array },
  data_before: { type: Array },
  latestYear: { type: Number },
  produsen: { type: Object },
});
const currentData = ref(props.data);
watch(
  () => props.data,
  (value) => {
    currentData.value = value;
  }
);
const dataBefore = ref(props.data_before);
watch(
  () => props.data_before,
  (value) => {
    dataBefore.value = value;
  }
);
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const getData = (r, tw, data) => {
  let result = data.find((x) => {
    return x.row_id == r && x.triwulan == tw;
  });
  if (result) {
    let formattedResult = null;
    if (result.data == "" || result.data == null) return formattedResult;
    else return formatNumberGerman(Number(result.data), 2, 9);
  }
};
const getTahunan = (data, r) => {
  const filteredData = data.filter((x) => x.row_id == r);
  const result = filteredData.reduce((sum, item) => sum + Number(item.data), 0);
  return formatNumberGerman(result, 2, 9);
};
//
const activeTab = ref(
  Array.from({ length: props.data.length }, () => ({
    g_qtoq: def,
    g_ytoy: def,
  }))
);
const arrayTabStatus = ref(Array.from({ length: props.data.length }, () => false));
var def = "btn-info-fordone";
const setActiveTab = (i, value) => {
  return activeTab.value?.[i]?.[value] ?? def;
};
const tabValue = ref(null);
const showTab = (index, tab) => {
  Object.keys(activeTab.value[index]).forEach((key) => {
    activeTab.value[index][key] = def;
  });
  activeTab.value[index][tab] = "btn-success-fordone";
  tabValue.value = tab;
};
const showTabButton = (i) => {
  arrayTabStatus.value[i] = !arrayTabStatus.value[i];
  if (arrayTabStatus.value[i]) showTab(i, "g_qtoq");
};
const getGrowth = (r, tw, data, sId) => {
  const current = data.find((x) => {
    return x.row_id == r && x.triwulan == tw;
  });
  const databefore = dataBefore.value.find((x) => {
    return x.sekunder_id == sId;
  });
  let previous = { data: 0 };
  let growth = 0;
  let divisor = 0;
  let dividend = 0;
  if (tabValue.value == "g_qtoq") {
    if (tw == 1) {
      if (databefore) {
        previous = databefore.data.find((x) => {
          return x.row_id == r && x.triwulan == 4;
        });
      }
    } else {
      previous = data.find((x) => {
        return x.row_id == r && x.triwulan == tw - 1;
      });
    }
  } else if (tabValue.value == "g_ytoy") {
    if (databefore) {
      previous = databefore.data.find((x) => {
        return x.row_id == r && x.triwulan == tw;
      });
    }
  } else if (tabValue.value == "g_ctoc") {
    for (let cumulative = 0; cumulative <= tw; cumulative++) {}
  }
  if (!previous) previous = { data: 0 };
  divisor = previous.data;
  dividend = current.data;
  if (!current.data) return null;
  growth = divisor != 0 && dividend != 0 ? (dividend / divisor) * 100 - 100 : 0;
  return formatNumberGerman(growth.toFixed(4), 2, 4);
};
const getGrowthTahunan = (r, data, sId) => {
  if (tabValue.value == "g_ytoy") {
    const filteredData = data.filter((x) => x.row_id == r);
    const current = filteredData.reduce((sum, item) => sum + Number(item.data), 0);

    let befores = dataBefore.value.find((x) => {
      return x.sekunder_id == sId;
    });
    let previous;
    if (befores) {
      const filterBefore = befores.data.filter((x) => x.row_id == r);
      previous = filterBefore.reduce((sum, item) => sum + Number(item.data), 0);
    } else previous = 0;

    let divisor = previous;
    let dividend = current;
    let growth = divisor != 0 && dividend != 0 ? (dividend / divisor) * 100 - 100 : 0;
    return formatNumberGerman(growth.toFixed(4), 2, 4);
  }
};
const growthMap = computed(() => {
  const map = [];
  currentData.value.forEach((sekunder) => {
    sekunder.row.forEach((r) => {
      [1, 2, 3, 4].forEach((tw) => {
        const key = `${sekunder.sekunder_id}-${r.id}-${tw}`;
        map[key] = getGrowth(r.id, tw, sekunder.data, sekunder.sekunder_id);
      });
      const yearKey = `${sekunder.sekunder_id}-${r.id}-tahun`;
      map[yearKey] = getGrowthTahunan(r.id, sekunder.data, sekunder.sekunder_id);
    });
  });
  return map;
});
const setClass = (number) => {
  if (number) {
    let converted = number.replaceAll(".", "").replace(",", ".");
    if (Number(converted) > 0) return "badge-status-dua";
    else if (Number(converted) < 0) return "badge-status-empat";
    else if (Number(converted) == 0) return "badge-info";
  }
};
//change year
const activeYear = ref(
  Object.fromEntries(props.listOfYear.map((year) => [year, "btn-light-fordone"]))
);
const setActiveYear = (key) => {
  return activeYear.value[key];
};
const thisYear = ref(props.latestYear);
const changeYear = (tahun, mounted = false) => {
  Object.keys(activeYear.value).forEach((k) => {
    activeYear.value[k] = "btn-light-fordone";
  });
  activeYear.value[tahun] = "btn-success-fordone";
  thisYear.value = tahun;
  if (!mounted) {
    fetchData();
  }
};
onMounted(() => {
  changeYear(props.latestYear, true);
});
const fetchData = async () => {
  try {
    const { data } = await axios.get(route("sekunder.by-dinas-change"), {
      params: {
        id: props.produsen.id,
        tahun: thisYear.value,
      },
    });
    currentData.value = data.data;
    dataBefore.value = data.data_before;
  } catch (error) {
    console.error("Error fetching : ", error);
  }
};
</script>

<style scoped>
.table-info {
  background-color: #175676;
}

.table-success {
  background-color: #1d845b;
}
.badge {
  font-size: 100%;
}
</style>
