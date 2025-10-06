<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
  <div
    class="animated-bg flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
  >
    <div class="flex flex-items items-center mb-2">
      <ApplicationLogo class="h-20 w-20 fill-current text-gray-500 mr-3" />
      <span class="text-4xl font-bold dark:text-white">KARLOTA</span>
    </div>
    <div class="">
      <span class="text-lg dark:text-white">PENINGKATAN KUALITAS REKONSILIASI</span>
    </div>
    <div class="">
      <span class="text-lg dark:text-white">PDRB KABUPATEN/KOTA SE-SULAWESI UTARA</span>
    </div>

    <div
      class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
    >
      <slot />
    </div>
  </div>
</template>
<style scope></style>
