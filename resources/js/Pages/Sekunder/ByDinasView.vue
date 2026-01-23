<template>
  <Head title="View" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout> </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<style scoped></style>
