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
            v-if="vifADHBADHK['adhb_now']"
            v-show="showPdrbAndResult['adhb']"
            :data-contents="dataContents"
            :subsectors="subsectors"
            :type="'adhb'"
            :onDemandType="'adhb_now'"
            @update:update-d-o-d="updateDOD"
            @update:updateDataContents="updateDataContents"
          />
          <!-- #endregion -->

          <!-- #region Section: ADHK -->
          <LapusTable
            v-if="vifADHBADHK['adhk_now']"
            v-show="showPdrbAndResult['adhk']"
            :data-contents="dataContents"
            :subsectors="subsectors"
            :type="'adhk'"
            :onDemandType="'adhk_now'"
            @update:update-d-o-d="updateDOD"
            @update:updateDataContents="updateDataContents"
          />
          <!-- #endregion -->

          <!-- #region Section: RESULT -->
          <LapusResultTable
            v-show="showPdrbAndResult['result']"
            :subsectors="subsectors"
            :type="'distribusi'"
          />
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
const dataContents = ref(page.props.dataContents);
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
const vifADHBADHK = ref({
  adhb_now: false,
  adhb_prev: false,
  adhk_now: false,
  adhk_prev: false,
});
const showPdrbAndResult = ref({
  adhb: false,
  adhk: false,
  result: true,
});
const submit = async () => {
  const response = await axios.get(route("token"));
  form._token = response.data;
  if (form.processing) return;
  form.post(route("pdrb.show"), {});
};
// #endregion

const triggerSpinner = ref(false);
watch(
  () => dataContents.value,
  (value) => {}
);
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
