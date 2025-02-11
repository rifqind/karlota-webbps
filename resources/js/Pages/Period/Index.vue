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
    <div class="table-responsive-mobile overflow-x-auto">
      <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
        <thead>
          <tr class="bg-info-fordone">
            <th class="first-column">No.</th>
            <th class="text-center th-order" @click="clickToOrder('username')">
              PDRB
            </th>
            <th class="text-center th-order tabel-width-15" @click="clickToOrder('name')">
              Tahun
            </th>
            <th
              class="text-center th-order tabel-width-20"
              @click="clickToOrder('dinas.nama')"
            >
              Triwulan
            </th>
            <th
              class="text-center th-order tabel-width-20"
              @click="clickToOrder('w.label')"
            >
              Periode/Putaran
            </th>
            <th class="text-center th-order" @click="clickToOrder('noHp')">Tanggal Mulai</th>
            <th class="text-center th-order" @click="clickToOrder('role')">Tanggal Selesai</th>
            <th class="text-center th-order deleted tabel-width-8">Edit</th>
            <th class="text-center th-order deleted">Hapus</th>
          </tr>
          <tr class="">
            <td class="search-header"></td>
            <td class="search-header">
              <input
                v-model.trim="searchUsername"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input v-model.trim="searchNama" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchInstansi"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchWilayah"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header">
              <input
                v-model.trim="searchNoHp"
                type="text"
                class="input-fordone w-full"
                placeholder="cari dengan 08..."
              />
            </td>
            <td class="search-header">
              <input v-model.trim="searchRole" type="text" class="input-fordone w-full" />
            </td>
            <td class="search-header deleted"></td>
            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
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
const clickToOrder = () => {
  
}
</script>

<style scoped></style>
