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
      <span class="ml-6 font-bold text-xl">Karlota</span>
    </div>
    <nav class="mt-4 px-3">
      <ul>
        <NavLinkSidebar
          :navIcon="'fa-solid fa-chart-line'"
          :href="route('dashboard')"
          :currentRoute="currentRoute == 'dashboard'"
        >
          Dashboard
        </NavLinkSidebar>
        <NavLinkParentSidebar
          :nav-icon="'fa-solid fa-chart-line'"
          :menu-open="
            menuOpenSummary || currentRoute == 'spv.lapus' || currentRoute == 'spv.peng'
          "
          :toggle-menu-open="toggleMenuOpen"
          :params="'summary'"
          ><template #label>Lihat PDRB</template>
          <template #content>
            <NavLinkSidebar
              :nav-icon="'fa-solid fa-chart-pie'"
              :href="route('spv.lapus')"
              :current-route="currentRoute == 'spv.lapus'"
              >Lapangan Usaha</NavLinkSidebar
            >
            <NavLinkSidebar
              :nav-icon="'fa-solid fa-money-bill-trend-up'"
              :href="route('spv.peng')"
              :current-route="currentRoute == 'spv.peng'"
              >Pengeluaran</NavLinkSidebar
            >
          </template>
        </NavLinkParentSidebar>
        <template
          v-if="
            page.props.auth.user.role == 'admin' || page.props.auth.user.role == 'user'
          "
        >
          <NavLinkParentSidebar
            :navIcon="'fa-solid fa-chart-pie'"
            :menuOpen="
              menuOpenLapus ||
              currentRoute == 'lapus.entri' ||
              currentRoute == 'lapus.adjustment' ||
              currentRoute == 'lapus.hasil' ||
              currentRoute == 'lapus.diskrepansi'
            "
            :toggleMenuOpen="toggleMenuOpen"
            :params="'lapus'"
          >
            <template #label> Lapangan Usaha</template>
            <template #content>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('lapus.entri')"
                :currentRoute="currentRoute == 'lapus.entri'"
              >
                Entri PDRB
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('lapus.adjustment')"
                :currentRoute="currentRoute == 'lapus.adjustment'"
              >
                Adjustment
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('lapus.hasil')"
                :currentRoute="currentRoute == 'lapus.hasil'"
              >
                Hasil
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('lapus.diskrepansi')"
                :currentRoute="currentRoute == 'lapus.diskrepansi'"
              >
                PDRB se-Provinsi
              </NavLinkSidebar>
            </template>
          </NavLinkParentSidebar>
          <NavLinkParentSidebar
            :navIcon="'fa-solid fa-money-bill-trend-up'"
            :menuOpen="
              menuOpenPeng ||
              currentRoute == 'peng.entri' ||
              currentRoute == 'peng.adjustment' ||
              currentRoute == 'peng.hasil' ||
              currentRoute == 'peng.diskrepansi'
            "
            :toggleMenuOpen="toggleMenuOpen"
            :params="'peng'"
          >
            <template #label> Pengeluaran </template>
            <template #content>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('peng.entri')"
                :currentRoute="currentRoute == 'peng.entri'"
              >
                Entri PDRB
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('peng.adjustment')"
                :currentRoute="currentRoute == 'peng.adjustment'"
              >
                Adjustment
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('peng.hasil')"
                :currentRoute="currentRoute == 'peng.hasil'"
              >
                Hasil
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('peng.diskrepansi')"
                :currentRoute="currentRoute == 'peng.diskrepansi'"
              >
                PDRB se-Provinsi
              </NavLinkSidebar>
            </template>
          </NavLinkParentSidebar>
          <NavLinkSidebar
            v-if="page.props.auth.user.role == 'admin'"
            :navIcon="'fa-solid fa-list-ol'"
            :href="route('pdrb.monitoring')"
            :currentRoute="currentRoute == 'pdrb.monitoring'"
          >
            Monitoring PDRB
          </NavLinkSidebar>
          <NavLinkParentSidebar
            :navIcon="'fa-solid fa-sun-plant-wilt'"
            :menuOpen="
              menuOpenFenom ||
              currentRoute == 'lapus.entri-fenomena' ||
              currentRoute == 'peng.entri-fenomena' ||
              currentRoute == 'fenomena.index' ||
              currentRoute == 'fenomena.monitoring'
            "
            :toggleMenuOpen="toggleMenuOpen"
            :params="'fenom'"
          >
            <template #label> Fenomena </template>
            <template #content>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('lapus.entri-fenomena')"
                :currentRoute="currentRoute == 'lapus.entri-fenomena'"
              >
                Lapangan Usaha
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('peng.entri-fenomena')"
                :currentRoute="currentRoute == 'peng.entri-fenomena'"
              >
                Pengeluaran
              </NavLinkSidebar>
              <NavLinkSidebar
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('fenomena.index')"
                :currentRoute="currentRoute == 'fenomena.index'"
              >
                Lihat Fenomena
              </NavLinkSidebar>
              <NavLinkSidebar
                v-if="page.props.auth.user.role == 'admin'"
                :navIcon="'fa-solid fa-list-ol'"
                :href="route('fenomena.monitoring')"
                :currentRoute="currentRoute == 'fenomena.monitoring'"
              >
                Monitoring Fenomena
              </NavLinkSidebar>
            </template>
          </NavLinkParentSidebar>
          <NavLinkParentSidebar
            :nav-icon="'fa-solid fa-server'"
            :toggle-menu-open="toggleMenuOpen"
            :params="'sekunder'"
            :menu-open="
              menuOpenSekunder ||
              currentRoute == 'produsen.index' ||
              currentRoute == 'sekunder.index' ||
              currentRoute == 'master.rows.index' ||
              currentRoute == 'master.sekunder.index' ||
              currentRoute == 'sekunder.create' ||
              currentRoute == 'sekunder.entri'
            "
          >
            <template #label>Data Sekunder</template>
            <template #content>
              <NavLinkSidebar
                :nav-icon="'fa-solid fa-building-circle-check'"
                :href="route('produsen.index')"
                :current-route="currentRoute == 'produsen.index'"
              >
                Daftar Dinas
              </NavLinkSidebar>
              <NavLinkSidebar
                :nav-icon="'fa-solid fa-magnifying-glass-chart'"
                :href="route('sekunder.index')"
                :current-route="
                  currentRoute == 'sekunder.index' ||
                  currentRoute == 'sekunder.create' ||
                  currentRoute == 'sekunder.entri'
                "
              >
                Daftar Data
              </NavLinkSidebar>
              <NavLinkSidebar
                :nav-icon="'fa-solid fa-diagram-successor'"
                :href="route('master.rows.index')"
                :current-route="currentRoute == 'master.rows.index'"
              >
                Master Rows
              </NavLinkSidebar>
              <NavLinkSidebar
                :nav-icon="'fa-solid fa-meteor'"
                :href="route('master.sekunder.index')"
                :current-route="currentRoute == 'master.sekunder.index'"
              >
                Master Data
              </NavLinkSidebar>
            </template>
          </NavLinkParentSidebar>
          <template v-if="page.props.auth.user.role == 'admin'">
            <NavLinkSidebar
              :navIcon="'fas fa-table'"
              :href="route('period.index')"
              :currentRoute="currentRoute == 'period.index'"
            >
              Kelola Jadwal</NavLinkSidebar
            >
            <NavLinkSidebar
              :navIcon="'fa-solid fa-users'"
              :href="route('user.index')"
              :currentRoute="currentRoute == 'user.index'"
            >
              Kelola Pengguna</NavLinkSidebar
            >
          </template>
          <NavLinkSidebar
            :navIcon="'fa-solid fa-users'"
            :href="route('user.edit', { id: page.props.auth.user.id })"
            :currentRoute="currentRoute == 'user.edit'"
          >
            Edit Profil Akun</NavLinkSidebar
          >
          <NavLinkSidebar
            :navIcon="'fa-solid fa-file-circle-question'"
            :href="route('user.question')"
            :currentRoute="currentRoute == 'user.question'"
          >
            Permasalahan Aplikasi</NavLinkSidebar
          >
          <NavLinkSidebar
            @click="downloadGuide"
            :navIcon="'fa-solid fa-book'"
            :currentRoute="currentRoute == 'download-guide'"
          >
            Panduan Aplikasi</NavLinkSidebar
          >
          <li
            v-if="page.props.auth.user.name == 'niu'"
            class="nav-item mb-1"
            @click="setMaintenance"
          >
            <a
              href="#"
              class="nav-link w-full relative flex items-center rounded"
              :class="{ maintenance: maintenanceStat }"
            >
              <font-awesome-icon class="nav-icon w-1/12" icon="fa-solid fa-microchip" />
              <p class="ml-2">Maintenance Mode</p>
            </a>
          </li>
        </template>
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
import { current } from "tailwindcss/colors";

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
    if (data.maintenance) {
      maintenanceStat.value = true;
    } else maintenanceStat.value = false;
  } catch (error) {
    console.error(error);
  }
};
onMounted(async () => {
  const { data } = await axios.get("/maintenance-status");
  if (data.maintenance) {
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
