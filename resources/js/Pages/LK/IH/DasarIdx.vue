<template>
  <Head title="Harga Dasar" />
  <SpinnerBorder v-if="triggerSpinner" />
  <LKLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Indeks Harga Dasar
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button
          @click="form.reset()"
          class="btn-success-fordone mr-2 mb-2 lg:mb-0"
          title="Download"
        >
          <font-awesome-icon icon="fa-solid fa-circle-down" />
        </button>
        <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
          <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Indeks Harga Dasar
        </button>
      </div>
    </div>
  </LKLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import LKLayout from "@/Layouts/LKLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";

const createModalStatus = ref(false);
const updateModalStatus = ref(false);
const deleteModalStatus = ref(false);

const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
</script>

<style scoped></style>
