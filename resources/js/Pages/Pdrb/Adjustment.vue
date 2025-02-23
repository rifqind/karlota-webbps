<template>
  <Head title="Adjustment" />
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
            <label for="subsectors">Pilih Data:</label>
            <Multiselect
              v-model="form.subsectors"
              :options="subsectorDrop"
              :searchable="true"
              placeholder="-- Pilih Data --"
            />
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
          <div class="text-center mt-3" v-if="warningToUser">
            User melakukan perubahan dataset, dan data belum dicari
          </div>
        </div>
      </div>
      <div
        v-if="showTabPanel"
        class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3"
      >
        <div class="p-5">
          <div class="flex flex-wrap gap-2">
            <button @click="showTab('q1')" :class="setActiveTab('q1')">Triwulan I</button>
            <button @click="showTab('q2')" :class="setActiveTab('q2')">
              Triwulan II
            </button>
            <button @click="showTab('q3')" :class="setActiveTab('q3')">
              Triwulan III
            </button>
            <button @click="showTab('q4')" :class="setActiveTab('q4')">
              Triwulan IV
            </button>
            <button @click="showTab('t')" :class="setActiveTab('t')">Tahunan</button>
          </div>
        </div>
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="fixed-thead" rowspan="2">Wilayah</th>
              <th colspan="3">ADHB</th>
              <th colspan="3">ADHK</th>
              <th colspan="2">Q-to-Q</th>
              <th colspan="2">Y-on-Y</th>
              <th colspan="2">C-to-C</th>
              <th colspan="2">Laju Imp Q-to-Q</th>
              <th colspan="2">SOG Y-on-Y thd Total</th>
              <th colspan="2">Kontribusi thd Total</th>
            </tr>
            <tr>
              <th class="min-w-[150px]">Inisial</th>
              <th class="min-w-[150px]">Adjustment</th>
              <th class="min-w-[150px]">Berjalan</th>
              <th class="min-w-[150px]">Inisial</th>
              <th class="min-w-[150px]">Adjustment</th>
              <th class="min-w-[150px]">Berjalan</th>
              <th class="min-w-[120px]">Inisial</th>
              <th class="min-w-[120px]">Berjalan</th>
              <th class="min-w-[120px]">Inisial</th>
              <th class="min-w-[120px]">Berjalan</th>
              <th class="min-w-[120px]">Inisial</th>
              <th class="min-w-[120px]">Berjalan</th>
              <th class="min-w-[120px]">Inisial</th>
              <th class="min-w-[120px]">Berjalan</th>
              <th class="min-w-[120px]">Inisial</th>
              <th class="min-w-[120px]">Berjalan</th>
              <th class="min-w-[120px]">Inisial</th>
              <th class="min-w-[120px]">Berjalan</th>
            </tr>
          </thead>
          <AdjustmentTable
            v-show="showAdjustment[1]"
            :regions="page.props.regions"
            :data-contents="dataContents"
            :quarter-cap="'1'"
            :data-adjustment="dataOnDemand[1]"
            :data-on-demand="dataOnDemand"
            :data-before="dataBefore"
            @update:update-data-on-demand="updateDataOnDemand"
            @update:update-data-contents="updateDataContents"
          />
          <AdjustmentTable
            v-show="showAdjustment[2]"
            :regions="page.props.regions"
            :data-contents="dataContents"
            :quarter-cap="'2'"
            :data-adjustment="dataOnDemand[2]"
            :data-on-demand="dataOnDemand"
            :data-before="dataBefore"
            @update:update-data-on-demand="updateDataOnDemand"
            @update:update-data-contents="updateDataContents"
          />
          <AdjustmentTable
            v-show="showAdjustment[3]"
            :regions="page.props.regions"
            :data-contents="dataContents"
            :quarter-cap="'3'"
            :data-adjustment="dataOnDemand[3]"
            :data-on-demand="dataOnDemand"
            :data-before="dataBefore"
            @update:update-data-on-demand="updateDataOnDemand"
            @update:update-data-contents="updateDataContents"
          />
          <AdjustmentTable
            v-show="showAdjustment[4]"
            :regions="page.props.regions"
            :data-contents="dataContents"
            :quarter-cap="'4'"
            :data-adjustment="dataOnDemand[4]"
            :data-on-demand="dataOnDemand"
            :data-before="dataBefore"
            @update:update-data-on-demand="updateDataOnDemand"
            @update:update-data-contents="updateDataContents"
          />
          <AdjustmentTahunTable
            v-show="showAdjustment['t']"
            :regions="page.props.regions"
            :data-contents="dataContents"
            :quarter-cap="'t'"
            :data-adjustment="dataOnDemand['t']"
            :data-on-demand="dataOnDemand"
            :data-before="dataBefore"
            @update:update-data-on-demand="updateDataOnDemand"
          />
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import AdjustmentTable from "@/Components/AdjustmentTable.vue";
import AdjustmentTahunTable from "@/Components/AdjustmentTahunTable.vue";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { onMounted, ref } from "vue";

const page = usePage();
const form = useForm({
  _token: null,
  type: page.props.type,
  year: null,
  quarter: null,
  description: null,
  dataBefore: null,
  subsectors: null,
});
const formError = ref({
  year: null,
  quarter: null,
  description: null,
  subsectors: null,
});
var def = "btn-info-fordone";
const activeTab = ref({
  q1: def,
  q2: def,
  q3: def,
  q4: def,
  t: def,
});
const setActiveTab = (value) => {
  return activeTab.value[value];
};
const showTab = (tab) => {
  Object.keys(showAdjustment.value).forEach((key) => {
    showAdjustment.value[key] = false;
  });
  Object.keys(activeTab.value).forEach((key) => {
    activeTab.value[key] = def;
  });
  activeTab.value[tab] = "btn-success-fordone";
  showAdjustment.value[tab.replaceAll("q", "")] = true;
};
const showAdjustment = ref({
  1: false,
  2: false,
  3: false,
  4: false,
  t: false,
});
const showTabPanel = ref(false);
const quarterCap = ref("4");
const dataContents = ref([]);
const dataBefore = ref([]);
const warningToUser = ref(false);
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 0.5) * 200); // Delay based on index
  });
};
const dataOnDemand = ref({ 1: {}, 2: {}, 3: {}, 4: {}, t: {} });
const updateDataOnDemand = (data) => {
  dataOnDemand.value[data.quarter] = data.data;
};
const updateDataContents = (data) => {
  dataContents.value = data;
};
const yearDrop = ref([]);
const quarterDrop = ref([]);
const descDrop = ref([]);
const dataBeforeDrop = ref([]);
const subsectorDrop = ref([]);
onMounted(() => {
  fetchYear();
  // #region Section: subsector select
  let tempData = [];
  page.props.subsectors.forEach((element) => {
    let data, label;
    if (
      (element.code != null &&
        element.code == "a" &&
        element.sector.code == "1" &&
        element.sector.category.type == "Lapangan Usaha") ||
      (element.code == null &&
        element.sector.code == "1" &&
        element.sector.category.type == "Lapangan Usaha")
    ) {
      label = element.sector.category.code + ". " + element.sector.category.name;
      data =
        "category-" +
        element.sector.category.id +
        "-" +
        element.sector.id +
        "-" +
        element.id;
      tempData.push({ value: data, label: label });
    }
    if (
      element.code != null &&
      element.code == "a" &&
      element.sector.category.type == "Lapangan Usaha"
    ) {
      label = element.sector.code + ". " + element.sector.name;
      data =
        "sector-" +
        element.sector.category.id +
        "-" +
        element.sector.id +
        "-" +
        element.id;
      tempData.push({ value: data, label: label });
    }
    if (element.code != null && element.sector.category.type == "Lapangan Usaha") {
      label = element.code + ". " + element.name;
      data =
        "subsector-" +
        element.sector.category.id +
        "-" +
        element.sector.id +
        "-" +
        element.id;
      tempData.push({ value: data, label: label });
    } else if (
      element.code == null &&
      element.sector.code != null &&
      element.sector.category.type == "Lapangan Usaha"
    ) {
      data =
        "subsector-" +
        element.sector.category.id +
        "-" +
        element.sector.id +
        "-" +
        element.id;
      label = element.sector.code + ". " + element.sector.name;
      tempData.push({ value: data, label: label });
    } else if (
      element.code == null &&
      element.sector.code == null &&
      element.sector.category.type == "Lapangan Usaha"
    ) {
      data =
        "subsector-" +
        element.sector.category.id +
        "-" +
        element.sector.id +
        "-" +
        element.id;
      label = element.sector.category.code + ". " + element.name;
      tempData.push({ value: data, label: label });
    }
  });
  subsectorDrop.value = tempData;
  // #endregion
});
// #region Section: FETCHING
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
    const response = await axios.get(route("pdrb.get-adjustment"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: form.quarter,
        description: form.description,
        dataBefore: form.dataBefore,
        subsectors: form.subsectors,
      },
    });
    quarterCap.value = form.quarter;
    showTabPanel.value = true;
    showTab(`q${quarterCap.value}`);
    showNotification(response.data.notification);
    dataContents.value = response.data.current_data;
    dataBefore.value = response.data.previous_data;
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
</script>

<style scoped>
.table {
  font-size: 13px;
}
.fixed-thead {
  position: sticky;
  min-width: 250px;
  left: 0;
  background-color: #175676;
  color: whitesmoke;
  z-index: 1;
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
