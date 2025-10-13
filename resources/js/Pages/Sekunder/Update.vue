<template>
  <Head title="Update Data Info" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="container px-[7.5px] mr-auto ml-auto lg:max-w-[1140px]">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200">
        <div class="bg-info-fordone text-center p-[1.25rem]">
          <h2 class="text-3xl">Update Data {{ page.props.sekunder.label }}</h2>
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
                placeholder="-- Pilih Baris --"
                :searchable="true"
              />
              <div
                class="text-danger text-left"
                v-if="page.props.errors['rows.selected']"
                id="error-rows"
              >
                {{ page.props.errors["rows.selected"] }}
              </div>
            </div>
            <div
              class="text-danger"
              v-if="formError.length > 0"
              v-for="node in formError"
            >
              {{ node }}
            </div>
            <div class="flex items-center space-x-2 justify-end">
              <div @click="buildValue" class="btn-info-fordone w-[150px]">
                <font-awesome-icon icon="fa fa-save" /> Update Data
              </div>
              <div
                v-if="previewStatus"
                @click="
                  () => {
                    createModalStatus = true;
                    form.force = true;
                  }
                "
                class="btn-warning-fordone w-[160px]"
              >
                <font-awesome-icon icon="fa fa-check" /> Force Simpan
              </div>
              <div
                v-if="previewStatus"
                @click="
                  () => {
                    createModalStatus = true;
                    form.force = false;
                  }
                "
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
    <ModalBs
      :-modal-status="createModalStatus"
      @close="createModalStatus = false"
      :title="'Konfirmasi'"
    >
      <template #modalBody>
        <div class="form-group">
          <div>
            <label>Apakah Anda yakin dengan perubahan yang dibuat?</label>
          </div>
        </div>
      </template>
      <template #modalFunction>
        <button type="button" class="btn-success-fordone btn-sm" @click.prevent="submit">
          Yakin
        </button>
      </template>
    </ModalBs>
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
import ModalBs from "@/Components/ModalBs.vue";

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
    id: page.props.sekunder.id,
    tahun: page.props.tahun,
    produsen_id: page.props.sekunder.produsen_id,
    label: page.props.sekunder.label,
  },
  rows: {
    selected: page.props.sekunder_row,
  },
  order: page.props.order,
  force: false,
});
const rowBuildValue = ref([]);
const produsenBuildValue = ref(null);
const previewStatus = ref(false);
const buildValue = () => {
  let rows = page.props.rows;
  let produsen = page.props.produsen;
  produsenBuildValue.value = produsen.find((x) => x.value == form.datas.produsen_id);
  rowBuildValue.value = rows.filter((x) => form.rows.selected.includes(x.value));
  previewStatus.value = true;
};
const tableRef = ref(null);
const submit = async () => {
  const token = await axios.get(route("token"));
  form._token = token.data;
  form.order = tableRef.value.datas.map((item) => item.value);
  try {
    form.post("/master/sekunder/update", {
      onSuccess: (response) => {
        showNotification(response.props.notification);
        if (response.props.notification[0].type == "success") {
          form
            .defaults({
              _token: null,
              datas: {
                id: page.props.sekunder.id,
                tahun: page.props.tahun,
                produsen_id: page.props.sekunder.produsen_id,
                label: page.props.sekunder.label,
              },
              rows: {
                selected: page.props.sekunder_row,
              },
              order: page.props.order,
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
      onFinish: () => {
        createModalStatus.value = false;
      },
    });
  } catch (error) {
    console.error(error);
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
