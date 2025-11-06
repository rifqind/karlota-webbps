<template>
  <Head title="Harga Dasar" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FlashFetch from "@/Components/FlashFetch.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";

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
