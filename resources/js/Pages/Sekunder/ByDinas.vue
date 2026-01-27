<template>
  <Head title="Daftar Dinas" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="mb-2 flex flex-wrap items-center justify-between">
      <div class="text-xl font-bold w-full md:w-full lg:w-auto mb-2 md:mb-2 lg:mb-0">
        Data By Dinas
      </div>
      <div class="flex items-center w-full md:w-full lg:w-auto">
        <button class="btn-success-fordone mr-2 mb-2 lg:mb-0" title="Download">
          <font-awesome-icon icon="fa-solid fa-circle-down" />
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
            <th class="text-center th-order">Data Sudah Ada</th>
            <th class="text-center th-order tabel-width-8 deleted">Edit/Hapus</th>
          </tr>
          <tr>
            <td class="search-header"></td>
            <td class="search-header">
              <input v-model.trim="searchNama" type="text" class="input-fordone w-full" />
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
                v-model.trim="searchSekunder"
                type="text"
                class="input-fordone w-full"
              />
            </td>
            <td class="search-header deleted"></td>
          </tr>
        </thead>
        <tbody>
          <tr v-if="produsens.length > 0" v-for="data in paginatedData" :key="data.id">
            <td>{{ data.number }}</td>
            <td>{{ data.produsen_label }}</td>
            <td>{{ data.region_name }}</td>
            <td>
              <span
                @click="(event) => handleClickSpan(event, sl)"
                class="badge badge-info mr-1"
                v-for="(sl, i) in data.sekunder_list"
                :key="i"
                >{{ hiddenText(sl) }}</span
              >
            </td>
            <td class="text-center">
              <Link :href="route('sekunder.by-dinas-view', { id: data.id })">
                <font-awesome-icon
                  icon="fa-solid fa-eye"
                  class="edit-pen mx-2"
                  title="Liat"
                />
              </Link>
            </td>
          </tr>
          <tr v-else>
            <td colspan="4" class="text-center">Data Tidak Ada</td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination
      @update:currentPage="updateCurrentPage"
      @update:showItems="updateShowItems"
      :show-items="showItems"
      :total-items="totalItems"
      :current-page="currentPage"
      :current-show-items="paginatedData.length"
    />
    <ModalBs
      :-modal-status="createModalStatus"
      @close="
        () => {
          createModalStatus = false;
          form.reset();
          formError = [];
        }
      "
      :modal-size="'min-w-[30vw]'"
      :title="modalTitle"
    >
      <template #modalBody>
        <div class="form-group">
          <div class="mb-3 space-y-2">
            <label>Nama Dinas</label>
            <input
              v-model="form.nama"
              type="text"
              class="input-fordone w-full"
              placeholder="Isi Nama Dinas"
            />
          </div>
          <div class="text-danger" v-if="form.errors.nama">{{ form.errors.nama }}</div>
          <div class="mb-3 space-y-2">
            <label>Wilayah Kerja</label>
            <Multiselect
              v-model="form.region_id"
              :options="page.props.wilayah"
              :searchable="true"
              placeholder="-- Pilih Wilayah Kerja --"
            />
          </div>
          <div class="text-danger" v-if="form.errors.region_id">
            {{ form.errors.region_id }}
          </div>
          <div class="text-danger" v-if="formError.length > 0" v-for="node in formError">
            {{ node }}
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
import FlashFetch from "@/Components/FlashFetch.vue";
import ModalBs from "@/Components/ModalBs.vue";
import Pagination from "@/Components/Pagination.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { computed, ref, watch } from "vue";

const page = usePage();
var dataObject = page.props.produsen;
const produsens = ref(dataObject);
const createModalStatus = ref(false);
const deleteModalStatus = ref(false);

const formError = ref([]);
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};

//fetch series
const searchNama = ref(null);
const searchWilayah = ref(null);
const searchSekunder = ref(null);
const ArrayBigObjects = [
  { key: "nama", valueFilter: searchNama },
  { key: "region_name", valueFilter: searchWilayah },
  { key: "sekunder_list", valueFilter: searchSekunder },
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
  () => page.props.produsen,
  (value) => {
    produsens.value = value;
  }
);
const fetchData = async () => {
  try {
    const { data } = await axios.get(route("sekunder.data-by-dinas"), {
      params: {
        currentPage: currentPage.value,
        paginated: showItems.value,
        ArrayFilter: {
          nama: searchNama.value,
          region_name: searchWilayah.value,
          sekunder_list: searchSekunder.value,
        },
        orderAttribute: orderAttribute.value,
      },
    });
    produsens.value = data.produsen;
    totalItems.value = data.countData;
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
  id: null,
  _token: null,
  nama: null,
  region_id: null,
});
const submit = async () => {
  const response = await axios.get(route("token"));
  form._token = response.data;
  form.post(route("produsen.store"), {
    onSuccess: (response) => {
      showNotification(response.props.notification);
      if (response.props.notification[0].type == "success") {
        fetchData();
        form
          .defaults({
            id: null,
            _token: null,
            nama: null,
            region_id: null,
          })
          .reset();
        formError.value = [];
        createModalStatus.value = false;
      } else {
        response.props.notification.forEach((element) => {
          formError.value.push(element.error);
        });
      }
    },
    onFinish: () => {
      modalTitle.value = "Tambah Dinas Baru";
    },
  });
};

//modal
const modalTitle = ref("Tambah Dinas Baru");
const toggleUpdateModal = async (id) => {
  try {
    modalTitle.value = "Update Dinas";
    const response = await axios.get(route("produsen.fetch", { id }));
    form.id = response.data.data.id;
    form.nama = response.data.data.nama;
    form.region_id = response.data.data.region_id;
    createModalStatus.value = true;
  } catch (error) {
    console.error(error);
  }
};
const deleteUpdateModal = async (id) => {
  try {
    const response = await axios.get(route("produsen.fetch", { id }));
    form.id = response.data.data.id;
    deleteModalStatus.value = true;
  } catch (error) {
    console.error(error);
  }
};
const hiddenText = (value) => {
  if (value.length > 50) {
    return value.substring(0, 50) + "...";
  } else return value;
};
//test
const handleClickSpan = (e, t) => {
  let span = e.target;
  if (span.textContent.length == 53) {
    span.textContent = t;
    return;
  }
  span.textContent = hiddenText(t);
};
</script>

<style scoped></style>
