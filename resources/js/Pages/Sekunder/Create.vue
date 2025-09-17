<template>
  <Head title="Membuat Data Baru" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="container px-[7.5px] mr-auto ml-auto lg:max-w-[1140px]">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200">
        <div class="bg-info-fordone text-center p-[1.25rem]">
          <h2 class="text-3xl">Buat Data Baru</h2>
        </div>
      </div>
      <div class="form-group">
        <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
          <div class="flex items-center justify-between py-3 px-4 border-b card-header">
            <label class="text-xl">Deskripsi Umum</label>
          </div>
          <div class="p-5">
            <div class="mb-3 space-y-2">
              <label for="dinas"
                >Pilih Nama Dinas<span class="text-danger">*</span></label
              >
              <Multiselect
                :options="page.props.produsen"
                placeholder="-- Pilih Dinas --"
                :searchable="true"
              />
              <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
            </div>
            <div class="mb-3 space-y-2">
              <label for="label">Judul Data<span class="text-danger">*</span></label>
              <input
                type="text"
                class="input-fordone w-full"
                placeholder="Isikan judul data"
              />
              <div class="text-danger text-left" v-if="true" id="error-label"></div>
            </div>
          </div>
        </div>
        <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
          <div class="flex items-center justify-between py-3 px-4 border-b card-header">
            <label class="text-xl">Kelola Baris</label>
          </div>
          <div class="p-5">
            <div class="mb-3 space-y-2">
              <label for="rows">Pilih Baris<span class="text-danger">*</span></label>
              <Multiselect
                :options="page.props.rows"
                v-model="form.rows.selected"
                mode="tags"
                placeholder="-- Pilih Baris --"
                :searchable="true"
              />
              <div class="text-danger text-left" v-if="true" id="error-rows"></div>
            </div>
            <div v-if="false" class="mb-3 space-y-2">
              <div>
                <label for="rows">Urutan Baris</label>
                <small> (Abaikan Jika tidak ada perubahan urutan baris)</small>
              </div>
              <Multiselect
                :options="[
                  { label: 'Ada perubahan', value: '1' },
                  { label: 'Sudah sesuai', value: '2' },
                ]"
                :value="2"
                placeholder="-- Apakah ada perubahan urutan? --"
                :searchable="true"
              />
              <div class="text-danger text-left" v-if="true" id="error-rows"></div>
            </div>
            <div class="flex items-center space-x-2 justify-end">
              <div @click="buildValue" class="btn-info-fordone w-[130px]">
                <font-awesome-icon icon="fa fa-save" /> Buat Data
              </div>
              <div class="btn-success-fordone w-[110px]">
                <font-awesome-icon icon="fa fa-check" /> Simpan
              </div>
            </div>
          </div>
        </div>
      </div>
      <TablePreview v-if="previewStatus" :rows="rowBuildValue" />
    </div>
  </GeneralLayout>
</template>
<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import Multiselect from "@vueform/multiselect";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import TablePreview from "@/Components/TablePreview.vue";

const page = usePage();
const createModalStatus = ref(false);
const deleteModalStatus = ref(false);

const formError = ref([]);
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};

//forms
const form = useForm({
  datas: {
    produsen_id: null,
    label: null,
  },
  rows: {
    selected: [],
  },
});
const rowBuildValue = ref([]);
const previewStatus = ref(false);
const buildValue = () => {
  previewStatus.value = true;
  let rows = page.props.rows;
  rowBuildValue.value = rows.filter((x) => form.rows.selected.includes(x.value));
};
</script>

<style scoped>
.bg-info-fordone {
  background-color: #175676;
  color: whitesmoke;
}
.card-header {
  border-bottom-color: #175676;
  border-bottom-width: 3px;
}

.tabel-container {
  max-height: 500px;
  overflow-y: scroll;
  overflow-x: scroll;
}
label {
  font-weight: 700;
}
/* .text-danger {
  color: #a80606;
} */
</style>
