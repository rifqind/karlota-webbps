<template>
  <aside
    class="bg-white shadow-lg h-screen fixed top-0 left-0 w-[300px] z-[1200] overflow-y-auto"
  >
    <div class="px-6 py-4 flex items-center border-b h-[56px]">
      <img
        src="../../images/karlota-logo.ico"
        alt="Logo"
        class="w-10 h-10 rounded-full shadow-lg opacity-80"
      />
      <span class="ml-6 font-bold text-xl">Lembar Kerja</span>
    </div>
    <nav class="mt-4 px-3">
      <ul>
        <NavLinkSidebar
          :navIcon="'fa-solid fa-sheet-plastic'"
          :href="route('lk.dashboard')"
          :currentRoute="currentRoute == 'lk.dashboard'"
        >
          Dashboard
        </NavLinkSidebar>
        <NavLinkSidebar
          :nav-icon="'fa-brands fa-stack-exchange'"
          :href="route('komoditas.index')"
          :current-route="currentRoute == 'komoditas.index'"
          >Master Komoditas</NavLinkSidebar
        >
        <NavLinkSidebar
          :nav-icon="'fa-solid fa-money-bill-transfer'"
          :href="route('ih.dasar.index')"
          :current-route="currentRoute == 'ih.dasar.index'"
          >Indeks Harga</NavLinkSidebar
        >
        <NavLinkSidebar
          :navIcon="'fa-solid fa-k'"
          :href="route('dashboard')"
          :currentRoute="currentRoute == 'dashboard'"
        >
          Karlota
        </NavLinkSidebar>
      </ul>
      <br />
      <div class="text-center">
        <font-awesome-icon
          @click="pushSidebar"
          data-widget="pushmenu"
          icon="fa-solid fa-circle-chevron-left"
          class="edit-pen back-pen"
        />
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import NavLinkSidebar from "./NavLinkSidebar.vue";
import NavLinkParentSidebar from "./NavLinkParentSidebar.vue";
import { onMounted, ref } from "vue";

const page = usePage();
const currentRoute = page.props.route;
const menuOpenLapus = ref(false);
const menuOpenPeng = ref(false);
const menuOpenFenom = ref(false);
const menuOpenSummary = ref(false);
const menuOpenSekunder = ref(false);

const toggleMenuOpen = (x) => {
  if (x == "summary") menuOpenSummary.value = !menuOpenSummary.value;
  if (x == "lapus") menuOpenLapus.value = !menuOpenLapus.value;
  if (x == "peng") menuOpenPeng.value = !menuOpenPeng.value;
  if (x == "fenom") menuOpenFenom.value = !menuOpenFenom.value;
  if (x == "sekunder") menuOpenSekunder.value = !menuOpenSekunder.value;
};

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
const maintenanceStat = ref(false);
const setMaintenance = async () => {
  try {
    const token = await axios.get(route("token"));
    const response = await axios.post("/set-maintenance", {
      params: {
        _token: token.data,
      },
    });
    const { data } = await axios.get("/maintenance-status");
    if (data.maintenance == "1") {
      maintenanceStat.value = true;
    } else maintenanceStat.value = false;
  } catch (error) {
    console.error(error);
  }
};
onMounted(async () => {
  const { data } = await axios.get("/maintenance-status");
  if (data.maintenance == "1") {
    maintenanceStat.value = true;
  } else maintenanceStat.value = false;
});
const downloadGuide = () => {
  window.location.href = "/download-guide";
};
</script>

<style scoped>
.nav-link {
  padding: 0.5rem 1rem;
}
.maintenance {
  background-color: #a80606;
  color: whitesmoke;
}
</style>
