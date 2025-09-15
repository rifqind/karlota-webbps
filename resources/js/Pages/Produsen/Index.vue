<template>

    <Head title="Daftar Dinas" />
    <SpinnerBorder v-if="triggerSpinner" />
    <GeneralLayout>
        <div class="mb-2 flex flex-wrap items-center justify-between">
            <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
                Daftar Dinas
            </div>
            <div class="flex items-center w-full md:w-full lg:w-auto">
                <button class="btn-success-fordone mr-2 mb-2 lg:mb-0" title="Download">
                    <font-awesome-icon icon="fa-solid fa-circle-down" />
                </button>
                <button @click="createModalStatus = true" class="btn-info-fordone mb-2 lg:mb-0">
                    <font-awesome-icon icon="fa-solid fa-plus" /> Tambah Dinas
                </button>
            </div>
        </div>
        <div class="table-responsive-mobile overflow-x-auto">
            <table class="table border-2 mb-2 w-full" ref="tabelUser" id="tabel-user">
                <thead>
                    <tr class="bg-info-fordone">
                        <th class="first-column tabel-width-5">No.</th>
                        <th class="text-center th-order" @click="clickToOrder('nama')">Nama Dinas</th>
                        <th class="text-center th-order" @click="clickToOrder('region_name')">
                            Wilayah Kerja
                        </th>
                        <th class="text-center th-order tabel-width-8 deleted">Edit/Hapus</th>
                    </tr>
                    <tr>
                        <td class="search-header"></td>
                        <td class="search-header">
                            <input v-model.trim="searchNama" type="text" class="input-fordone w-full" />
                        </td>
                        <td class="search-header">
                            <input v-model.trim="searchWilayah" type="text" class="input-fordone w-full" />
                        </td>
                        <td class="search-header deleted"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="produsens.length > 0" v-for="data in paginatedData" :key="data.id">
                        <td>{{ data.number }}</td>
                        <td>{{ data.nama }}</td>
                        <td>{{ data.region_name }}</td>
                    </tr>
                    <tr v-else>
                        <td colspan="4" class="text-center">Data Tidak Ada</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Pagination @update:currentPage="updateCurrentPage" @update:showItems="updateShowItems" :show-items="showItems"
            :total-items="totalItems" :current-page="currentPage" :current-show-items="paginatedData.length" />
        <ModalBs :-modal-status="createModalStatus" @close="createModalStatus = false" :modal-size="'min-w-[30vw]'">
            <template #modalBody>
                <div class="form-group">
                    <div class="mb-3 space-y-2">
                        <label>Nama Dinas</label>
                        <input type="text" class="input-fordone w-full"></input>
                    </div>
                    <div class="mb-3 space-y-2">
                        <label>Wilayah Kerja</label>
                        <Multiselect :options="page.props.wilayah" :searchable="true"
                            placeholder="-- Pilih Wilayah Kerja --" />
                    </div>
                </div>
            </template>
            <template #modalFunction>
                <button type="button" class="btn-success-fordone btn-sm" @click.prevent="submit">
                    Simpan
                </button>
            </template>
        </ModalBs>
    </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import ModalBs from "@/Components/ModalBs.vue";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, ref, watch } from "vue";

const page = usePage();
var dataObject = page.props.produsen.data;
const produsens = ref(dataObject);
const createModalStatus = ref(false);

//fetch series
const searchNama = ref(null);
const searchWilayah = ref(null);
const ArrayBigObjects = [
    { key: "nama", valueFilter: searchNama },
    { key: "region_name", valueFilter: searchWilayah },
];
watch(
    ArrayBigObjects.map((obj) => obj.valueFilter),
    function () {
        currentPage.value = 1;
        delayedFetchData();
    }
);
const delayedFetchData = debounce(() => {
    fetchData();
});
const showItems = ref(10);
const currentPage = ref(1);
const updateShowItems = (value) => {
    showItems.value = value;
    fetchData();
};
const updateCurrentPage = (value) => {
    currentPage.value = value;
    fetchData();
};
const totalItems = ref(page.props.countData);
watch(
    () => page.props.countData,
    (value) => {
        totalItems.value = value;
    }
);
const paginatedData = computed(() => {
    return produsens.value;
});
watch(
    () => page.props.produsen.data,
    (value) => {
        produsens.value = value;
    }
);
const fetchData = async () => {
    try {
        const response = await axios.get(route("produsen.index"), {
            params: {
                currentPage: currentPage.value,
                paginated: showItems.value,
                ArrayFilter: {
                    nama: searchNama.value,
                    region_name: searchWilayah.value,
                },
                orderAttribute: orderAttribute.value,
            },
        });
    } catch (error) {
        console.error("Error fetching data: ", error);
    }
};
const orderAttribute = ref({
    before: null,
    label: null,
    value: "asc",
});
const clickToOrder = (value) => {
    orderAttribute.value.label = value;
    if (orderAttribute.value.before == null || orderAttribute.value.before == value) {
        if (orderAttribute.value.value == "asc") orderAttribute.value.value = "desc";
        else if (orderAttribute.value.value == "desc") orderAttribute.value.value = null;
        else orderAttribute.value.value = "asc";
    } else orderAttribute.value.value = "asc";
    orderAttribute.value.before = value;
    fetchData();
};

//submit
const form = useForm({
    _token: null,
    nama: null,
    region_id: null,
})
const submit = async () => {
    const response = await axios.get(route('token'))
    form._token = response.data
}
</script>

<style scoped></style>
