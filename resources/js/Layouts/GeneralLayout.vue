<template>
  <div>
    <!-- Sidebar -->
    <Transition name="slide">
      <Sidebar
        v-if="isSidebarVisible"
        @update:updateSidebarValue="showSidebar"
        :isSidebarVisible="isSidebarVisible"
        class="transition-transform duration-300"
      />
    </Transition>
    <!-- Content Wrapper -->
    <div class="flex-grow" :class="mobileChecked">
      <!-- Navbar -->
      <Navbar
        :isSidebarVisible="isSidebarVisible"
        @update:triggerSidebar="triggerSidebar"
      />
      <!-- Main Content -->
      <main class="bg-gray-100 p-4">
        <slot />
      </main>
      <Footer />
    </div>
  </div>
</template>

<script setup lang="ts">
import Footer from "@/Components/Footer.vue";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
import { computed, onMounted, onUnmounted, ref } from "vue";
const props = defineProps({
  entri: {
    type: Boolean,
    default: true,
    required: false,
  },
});
const isSidebarVisible = ref(true);
isSidebarVisible.value = props.entri;
const triggerSidebar = (value) => {
  isSidebarVisible.value = value;
};
const isMobile = ref(false);
const isTablet = ref(false);
const updateDeviceType = () => {
  isMobile.value = window.matchMedia("(max-width: 767px)").matches;
  isTablet.value = window.matchMedia(
    "(min-width: 768px) and (max-width: 1024px)"
  ).matches;
};
onMounted(() => {
  updateDeviceType();
  if (isMobile.value) isSidebarVisible.value = false;
  window.addEventListener("resize", updateDeviceType);
});

onUnmounted(() => {
  window.removeEventListener("resize", updateDeviceType);
});
const mobileChecked = computed(() => {
  if (isMobile.value) {
    return "ml-0";
  } else {
    return isSidebarVisible.value ? "ml-[300px]" : "ml-0";
  }
});
const showSidebar = (value) => {
  isSidebarVisible.value = value;
};
</script>

<style scoped>
main {
  min-height: calc(100vh - 56px - 56px);
}
</style>
