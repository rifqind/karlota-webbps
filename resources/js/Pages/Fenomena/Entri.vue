<template>
  <Head title="Entri Fenomena" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />

    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Entri Fenomena</label>
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
              :options="yearDrop.options"
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
              :options="[
                { label: 'Triwulan 1', value: '1' },
                { label: 'Triwulan 2', value: '2' },
                { label: 'Triwulan 3', value: '3' },
                { label: 'Triwulan 4', value: '4' },
                { label: 'Tahunan', value: '5' },
              ]"
              :searchable="true"
              placeholder="-- Pilih Triwulan --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.quarter }}
            </div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Kabupaten/Kota<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.regions"
              :options="page.props.regions"
              :searchable="true"
              placeholder="-- Pilih Kabupaten/Kota --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas">
              {{ formError.regions }}
            </div>
          </div>
          <div class="flex items-center space-x-2 justify-end">
            <div
              @click="downloadHasil('tabel-entry')"
              class="btn-warning-fordone text-center"
            >
              Download
            </div>
            <div
              v-if="!changeView"
              @click="modalValue = true"
              class="btn-fordone bg-[#60435F] text-white text-center"
            >
              Ambil Nilai
            </div>
            <div
              v-if="!changeView"
              @click="changeImplisit = !changeImplisit"
              class="btn-warning-fordone text-center"
            >
              {{ changeImplisit ? "Hide Implisit" : "Show Implisit" }}
            </div>
            <div
              @click="changeView = !changeView"
              class="btn-success-fordone text-center"
            >
              Ganti Tampilan
            </div>
            <div class="btn-info-fordone w-[130px] text-center" @click.prevent="submit">
              <font-awesome-icon icon="fa-solid fa-magnifying-glass" />
              Cari Data
            </div>
          </div>
        </div>
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="fixed-thead">Komponen</th>
              <template v-if="changeView">
                <th v-if="!isYear">Fenomena Q-to-Q</th>
                <th>Fenomena Y-on-Y</th>
                <th v-if="page.props.type == 'Lapangan Usaha'">Fenomena Implisit</th>
                <th v-if="page.props.type == 'Pengeluaran'">Fenomena C-to-C</th>
              </template>
              <template v-else>
                <th class="w-[150px]">Pertumbuhan</th>
                <th class="w-[150px]">Nilai</th>
                <th>Fenomena</th>
              </template>
            </tr>
          </thead>
          <LapusFenomena
            v-if="page.props.type == 'Lapangan Usaha'"
            v-show="showTabPanel && changeView"
            :subsectors="page.props.subsectors"
            :data-contents="dataContents"
            :fenomena-status="fenomenasets.status"
            :is-year="isYear"
            @update:update-data-contents="updateDataContents"
            @update:handle-input="handleInput"
            @update:handle-paste="handlePaste"
            @update:set-default-data="setDefaultData"
          />
          <LapusFenomenaCoded
            v-if="page.props.type == 'Lapangan Usaha'"
            v-show="showTabPanel && !changeView"
            :subsectors="page.props.subsectors"
            :data-contents="dataContents"
            :fenomena-status="fenomenasets.status"
            :is-year="isYear"
            :is-implisit="changeImplisit"
            :table-model="tableModel"
            @update:update-data-contents="updateDataContents"
            @update:handle-input="handleInput"
            @update:handle-paste="handlePaste"
            @update:set-default-data="setDefaultData"
            @update:update-fenom-spec-dev="updateFenomSpecDev"
          />
          <PengFenomena
            v-if="page.props.type == 'Pengeluaran'"
            v-show="showTabPanel && changeView"
            :subsectors="page.props.subsectors"
            :data-contents="dataContents"
            :fenomena-status="fenomenasets.status"
            :is-year="isYear"
            @update:update-data-contents="updateDataContents"
            @update:handle-input="handleInput"
            @update:handle-paste="handlePaste"
            @update:set-default-data="setDefaultData"
          />
          <PengFenomenaCoded
            v-if="page.props.type == 'Pengeluaran'"
            v-show="showTabPanel && !changeView"
            :subsectors="page.props.subsectors"
            :data-contents="dataContents"
            :fenomena-status="fenomenasets.status"
            :is-year="isYear"
            :is-implisit="changeImplisit"
            :table-model="tableModel"
            @update:update-data-contents="updateDataContents"
            @update:handle-input="handleInput"
            @update:handle-paste="handlePaste"
            @update:set-default-data="setDefaultData"
            @update:update-fenom-spec-dev="updateFenomSpecDev"
          />
        </table>
      </div>
      <div
        v-if="showTabPanel"
        class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3"
      >
        <div class="p-5">
          <div class="flex justify-end space-x-2">
            <button
              v-if="fenomenasets.status != 'Submitted'"
              @click.prevent="saveEntri"
              class="btn-info-fordone"
            >
              <font-awesome-icon icon="fa fa-save" />
              Simpan Data
            </button>
            <button
              v-if="fenomenasets.status == 'Entry'"
              @click.prevent="submitEntri"
              class="btn-success-fordone"
            >
              <font-awesome-icon icon="fa fa-check" />
              Submit Data
            </button>
            <button
              v-if="fenomenasets.status == 'Submitted'"
              @click.prevent="unsubmitEntri"
              class="btn-red-fordone"
            >
              <font-awesome-icon icon="fa-solid fa-circle-xmark" />
              Unsubmit Data
            </button>
          </div>
        </div>
      </div>
    </div>
  </GeneralLayout>
  <ModalBs
    :-modal-status="modalValue"
    @close="modalValue = false"
    :modal-size="'min-w-[40vw]'"
    :title="'Ambil Nilai'"
  >
    <template #modalBody>
      <div class="form-group">
        <div class="space-y-2 mb-3">
          <label for="year">Pilih Triwulan<span class="text-danger">*</span></label>
          <Multiselect
            v-model="quarterDrop.selected"
            :options="quarterDrop.options"
            :searchable="true"
            placeholder="-- Pilih Triwulan --"
            @change="fetchPeriod"
          />
        </div>
        <div class="mb-3 space-y-2">
          <label for="year"
            >Pilih Periode Putaran<span class="text-danger">*</span></label
          >
          <Multiselect
            v-model="descDrop.selected"
            :options="descDrop.options"
            :searchable="true"
            placeholder="-- Pilih Periode Putaran --"
            @change="fetchYearBefore"
          />
        </div>
        <div class="mb-3 space-y-2">
          <label for="year">Pilih Data Tahun Sebelumnya</label>
          <Multiselect
            v-model="dataBeforeDrop.selected"
            :options="dataBeforeDrop.options"
            :searchable="true"
            placeholder="-- Pilih Data Tahun Sebelumnya (opsional, jika tidak dipilih maka akan memilih data tahun paling terbaru/terakhir) --"
          />
        </div>
        <div class="text-red-900">
          {{ errorModula ?? null }}
        </div>
      </div>
    </template>
    <template #modalFunction>
      <button
        type="button"
        class="btn-success-fordone btn-sm"
        @click.prevent="fetchingData"
      >
        Fetch
      </button>
    </template>
  </ModalBs>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import LapusFenomena from "@/Components/LapusFenomena.vue";
import LapusFenomenaCoded from "@/Components/LapusFenomenaCoded.vue";
import ModalBs from "@/Components/ModalBs.vue";
import PengFenomena from "@/Components/PengFenomena.vue";
import PengFenomenaCoded from "@/Components/PengFenomenaCoded.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import {
  buildAOAFenomena,
  buildRowDefsLapus,
  buildRowDefsPeng,
  tableToJson,
  theDownload,
} from "@/download";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, onMounted, ref, watch } from "vue";

const page = usePage();
const dataContents = ref({});
const form = useForm({
  dataContents: null,
  _token: null,
  type: page.props.type,
  year: null,
  quarter: null,
  regions: null,
});
const formError = ref({
  year: null,
  quarter: null,
  regions: null,
});
const isYear = ref(false);
const defaultData = ref([]);
const showTabPanel = ref(false);
const fenomenasets = ref({ status: "Entry" });
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
const updateDataContents = (value) => {
  dataContents.value = value;
};
const handleInput = (value) => {
  dataContents.value[value.theIndex][value.type] = value.value;
};
const setDefaultData = (value) => {
  defaultData.value = value;
};
const handlePaste = (value) => {
  dataContents.value[value.theIndex][value.type] = value.value;
};
const updateFenomSpecDev = (value) => {
  rowspanspec.value = value;
};
onMounted(() => {});
const submit = async () => {
  try {
    dataContents.value = JSON.parse(JSON.stringify(defaultData.value));
    const response = await axios.get(route("fenomena.show"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: form.quarter,
        regions: form.regions,
      },
    });

    response.data.data.forEach((element) => {
      const theIndex = dataContents.value.findIndex((x) => {
        return (
          x.category_id == element.category_id &&
          x.sector_id == element.sector_id &&
          x.subsector_id == element.subsector_id
        );
      });
      if (theIndex != -1) {
        dataContents.value[theIndex].id = element.id;
        dataContents.value[theIndex].fenomena_sets = element.fenomena_sets;
        dataContents.value[theIndex].qtoq = element.qtoq;
        dataContents.value[theIndex].yony = element.yony;
        dataContents.value[theIndex].implisit = element.implisit;
      }
    });
    showTabPanel.value = true;
    fenomenasets.value = response.data.fenomena_set;
    formError.value = {
      year: null,
      quarter: null,
      regions: null,
    };
    isYear.value = form.quarter == 5 ? true : false;
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
const getData = async () => {
  dataContents.value = JSON.parse(JSON.stringify(defaultData.value));
  const response = await axios.get(route("fenomena.show"), {
    params: {
      type: page.props.type,
      year: form.year,
      quarter: form.quarter,
      regions: form.regions,
    },
  });
  response.data.data.forEach((element) => {
    const theIndex = dataContents.value.findIndex((x) => {
      return (
        x.category_id == element.category_id &&
        x.sector_id == element.sector_id &&
        x.subsector_id == element.subsector_id
      );
    });
    if (theIndex != -1) {
      dataContents.value[theIndex].id = element.id;
      dataContents.value[theIndex].fenomena_sets = element.fenomena_sets;
      dataContents.value[theIndex].qtoq = element.qtoq;
      dataContents.value[theIndex].yony = element.yony;
      dataContents.value[theIndex].implisit = element.implisit;
    }
  });
  fenomenasets.value = response.data.fenomena_set;
};
const saveEntri = async () => {
  const thisForm = useForm({
    dataContents: dataContents.value,
    type: page.props.type,
    fenomena_sets: fenomenasets.value.id,
    _token: null,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("fenomena.save-fenomena"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
      getData();
    },
  });
};
const submitEntri = async () => {
  const thisForm = useForm({
    dataContents: dataContents.value,
    type: page.props.type,
    id: fenomenasets.value.id,
    _token: null,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("fenomena.submit-fenomena"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
      getData();
      if (response.props.notification[0].type == "success")
        fenomenasets.value.status = "Submitted";
    },
  });
};
const unsubmitEntri = async () => {
  const thisForm = useForm({
    id: fenomenasets.value.id,
    type: page.props.type,
    _token: null,
  });
  const response = await axios.get(route("token"));
  thisForm._token = response.data;
  if (thisForm.processing) return;
  thisForm.post(route("fenomena.unsubmit-fenomena"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
      if (response.props.notification[0].type == "success")
        fenomenasets.value.status = "Entry";
    },
  });
};
const rowspanspec = ref([]);
const downloadHasil = (id) => {
  if (changeView.value) {
    try {
      triggerSpinner.value = true;
      let list = {};
      list["fenomena-normal"] = tableToJson(id, "text");
      theDownload({ setdata: list });
    } catch (error) {
      console.error(error);
    } finally {
      triggerSpinner.value = false;
    }
  } else {
    let rowDefs =
      page.props.type == "Lapangan Usaha"
        ? buildRowDefsLapus(page.props.subsectors)
        : buildRowDefsPeng(page.props.subsectors);
    let aoas = buildAOAFenomena({
      rowDefs: rowDefs,
      growth: tableModel.value.growth,
      rowspanspec: rowspanspec.value,
      data: dataContents.value,
    });
    let list = {};
    list["fenomena"] = aoas;
    theDownload({ setdata: list });
  }
};
//
const changeView = ref(false);
const changeImplisit = ref(true);
const modalValue = ref(false);
const quarterDrop = ref({ selected: null, options: [] });
const descDrop = ref({ selected: null, options: [] });
const dataBeforeDrop = ref({ selected: null, options: [] });
const fetchQuarter = async (value) => {
  if (value) {
    try {
      const response = await axios.get(route("period.fetchQuarter"), {
        params: {
          type: page.props.type,
          year: value,
        },
      });
      let result = response.data;
      quarterDrop.value.options = result;
    } catch (error) {
      console.error(error);
    }
  }
};
const fetchPeriod = async (value, type = "normal") => {
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
      descDrop.value.options = result;
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
    dataBeforeDrop.value.options = result;
  } catch (error) {
    console.error(error);
  }
};
const errorModula = ref("");
const fetchingData = async () => {
  if (!quarterDrop.value.selected || !form.regions) {
    errorModula.value = "Form belum lengkap, cek kabupaten/kota, triwulan, dan putaran";
    return;
  } else {
    errorModula.value = "";
  }
  try {
    const response = await axios.get(route("pdrb.show"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: quarterDrop.value.selected,
        regions: form.regions,
        description: descDrop.value.selected,
        dataBefore: dataBeforeDrop.value.selected,
      },
    });
    dataFetched.value.before = response.data.previous_data;
    dataFetched.value.current = response.data.current_data;
    modalValue.value = false;
  } catch (error) {
    console.error(error);
  }
};
const dataFetched = ref({ before: [], current: [] });
const idx = computed(() => {
  const out = {};
  for (const [key, df] of Object.entries(dataFetched.value)) {
    out[key] = {};
    for (const dd of df) {
      const sid = Number(dd.subsector_id);
      const q = String(dd.quarter);
      const adhb = dd?.["adhb"] ?? 0;
      const adhk = dd?.["adhk"] ?? 0;

      out[key][sid] ||= {};
      out[key][sid].adhb ||= {};
      out[key][sid].adhk ||= {};

      out[key][sid].adhb[q] = Number(adhb);
      out[key][sid].adhk[q] = Number(adhk);
    }
  }
  return out;
});
const tableModel = computed(() => {
  const period = ["before", "current"];
  const types = ["adhb", "adhk"];
  const quarters = ["1", "2", "3", "4"];
  const subsectorsBySector = {};
  const subsectorsByCategory = {};
  const allSubsectorIds = new Set();

  for (const s of page.props.subsectors) {
    if (s.id) allSubsectorIds.add(Number(s.id));

    if (s.sector_id && s.id) {
      (subsectorsBySector[s.sector_id] ||= []).push(Number(s.id));
    }

    const catId = s?.sector?.category_id;
    if (catId && s.id) {
      (subsectorsByCategory[catId] ||= []).push(Number(s.id));
    }
  }
  const sumIds = (p, ids, t) => {
    const q = quarters.map((qq) =>
      ids.reduce((acc, sid) => acc + (idx.value?.[p]?.[sid]?.[t]?.[qq] ?? 0), 0)
    );
    return { q, total: q.reduce((a, b) => a + b, 0) };
  };
  const result = {};
  for (const p of period) {
    result[p] = {};
  }
  for (const sid of allSubsectorIds) {
    const key = `sub-${sid}`;
    for (const p of period) {
      result[p][key] = {};
      for (const t of types) {
        const q = quarters.map((qq) => idx.value?.[p]?.[sid]?.[t]?.[qq] ?? 0);
        result[p][key][t] = { q, total: q.reduce((a, b) => a + b, 0) };
      }
    }
  }
  if (page.props.type == "Lapangan Usaha") {
    for (const [sectorId, ids] of Object.entries(subsectorsBySector)) {
      const key = `sec-${sectorId}`;
      for (const p of period) {
        result[p][key] ||= {};
        for (const t of types) result[p][key][t] = sumIds(p, ids, t);
      }
    }
  } else if (page.props.type == "Pengeluaran") {
    for (const [sectorId, ids] of Object.entries(subsectorsBySector)) {
      const key = `sec-${sectorId}`;
      for (const p of period) {
        result[p][key] ||= {};
        if (sectorId == 54) {
          for (const t of types) {
            const q = quarters.map((qq) => {
              const qResult =
                (idx.value?.[p]?.[ids[0]]?.[t]?.[qq] ?? 0) -
                (idx.value?.[p]?.[ids[1]]?.[t]?.[qq] ?? 0);
              return qResult;
            });
            result[p][key][t] = { q, total: q.reduce((a, b) => a + b, 0) };
          }
        } else for (const t of types) result[p][key][t] = sumIds(p, ids, t);
      }
    }
  }
  for (const [catId, ids] of Object.entries(subsectorsByCategory)) {
    const key = `cat-${catId}`;
    for (const p of period) {
      result[p][key] ||= {};
      for (const t of types) result[p][key][t] = sumIds(p, ids, t);
    }
  }
  const growth = { qtoq: {}, yony: {}, implisit: {} };
  const current = result.current;
  const previous = result.before;
  const thisQuarter = Number(form.quarter) - 1;
  for (const rowKey of Object.keys(current ?? {})) {
    const currAdhk = Number(current?.[rowKey]?.adhk?.q[thisQuarter] ?? 0);
    const currAdhb = Number(current?.[rowKey]?.adhb?.q[thisQuarter] ?? 0);
    //qtoq
    let dsQtoQ = 0;
    if (thisQuarter == 0) {
      dsQtoQ = Number(previous?.[rowKey]?.adhk?.q[3] ?? 0);
    } else dsQtoQ = Number(current?.[rowKey]?.adhk?.q[thisQuarter - 1]);
    growth.qtoq[rowKey] = dsQtoQ !== 0 ? (currAdhk / dsQtoQ) * 100 - 100 : 0;

    //yony
    let dsYonY = Number(previous?.[rowKey]?.adhk?.q[thisQuarter]);
    growth.yony[rowKey] = dsYonY !== 0 ? (currAdhk / dsYonY) * 100 - 100 : 0;
    // growth.yony[rowKey] = dsYonY;

    //implisit
    const dImplisit = currAdhk != 0 ? currAdhb / currAdhk : 0;
    let dsImplisit = 0;
    if (thisQuarter == 0) {
      const prevAdhb = Number(previous?.[rowKey]?.adhb?.q[3] ?? 0);
      const prevAdhk = Number(previous?.[rowKey]?.adhk?.q[3] ?? 0);
      dsImplisit = prevAdhk != 0 ? prevAdhb / prevAdhk : 0;
    } else {
      const prevAdhb = Number(current?.[rowKey]?.adhb?.q[thisQuarter - 1] ?? 0);
      const prevAdhk = Number(current?.[rowKey]?.adhk?.q[thisQuarter - 1] ?? 0);
      dsImplisit = prevAdhk != 0 ? prevAdhb / prevAdhk : 0;
    }
    growth.implisit[rowKey] = dsImplisit != 0 ? (dImplisit / dsImplisit) * 100 - 100 : 0;
  }
  return { result, growth };
});
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
