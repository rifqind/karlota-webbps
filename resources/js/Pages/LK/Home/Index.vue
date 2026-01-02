<template>
  <Head title="Lembar Kerja" />
  <LKLayout>
    <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
      <div class="flex items-center justify-between py-3 px-4 border-b card-header">
        <label class="text-xl">Entri Lembar Kerja</label>
      </div>
      <div class="p-5">
        <div class="mb-3 space-y-2">
          <label for="type">Pilih Tahun<span class="text-danger">*</span></label>
          <Multiselect
            v-model="yearDrop.value"
            :options="yearDrop.options"
            :searchable="true"
            mode="tags"
            placeholder="-- Pilih Tahun --"
          />
          <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
        </div>
        <div class="mb-3 space-y-2">
          <label for="type">Pilih Kategori<span class="text-danger">*</span></label>
          <Multiselect
            v-model="form.category"
            :options="props.category"
            :searchable="true"
            placeholder="-- Pilih Kategori --"
            @change="fetchSector"
          />
          <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
        </div>
        <div class="mb-3 space-y-2" v-if="form.category">
          <label for="year">Pilih Sektor</label>
          <Multiselect
            v-model="form.sector"
            :options="sectorDrop"
            @change="fetchSubsector"
            :searchable="true"
            placeholder="-- Pilih Sektor (bisa dikosongkan) --"
          />
          <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
        </div>
        <div class="mb-3 space-y-2" v-if="form.sector && form.category">
          <label for="year">Pilih Subsektor</label>
          <Multiselect
            v-model="form.subsector"
            :options="subsectorDrop"
            :searchable="true"
            placeholder="-- Pilih Subsektor (bisa dikosongkan) --"
          />
          <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
        </div>
        <div class="flex items-center space-x-2 justify-end">
          <div @click="submit" class="btn-info-fordone ml-auto w-[130px] text-center">
            <font-awesome-icon icon="fa-solid fa-magnifying-glass" />
            Cari Data
          </div>
        </div>
      </div>
    </div>
    <table v-if="loaddata" class="table text-xs w-full">
      <thead>
        <tr>
          <th rowspan="2">Periode</th>
          <th rowspan="2">Komoditas</th>
          <th rowspan="2">Produksi</th>
          <th rowspan="2">Indeks Harga</th>
          <th colspan="2">Harga</th>
          <th colspan="2">Output</th>
          <th rowspan="2">Rasio Biaya Antara</th>
          <th colspan="2">Biaya Antara</th>
          <th colspan="2">NTB</th>
        </tr>
        <tr>
          <th>ADHK</th>
          <th>ADHB</th>
          <th>ADHK</th>
          <th>ADHB</th>
          <th>ADHK</th>
          <th>ADHB</th>
          <th>ADHK</th>
          <th>ADHB</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="(y, iy) in datacontents" :key="iy">
          <template v-for="(t, it) in y" :key="`${iy}-${it}`">
            <tr v-for="(n, i) in props.test">
              <td v-if="i === 0" :rowspan="props.test.length">
                {{ iy }}
              </td>
              <td>{{ n.label }}</td>
              <td>
                <input
                  v-if="checkData(t, n.label, it)"
                  :id="`produksi-${iy}-${it}-${n.label}`"
                  class="w-full py-0 px-1 text-xs text-right"
                  @input="
                    (event) => {
                      debounceHandleInput(event, t, n.label, it);
                    }
                  "
                  :value="getData(t, n.label, it)"
                />
              </td>
              <td class="text-right">{{ getValue("indeks_harga", t, n.label, it) }}</td>
              <td class="text-right">{{ getValue("harga_konstan", t, n.label, it) }}</td>
              <td class="text-right">{{ getValue("harga_berlaku", t, n.label, it) }}</td>
              <td class="text-right">{{ getOutput("adhk", t, n.label, it) }}</td>
              <td class="text-right">{{ getOutput("adhb", t, n.label, it) }}</td>
            </tr>
          </template>
        </template>
      </tbody>
    </table>
  </LKLayout>
</template>

<script setup>
import { debounce } from "@/debounce";
import LKLayout from "@/Layouts/LKLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { ref, watch } from "vue";

const props = defineProps({
  category: Array,
  test: Array,
});
const datacontents = ref({});
const loaddata = ref(false);
const form = useForm({
  category: null,
  sector: null,
  subsector: null,
});
const sectorDrop = ref([]);
const fetchSector = async (cat) => {
  try {
    const { data } = await axios.get("/fetch-sector/" + cat);
    sectorDrop.value = data;
  } catch (error) {
    console.error("Error ketika fetch sector: ", error);
  }
};
const subsectorDrop = ref([]);
const fetchSubsector = async (sec) => {
  try {
    const { data } = await axios.get("/fetch-subsector/" + sec);
    subsectorDrop.value = data;
  } catch (error) {
    console.error("Error ketika fetch subsector: ", error);
  }
};
const yearDrop = ref({
  value: [new Date().getFullYear()],
  options: Array.from({ length: 11 }, (_, index) => new Date().getFullYear() - index).map(
    (y) => ({
      label: y.toString(),
      value: y.toString(),
    })
  ),
});
const handleInput = (event, arr, label, t) => {
  let value = event.target.value;
  if (value == "-") value = String(0);
  value = String(value).replaceAll(".", "").replace(",", ".");
  const num = Number(value) || 0;
  const arrResult = arr.find((item) => item.label === label && item.triwulan == t);
  if (!arrResult) return;
  const tahun = arrResult.tahun;
  const homes = datacontents.value?.[tahun]?.[t];
  if (!Array.isArray(homes)) return;
  const target = homes.find((item) => item.label == label);
  if (!target) return;
  target.produksi = num;
};
const debounceHandleInput = debounce((event, arr, label, t) => {
  handleInput(event, arr, label, t);
}, 300);
const getData = (arr, label, t) => {
  const arrResult = arr.find((item) => item.label === label && item.triwulan == t);
  if (!arrResult) return;
  const tahun = arrResult.tahun;
  const homes = datacontents.value?.[tahun]?.[t];
  if (!Array.isArray(homes)) return;
  const target = homes.find((item) => item.label == label);
  if (!target) return;
  let result = target.produksi ? formatNumberGerman(target.produksi, 2, 2) : null;
  return result;
};
const checkData = (arr, label, t) => {
  const arrResult = arr.find((item) => item.label === label && item.triwulan == t);
  return arrResult ? true : false;
};
const getValue = (type, arr, label, t) => {
  const arrResult = arr.find((item) => item.label === label && item.triwulan == t);
  let result = "-";
  switch (type) {
    case "indeks_harga":
      result = arrResult ? arrResult.indeks_harga : "-";
      break;
    case "harga_konstan":
      result = arrResult ? arrResult.harga_konstan : "-";
      break;
    case "harga_berlaku":
      result = arrResult ? arrResult.harga_konstan * (arrResult.indeks_harga / 100) : "-";
      break;
    default:
      break;
  }
  let formatted = formatNumberGerman(result, 2, 2);
  return formatted;
};
const getOutput = (type, arr, label, t) => {
  const arrResult = arr.find((item) => item.label === label && item.triwulan == t);
  let result = "-";
  switch (type) {
    case "adhk":
      result = arrResult ? arrResult.produksi * arrResult.harga_konstan : "-";
      break;
    case "adhb":
      result = arrResult
        ? arrResult.produksi * arrResult.harga_konstan * (arrResult.indeks_harga / 100)
        : "-";
      break;
    default:
      break;
  }
  let formatted = formatNumberGerman(result, 2, 2);
  return formatted;
};
const formatNumberGerman = (num, min = 2, max = 5) => {
  let result = new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
  let formatted = num == 0 || num == "-" ? "-" : result;
  return formatted;
};
//submit
const submit = async () => {
  loaddata.value = false;
  const { data } = await axios.get(route("lk.getData"), {
    params: {
      category: form.category,
      sector: form.sector,
      subsector: form.subsector,
      years: yearDrop.value.value,
    },
  });
  datacontents.value = data;
  loaddata.value = true;
};
</script>

<style scoped></style>
