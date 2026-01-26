<template>
  <Head title="View" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <div class="flex flex-wrap gap-2">
      <button v-for="data in props.listOfYear" class="btn-sm btn btn-success-fordone">
        {{ data }}
      </button>
    </div>
    <template v-for="(node, index) in props.data" :key="index">
      <div class="bg-white shadow-md my-2 rounded-md border border-gray-200">
        <div class="py-4 px-6">
          <h3 class="font-bold text-2xl">
            {{ node.label }}, Tahun
            {{ node.tahun }}
          </h3>
          <h4 class="mt-2 flex text-2xl items-center">
            <span class="ml-auto text-xl text-right" id="">
              Terakhir diupdate :
              {{ new Date(node.status.updated_at).toLocaleString("id-ID") }}
            </span>
          </h4>
        </div>
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead table-info">Data</th>
              <th class="text-center align-middle not-fixed" v-for="tw in [1, 2, 3, 4]">
                Triwulan {{ tw }}
              </th>
              <th class="text-center align-middle not-fixed">Tahunan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in node.row">
              <td class="fixed-column">{{ r.label }}</td>
              <td v-for="(item, idx) in [1, 2, 3, 4]" :key="idx">
                <input
                  type="text"
                  class="w-full input-fordone"
                  :value="getData(r.id, item, node.data)"
                />
              </td>
              <td class="text-right text-md font-bold">{{}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
  listOfYear: { type: Array },
  data: { type: Array },
});
const getData = (r, tw, data) => {};
</script>

<style scoped></style>
