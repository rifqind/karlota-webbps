<template>
  <Head title="Diskrepansi" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout :entri="mountThis">
    <FlashFetch :notifications="notifications" />
    <FloatScrollDown />
    <div class="container mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="p-5">
          <div class="flex flex-items items-center gap-5">
            <div class="w-full flex gap-5">
              <div class="w-1/5 space-y-2">
                <label for="type">Pilih PDRB<span class="text-danger">*</span></label>
                <Multiselect v-model="form.type" :placeholder="form.type" disabled />
                <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
              </div>
              <div class="w-1/5 space-y-2">
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
              <div class="w-1/5 space-y-2">
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
              <div class="w-1/5 space-y-2">
                <label for="year"
                  >Pilih Periode Putaran<span class="text-danger">*</span></label
                >
                <Multiselect
                  v-model="form.description"
                  :options="descDrop"
                  :searchable="true"
                  placeholder="-- Pilih Periode --"
                  @change="fetchYearBefore"
                />
                <div class="text-danger text-left" v-if="true" id="error-dinas">
                  {{ formError.description }}
                </div>
              </div>
              <div class="w-1/5 space-y-2">
                <label for="year">Pilih Data Tahun Sebelumnya</label>
                <Multiselect
                  v-model="form.dataBefore"
                  :options="dataBeforeDrop"
                  :searchable="true"
                  placeholder="-- Pilih Periode Sebelumnya --"
                />
                <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
              </div>
            </div>
          </div>
          <div>
            <button>Hehe</button>
          </div>
        </div>
      </div>
    </div>
  </GeneralLayout>
</template>
<script setup>
import { triggerSpinner } from "@/axiosSetup";
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
});
const formError = ref({
  year: null,
  quarter: null,
  description: null,
});
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
const mountThis = ref(false);
const yearDrop = ref([]);
const quarterDrop = ref([]);
const descDrop = ref([]);
const dataBeforeDrop = ref([]);
onMounted(() => {
  fetchYear();
});
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
</script>
<style scoped></style>
