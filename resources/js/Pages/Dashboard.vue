<template>
  <Head title="Dashboard" />
  <GeneralLayout>
    <div class="font-bold text-xl">PERIODE PUTARAN AKTIF (3 TERAKHIR)</div>
    <div class="flex flex-wrap mt-2 items-center">
      <div
        class="bg-white text-lg shadow-md mb-2 rounded-md border border-gray-200 w-full border-l-indigo-500 border-l-4 border-r-orange-500 border-r-4"
      >
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col h-full border-r md:pr-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">LAPANGAN USAHA</h3>
                <font-awesome-icon
                  icon="fa-solid fa-industry"
                  class="fa-xl text-indigo-500"
                />
              </div>
              <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                <li v-for="(node, index) in props.lapus" :key="'lapus-' + index">
                  {{ node.description }}
                </li>
              </ul>
            </div>
            <div class="flex flex-col h-full md:pl-4">
              <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-base">PENGELUARAN</h3>
                <font-awesome-icon
                  icon="fa-solid fa-coins"
                  class="fa-xl text-orange-500"
                />
              </div>
              <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                <li v-for="(node, index) in props.peng" :key="'peng-' + index">
                  {{ node.description }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="flex justify-end items-center">
        <button @click="buildSummaries" class="btn btn-success-fordone">Summaries</button>
      </div>
      <div v-for="item in sumTime" :key="item.id">
        {{ item }}
      </div>

      <!-- <div
        class="bg-white shadow-md mb-2 rounded-md border border-gray-200 w-full md:w-full lg:w-6/12 xl:w-6/12 border-l-orange-500 border-l-4"
      >
        <div class="p-4"></div>
      </div> -->
    </div>
  </GeneralLayout>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
const props = defineProps({
  lapus: {
    type: Array,
    required: false,
  },
  peng: {
    type: Array,
    required: false,
  },
  sumTime: {
    type: Array,
    required: false,
  },
});
const form = useForm({
  _token: null,
  lapus_id: null,
  peng_id: null,
});
const buildSummaries = async () => {
  // let setupArray = ["category", "sector", "subsector", "total"];
  let setupArray = ["total"];
  setupArray.forEach(async (element) => {
    try {
      const response = await axios.get(route("home.index"), {
        params: {
          setup: element,
        },
      });
      form.lapus_id = response.data.lapus_period;
      form.peng_id = response.data.peng_period;
    } catch (error) {
      console.error(error);
    }
  });
  const _token = await axios.get(route("token"));
  form._token = _token.data;
  form.post(route("home.update-time"));
};
</script>
<style scoped></style>
