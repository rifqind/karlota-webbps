<template>
  <aside class="bg-white shadow-lg min-h-screen fixed top-0 left-0 w-[300px] z-[1200]">
    <div class="px-6 py-4 flex items-center border-b h-[56px]">
      <img
        src="../../images/karlota-logo.png"
        alt="Logo"
        class="w-10 h-10 rounded-full shadow-lg opacity-80"
      />
      <span class="ml-6 font-bold text-xl">Karlota</span>
    </div>
    <nav class="mt-4 px-3">
      <ul>
        <NavLinkSidebar
          :navIcon="'fa-solid fa-users'"
          :href="route('dashboard')"
          :currentRoute="currentRoute == 'dashboard'"
        >
          Kelola Pengguna</NavLinkSidebar
        >
        <NavLinkSidebar
          :navIcon="'fas fa-table'"
          :href="route('period.index')"
          :currentRoute="currentRoute == 'period.index'"
        >
          Kelola Jadwal</NavLinkSidebar
        >
        <NavLinkSidebar
          :navIcon="'fas fa-user'"
          :href="route('dashboard')"
          :currentRoute="currentRoute == 'dashboard'"
        >
          Edit Profile</NavLinkSidebar
        >
      </ul>
      <br />
      <div class="text-center">
        <!-- <font-awesome-icon
          @click="pushSidebar"
          data-widget="pushmenu"
          icon="fa-solid fa-circle-chevron-left"
          class="edit-pen back-pen"
        /> -->
      </div>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import { usePage } from "@inertiajs/vue3";
import NavLinkSidebar from "./NavLinkSidebar.vue";

const page = usePage();
const currentRoute = page.props.route;
const emit = defineEmits(["update:updateSidebarValue"]);
const props = defineProps({
  isSidebarVisible: {
    type: Boolean,
    default: true,
    required: false,
  },
});
const pushSidebar = () => {
  emit("update:updateSidebarValue", !props.isSidebarVisible);
};
</script>

<style scoped></style>
