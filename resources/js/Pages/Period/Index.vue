<template>
  <Head title="Daftar Putaran" />
  <GeneralLayout>
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Jadwal Rekon PDRB / Putaran
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button class="btn-success-fordone mr-2 mb-2 lg:mb-0" title="Download">
          <font-awesome-icon icon="fa-solid fa-circle-down" />
        </button>
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Baris Baru
        </button>
      </div>
    </div>
    <FlashMessage
      :toggleFlash="toggleFlash"
      @close="flashHandle"
      :flashObject="flashObject"
    />
  </GeneralLayout>
</template>

<script setup lang="ts">
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const page = usePage();
const createModalStatus = ref(false);
const toggleFlash = ref(false);
const flashObject = ref(page.props.flash);
watch(
  () => page.props.flash,
  (value) => {
    flashObject.value = value;
  }
);
const flashHandle = () => {
  toggleFlash.value = false;
  flashObject.value = {
    message: null,
    error: null,
  };
};
</script>

<style scoped></style>
