<template>
  <Head title="Lihat Fenomena" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout :entri="mountThis">
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div class="px-[5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="p-3">
          <div class="flex flex-items items-center gap-5">
            <div class="w-full flex gap-5">
              <div class="w-1/2 space-y-2">
                <label for="type">Pilih PDRB<span class="text-danger">*</span></label>
                <Multiselect
                  v-model="form.type"
                  :options="[
                    { label: 'Lapangan Usaha', value: 'Lapangan Usaha' },
                    { label: 'Pengeluaran', value: 'Pengeluaran' },
                  ]"
                  placeholder="-- Pilih PDRB --"
                  :searchable="true"
                />
                <div class="text-danger text-left" v-if="true" id="error-dinas">
                  {{ formError.type }}
                </div>
              </div>
              <div class="w-1/2 space-y-2">
                <label for="year">Pilih Tahun<span class="text-danger">*</span></label>
                <Multiselect
                  v-model="form.year"
                  :options="yearDrop.options"
                  :searchable="true"
                  placeholder="-- Pilih Tahun --"
                />
                <div class="text-danger text-left" v-if="true" id="error-dinas">
                  {{ formError.year }}
                </div>
              </div>
            </div>
          </div>
          <div class="flex items-center justify-end">
            <div class="text-center mr-3 font-bold text-sm" v-if="warningToUser">
              User melakukan perubahan dataset, dan data belum dicari
            </div>
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
              v-if="listTab[5]"
              @click="quartersTab('5')"
              :class="setActiveQuarter('5')"
            >
              Tahunan
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
            <button v-if="!isYear" @click="showTab('qtoq')" :class="setActiveTab('qtoq')">
              Growth (Q-to-Q)
            </button>
            <button @click="showTab('yony')" :class="setActiveTab('yony')">
              Growth (Y-to-Y)
            </button>
            <button @click="showTab('implisit')" :class="setActiveTab('implisit')">
              Indeks Implisit
            </button>
            <button
              @click="downloadHasil('tabel-entry')"
              class="btn-warning-fordone ml-auto"
            >
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
              <th v-for="(node, index) in page.props.regions" :key="index">
                {{ node.name }}
              </th>
            </tr>
          </thead>
          <LapusFenomenaIndex
            v-if="typeShow == 'Lapangan Usaha'"
            :subsectors="page.props.subsectors"
            :regions="page.props.regions"
            :data-contents="dataContents"
            :quarter="quarterCap"
            :type="typeCap"
            ref="childRef"
          />
          <PengFenomenaIndex
            v-if="typeShow == 'Pengeluaran'"
            :subsectors="page.props.subsectors"
            :regions="page.props.regions"
            :data-contents="dataContents"
            :quarter="quarterCap"
            :type="typeCap"
            ref="childRef"
          />
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import LapusFenomenaIndex from "@/Components/LapusFenomenaIndex.vue";
import PengFenomenaIndex from "@/Components/PengFenomenaIndex.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { tableToJson, theDownload } from "@/download";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, nextTick, onMounted, ref, watch } from "vue";

//contoh
const page = usePage();
//
const listTab = ref({
  1: false,
  2: false,
  3: false,
  4: false,
  5: false,
});
const form = useForm({
  type: null,
  year: null,
});
const formError = ref({
  type: null,
  year: null,
});
const typeShow = computed(() => {
  return form.type;
});
watch(form, () => {
  warningToUser.value = true;
});
const childRef = ref(null);
const warningToUser = ref(false);
const mountThis = ref(false);
const isYear = ref(false);
const quarterCap = ref("4");
const typeCap = ref("yony");
const showTabPanel = ref(false);
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
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 11 }, (_, index) => currentYear - index);
const yearDrop = ref({
  value: null,
  options: years.map((year) => ({
    label: year.toString(),
    value: year.toString(),
  })),
});
onMounted(() => {
  mountThis.value = true;
});
const submit = async () => {
  try {
    dataContents.value = [];
    const response = await axios.get(route("fenomena.get-index"), {
      params: {
        type: form.type,
        year: form.year,
      },
    });
    warningToUser.value = false;
    if (response.data.notification[0].type == "success") {
      showTab("qtoq");
      response.data.quarter.forEach((element, index) => {
        listTab.value[element] = true;
        if (index == 0) quartersTab(String(element));
      });
      showTabPanel.value = true;
      dataContents.value = response.data.data;
    }
    showNotification(response.data.notification);
  } catch (error) {
    if (error.response.data.errors) {
      formError.value = Object.keys(error.response.data.errors).reduce((acc, key) => {
        acc[key] = error.response.data.errors[key][0];
        return acc;
      }, {});
    }
  }
};

var def = "btn-info-fordone";
const activeQuarters = ref({
  1: def,
  2: def,
  3: def,
  4: def,
  5: def,
});
const activeTab = ref({
  qtoq: def,
  yony: def,
  implisit: def,
});
const setActiveTab = (value) => {
  return activeTab.value[value];
};
const setActiveQuarter = (value) => {
  return activeQuarters.value[value];
};
const quartersTab = (quarter) => {
  quarterCap.value = quarter;
  if (quarterCap.value == "5") isYear.value = true;
  else isYear.value = false;
  Object.keys(activeQuarters.value).forEach((key) => {
    activeQuarters.value[key] = def;
  });
  activeQuarters.value[quarter] = "btn-success-fordone";
  let currentActive = Object.entries(activeTab.value).find(
    ([key, value]) => value != def
  );
  showTab(currentActive[0]);
};
const showTab = (tab) => {
  Object.keys(activeTab.value).forEach((key) => {
    activeTab.value[key] = def;
  });
  activeTab.value[tab] = "btn-success-fordone";
  typeCap.value = tab;
};
const downloadHasil = async (id) => {
  let list = {};
  let quarterReady = [];
  triggerSpinner.value = true;
  if (childRef.value) childRef.value.prepareDownload();
  try {
    Object.entries(listTab.value).forEach(([key, index]) => {
      if (index) quarterReady.push(key);
    });
    for (let key of Object.keys(activeQuarters.value)) {
      if (quarterReady.includes(key)) {
        quartersTab(key);
        await nextTick();
        for (let keytab of Object.keys(activeTab.value)) {
          showTab(keytab);
          await new Promise((resolve) => setTimeout(resolve, 200));
          if (key == 5) list["Tahunan-" + keytab] = tableToJson(id, "string");
          else list["Triwulan-" + key + "-" + keytab] = tableToJson(id, "string");
        }
      }
    }
    theDownload({ setdata: list });
  } catch (error) {
    console.error(error);
  } finally {
    triggerSpinner.value = false;
  }
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
