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
    <table class="table text-xs w-full">
      <thead>
        <tr>
          <th rowspan="2">Periode</th>
          <th rowspan="2">No</th>
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
          <th>ADHB</th>
          <th>ADHK</th>
          <th>ADHB</th>
          <th>ADHK</th>
          <th>ADHB</th>
          <th>ADHK</th>
          <th>ADHB</th>
          <th>ADHK</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="item in lengthSpan" :key="item">
          <tr>
            <td v-if="item == 0" :rowspan="props.test.length"></td>
          </tr>
        </template>
      </tbody>
    </table>
  </LKLayout>
</template>

<script setup>
import LKLayout from "@/Layouts/LKLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { ref } from "vue";

const props = defineProps({
  category: Array,
  test: Array,
});
const lengthSpan = ref(Array.from({ length: props.test.length }, (_, index) => index));
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
//submit
const submit = async () => {
  form.get(route("lk.getData"), {
    onSuccess: (response) => {
      console.log(response);
    },
  });
};
</script>

<style scoped></style>
