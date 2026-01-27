<template>
  <Head title="Entri Data Sekunder" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div id="container-of-entry" class="pb-3">
      <div class="bg-white shadow-md mb-2 rounded-md border border-gray-200">
        <div class="py-4 px-6">
          <h3 class="font-bold text-2xl">
            {{ page.props.sekunder.label }}, Tahun
            {{ page.props.status_sekunder.tahun }}
          </h3>
          <h4 class="mt-2 flex text-2xl items-center">
            <span
              class="badge"
              :class="getClass(page.props.status_sekunder.status)"
              id="badges-status"
            >
              {{ page.props.status_sekunder.status_label }}</span
            >
            <span class="ml-auto text-xl text-right" id="">
              Terakhir diupdate : {{ page.props.status_sekunder.updated_time }}
            </span>
          </h4>
        </div>
      </div>
      <!-- data -->
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead table-info">Data</th>
              <th
                class="text-center align-middle not-fixed"
                v-for="(node, index) in [1, 2, 3, 4]"
                :key="index"
              >
                Triwulan {{ node }}
              </th>
              <th class="text-center align-middle not-fixed">Tahunan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(node, index) in page.props.rows" :key="index">
              <td class="fixed-column">{{ node.label }}</td>
              <td v-for="(item, idx) in [1, 2, 3, 4]" :key="idx">
                <input
                  type="text"
                  class="w-full input-fordone"
                  :value="getData(node.id, item)"
                  :id="'cell-' + node.id + '-' + item"
                  @input="
                    (e) => {
                      debounceHandleInput(e, node.id, item);
                    }
                  "
                  @paste="
                    (e) => {
                      handlePaste(e);
                    }
                  "
                />
              </td>
              <td class="text-right text-md font-bold">{{ getTahunan(node.id) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="errorNaN" class="text-danger">Masih ada NaN di data ini</div>
      <div
        v-if="isNeraca"
        class="bg-white shadow-md mb-2 rounded-md border border-gray-200"
      >
        <div class="py-3 px-2">
          <div class="flex items-center justify-center">
            <Link :href="route('sekunder.index')" class="btn btn-light-fordone border"
              ><font-awesome-icon icon="fas fa-chevron-left" />
              Kembali
            </Link>
            <button class="ml-auto btn-success-fordone" @click.prevent="submit(true)">
              <font-awesome-icon icon="fa-solid fa-check" /> Simpan
            </button>
          </div>
        </div>
      </div>
      <!-- growth -->

      <div class="flex flex-wrap gap-2 mt-5 mb-2">
        <button @click="showTab('g_qtoq')" :class="setActiveTab('g_qtoq')">
          Growth (Q-to-Q)
        </button>
        <button @click="showTab('g_ytoy')" :class="setActiveTab('g_ytoy')">
          Growth (Y-to-Y)
        </button>
        <!-- <button @click="showTab('g_ctoc')" :class="setActiveTab('g_ctoc')">
              Growth (C-to-C)
            </button> -->
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead table-success">Growth</th>
              <th
                class="text-center table-success align-middle not-fixed"
                v-for="(node, index) in [1, 2, 3, 4]"
                :key="index"
              >
                Triwulan {{ node }}
              </th>
              <th class="text-center table-success align-middle not-fixed">Tahunan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(node, index) in page.props.rows" :key="index">
              <td class="fixed-column">{{ node.label }}</td>
              <td
                class="text-right font-bold"
                v-for="(item, idx) in [1, 2, 3, 4]"
                :key="idx"
              >
                <span :class="setClass(growthMap[`${node.id}-${item}`])" class="badge">{{
                  growthMap[`${node.id}-${item}`]
                }}</span>
              </td>
              <td class="text-right text-md font-bold">
                <span :class="setClass(growthMap[`${node.id}-tahun`])" class="badge">
                  {{ growthMap[`${node.id}-tahun`] }}</span
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
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUpdated, ref } from "vue";

const page = usePage();
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
const getClass = (id) => {
  if (id == 1) return "badge-status-empat";
  if (id == 2) return "badge-status-dua";
};
const form = useForm({
  _token: null,
  status_id: page.props.status_sekunder.id,
  datacontent: page.props.datacontent,
});

//tablehandle
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const getData = (r, tw) => {
  const datas = form.datacontent.find((x) => {
    return x.row_id == r && x.triwulan == tw;
  });
  if (datas) {
    let formattedResult;
    if (datas.data == "" || datas.data == null) formattedResult = null;
    else formattedResult = formatNumberGerman(Number(datas.data), 0, 9);
    return formattedResult;
  }
};
const handleInput = (e, r, tw) => {
  let value = e.target.value;
  value = String(value).replaceAll(".", "").replace(",", ".");
  const dataIndex = form.datacontent.findIndex((x) => {
    return x.row_id == r && x.triwulan == tw;
  });
  if (dataIndex != -1) form.datacontent[dataIndex].data = value;
};
const debounceHandleInput = debounce((e, r, tw) => {
  handleInput(e, r, tw);
}, 300);
const handlePaste = (e) => {
  const items = e.clipboardData.items;
  for (let i = 0; i < items.length; i++) {
    if (items[i].type == "text/plain") {
      items[i].getAsString((text) => {
        const columnIndex = e.target.closest("td").cellIndex;
        const rowIndex = e.target.closest("tr").rowIndex;
        const lines = text.trim().split("\n");
        lines.forEach((line, index) => {
          const cells = line.trim().split("\t");
          cells.forEach((cell, subIndex) => {
            const row = rowIndex + index;
            const col = columnIndex + subIndex;
            const table = e.target.closest("table");
            const tableRow = table.rows[row];
            if (tableRow) {
              const tableCell = tableRow.cells[col];
              if (tableCell) {
                let input = tableCell.querySelector('input:not([type="hidden"])');
                if (input) {
                  const r = input.id.split("-")[1];
                  const tw = input.id.split("-")[2];
                  input = cell;
                  //   if (cell == "-") cell = String(0);
                  let formatCell = String(cell).replaceAll(".", "").replace(",", ".");
                  const dataIndex = form.datacontent.findIndex((x) => {
                    return x.row_id == r && x.triwulan == tw;
                  });
                  if (dataIndex != -1) form.datacontent[dataIndex].data = formatCell;
                }
              }
            }
          });
        });
      });
    }
  }
};
const getTahunan = (r) => {
  const filteredData = form.datacontent.filter((x) => x.row_id == r);
  const result = filteredData.reduce((sum, item) => sum + Number(item.data), 0);
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
// growth calculate
var def = "btn-info-fordone";
const activeTab = ref({
  g_qtoq: def,
  g_ytoy: def,
  g_ctoc: def,
});
const setActiveTab = (value) => {
  return activeTab.value[value];
};
const tabValue = ref(null);
const showTab = (tab) => {
  Object.keys(activeTab.value).forEach((key) => {
    activeTab.value[key] = def;
  });
  activeTab.value[tab] = "btn-success-fordone";
  tabValue.value = tab;
};
const getGrowth = (r, tw) => {
  const databefore = page.props.datacontent_before;
  const current = form.datacontent.find((x) => {
    return x.row_id == r && x.triwulan == tw;
  });
  let previous = { data: 0 };
  let growth = 0;
  let divisor = 0;
  let dividend = 0;
  if (tabValue.value == "g_qtoq") {
    if (tw == 1) {
      previous = databefore.find((x) => {
        return x.row_id == r && x.triwulan == 4;
      });
    } else {
      previous = form.datacontent.find((x) => {
        return x.row_id == r && x.triwulan == tw - 1;
      });
    }
  } else if (tabValue.value == "g_ytoy") {
    previous = databefore.find((x) => {
      return x.row_id == r && x.triwulan == tw;
    });
  } else if (tabValue.value == "g_ctoc") {
    for (let cumulative = 0; cumulative <= tw; cumulative++) {}
  }
  if (!previous) previous = { data: 0 };
  divisor = previous.data;
  dividend = current.data;
  growth = divisor != 0 && dividend != 0 ? (dividend / divisor) * 100 - 100 : 0;
  return formatNumberGerman(growth.toFixed(4), 2, 4);
};
const getGrowthTahunan = (r) => {
  if (tabValue.value == "g_ytoy") {
    const filteredData = form.datacontent.filter((x) => x.row_id == r);
    const current = filteredData.reduce((sum, item) => sum + Number(item.data), 0);

    const filterBefore = page.props.datacontent_before.filter((x) => x.row_id == r);
    const previous = filterBefore.reduce((sum, item) => sum + Number(item.data), 0);

    let divisor = previous;
    let dividend = current;
    let growth = divisor != 0 && dividend != 0 ? (dividend / divisor) * 100 - 100 : 0;
    return formatNumberGerman(growth.toFixed(4), 2, 4);
  }
};
const growthMap = computed(() => {
  let map = [];
  page.props.rows.forEach((r) => {
    [1, 2, 3, 4].forEach((tw) => {
      const key = `${r.id}-${tw}`;
      map[key] = getGrowth(r.id, tw);
    });
    const yearKey = `${r.id}-tahun`;
    map[yearKey] = getGrowthTahunan(r.id);
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
// const inputDisabled = ref(false);
onMounted(() => {
  showTab("g_qtoq");
});
//form
const validateContent = () => {
  return form.datacontent.some((e) => isNaN(e.data));
};
const errorNaN = ref(false);
const submit = async () => {
  // form.submitted = thissubmit;
  try {
    let result = validateContent();
    if (result) {
      errorNaN.value = true;
      return;
    }
    errorNaN.value = false;
    const token = await axios.get(route("token"));
    form._token = token.data;
    form.post(route("sekunder.update"), {
      onSuccess: (response) => {
        showNotification(response.props.notification);
      },
    });
  } catch (error) {
    console.error(error);
  }
};
const isNeraca =
  page.props.auth.user.role == "admin" || page.props.auth.user.role == "user";
</script>

<style scoped>
.fixed-thead {
  position: sticky;
  width: 400px;
  left: 0;
  color: whitesmoke;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.table-info {
  background-color: #175676;
}

.table-success {
  background-color: #1d845b;
}

.table {
  table-layout: fixed;
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  font-size: smaller;
  /* Avoid extra spacing */
}

.input-fordone {
  padding: 5px 5px 5px 5px;
  text-align: right;
  /* font-size: smaller; */
}
.badge {
  font-size: 100%;
}
</style>
