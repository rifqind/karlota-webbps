<template>
  <Head title="Membuat Data Baru" />
  <SpinnerBorder v-if="triggerSpinner" />
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
                v-model="form.datas.produsen_id"
                :options="page.props.produsen"
                placeholder="-- Pilih Dinas --"
                :searchable="true"
              />
              <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
            </div>
            <div class="mb-3 space-y-2">
              <label for="label">Judul Data<span class="text-danger">*</span></label>
              <input
                v-model="form.datas.label"
                type="text"
                class="input-fordone w-full"
                placeholder="Isikan judul data"
              />
              <div class="text-danger text-left" v-if="true" id="error-label"></div>
            </div>
            <div class="mb-3 space-y-2">
              <label for="year">Tahun<span class="text-danger">*</span></label>
              <Multiselect
                v-model="form.datas.tahun"
                :options="yearDrop"
                placeholder="-- Pilih Tahun --"
                mode="tags"
                :searchable="true"
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
                @paste="handlePaste"
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
            <div
              class="text-danger"
              v-if="formError.length > 0"
              v-for="node in formError"
            >
              {{ node }}
            </div>
            <div class="flex items-center space-x-2 justify-end">
              <div @click="buildValue" class="btn-info-fordone w-[130px]">
                <font-awesome-icon icon="fa fa-save" /> Buat Data
              </div>
              <div
                v-if="previewStatus"
                @click="submit"
                class="btn-success-fordone w-[110px]"
              >
                <font-awesome-icon icon="fa fa-check" /> Simpan
              </div>
            </div>
          </div>
        </div>
      </div>
      <TablePreview
        ref="tableRef"
        @close="previewStatus = false"
        v-if="previewStatus"
        :rows="rowBuildValue"
        :label="form.datas.label"
        :produsen="produsenBuildValue"
      />
      <Link :href="route('sekunder.index')" class="btn btn-light-fordone border"
        ><font-awesome-icon icon="fas fa-chevron-left" />
        Kembali
      </Link>
    </div>
  </GeneralLayout>
</template>
<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import Multiselect from "@vueform/multiselect";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import TablePreview from "@/Components/TablePreview.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { parseTSVWithQuotes } from "@/handleCopy";

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
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 6 }, (_, index) => currentYear + 1 - index);
const yearDrop = ref(null);
yearDrop.value = years.map((year) => ({
  label: year.toString(),
  value: year.toString(),
}));
const form = useForm({
  _token: null,
  datas: {
    tahun: [],
    produsen_id: null,
    label: null,
  },
  rows: {
    selected: [],
  },
  order: [],
});
const rowBuildValue = ref([]);
const produsenBuildValue = ref(null);
const previewStatus = ref(false);
const buildValue = () => {
  let rows = page.props.rows;
  let produsen = page.props.produsen;
  const rowsMap = new Map(rows.map((x) => [x.value, x]));
  produsenBuildValue.value = produsen.find((x) => x.value == form.datas.produsen_id);
  rowBuildValue.value = form.rows.selected.map((id) => rowsMap.get(id)).filter(Boolean);
  previewStatus.value = true;
};
const tableRef = ref(null);
const submit = async () => {
  const token = await axios.get(route("token"));
  form._token = token.data;
  form.order = tableRef.value.datas.map((item) => item.value);
  try {
    form.post(route("sekunder.store"), {
      onSuccess: (response) => {
        showNotification(response.props.notification);
        if (response.props.notification[0].type == "success") {
          form
            .defaults({
              _token: null,
              datas: {
                tahun: [],
                produsen_id: null,
                label: null,
              },
              rows: {
                selected: [],
              },
              order: [],
            })
            .reset();
          formError.value = [];
          previewStatus.value = false;
        } else {
          response.props.notification.forEach((element) => {
            formError.value.push(element.error);
          });
        }
      },
    });
  } catch (error) {
    console.error(error);
  }
};

//paste
const handlePaste = (event) => {
  const items = event.clipboardData.items;
  for (let i = 0; i < items.length; i++) {
    if (items[i].type == "text/plain") {
      items[i].getAsString((text) => {
        const parsedRows = parseTSVWithQuotes(text);
        let data = [];
        parsedRows.forEach((row) => {
          let theIndex = page.props.rows.find((item) => item.label == row[0].trim());
          if (theIndex) {
            data.push(theIndex.value);
          }
        });
        form.rows.selected = data;
      });
    }
  }
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
