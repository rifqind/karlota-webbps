<template>
  <Head title="Entri Data Sekunder" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <FloatScrollDown />
    <div id="container-of-entry" class="pb-3">
      <div class="bg-white shadow-md mb-2 rounded-md border border-gray-200">
        <div class="py-4 px-6">
          <h3 class="font-bold text-2xl">
            {{ page.props.sekunder.label }}, Tahun
            {{ page.props.status_sekunder.tahun }}
          </h3>
          <h4 class="mt-2 flex text-2xl items-center">
            <span class="badge badge-info" id="badges-status">
              {{ page.props.status_sekunder.status_label }}</span
            >
            <span class="ml-auto text-xl text-right" id="">
              Terakhir diupdate : {{ page.props.status_sekunder.updated_time }}
            </span>
          </h4>
        </div>
      </div>
      <!-- data -->
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="text-center align-middle fixed-thead">Data</th>
              <th
                class="text-center align-middle not-fixed"
                v-for="(node, index) in [1, 2, 3, 4]"
                :key="index"
              >
                Triwulan {{ node }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(node, index) in page.props.rows" :key="index">
              <td class="fixed-column">{{ node.label }}</td>
              <td v-for="(item, idx) in [1, 2, 3, 4]" :key="idx">
                <input
                  type="text"
                  class="w-full input-fordone"
                  :value="getData(node.id, item)"
                  :id="'cell-' + node.id + '-' + item"
                  @input="
                    (e) => {
                      debounceHandleInput(e, node.id, item);
                    }
                  "
                  @paste="
                    (e) => {
                      handlePaste(e);
                    }
                  "
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-end">
        <button class="btn-success-fordone" @click.prevent="submit">
          <font-awesome-icon icon="fa-solid fa-check" /> Simpan
        </button>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import { triggerSpinner } from "@/axiosSetup";
import FloatScrollDown from "@/Components/FloatScrollDown.vue";
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

const page = usePage();
const form = useForm({
  _token: null,
  datacontent: page.props.datacontent,
});

//tablehandle
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const getData = (r, tw) => {
  const datas = form.datacontent.find((x) => {
    return x.row_id == r && x.triwulan == tw;
  });
  if (datas) {
    let formattedResult;
    if (datas.data == "" || datas.data == null) formattedResult = null;
    else formattedResult = formatNumberGerman(Number(datas.data), 0, 9);
    return formattedResult;
  }
};
const handleInput = (e, r, tw) => {
  let value = e.target.value;
  value = String(value).replaceAll(".", "").replace(",", ".");
  const dataIndex = form.datacontent.findIndex((x) => {
    return x.row_id == r && x.triwulan == tw;
  });
  if (dataIndex != -1) form.datacontent[dataIndex].data = Number(value);
};
const debounceHandleInput = debounce((e, r, tw) => {
  handleInput(e, r, tw);
}, 700);
const handlePaste = (e) => {
  const items = e.clipboardData.items;
  for (let i = 0; i < items.length; i++) {
    if (items[i].type == "text/plain") {
      items[i].getAsString((text) => {
        const columnIndex = e.target.closest("td").cellIndex;
        const rowIndex = e.target.closest("tr").rowIndex;
        const lines = text.trim().split("\n");
        lines.forEach((line, index) => {
          const cells = line.trim().split("\t");
          cells.forEach((cell, subIndex) => {
            const row = rowIndex + index;
            const col = columnIndex + subIndex;
            const table = e.target.closest("table");
            const tableRow = table.rows[row];
            if (tableRow) {
              const tableCell = tableRow.cells[col];
              if (tableCell) {
                let input = tableCell.querySelector('input:not([type="hidden"])');
                if (input) {
                  const r = input.id.split("-")[1];
                  const tw = input.id.split("-")[2];
                  input = cell;
                  //   if (cell == "-") cell = String(0);
                  let formatCell = String(cell).replaceAll(".", "").replace(",", ".");
                  const dataIndex = form.datacontent.findIndex((x) => {
                    return x.row_id == r && x.triwulan == tw;
                  });
                  if (dataIndex != -1)
                    form.datacontent[dataIndex].data = Number(formatCell);
                }
              }
            }
          });
        });
      });
    }
  }
};

//form
const validateContent = () => {
  return form.datacontent.some((e) => isNaN(e.data));
};
const submit = async () => {
  try {
    let result = validateContent();
    if (result) {
      console.log("hehe");
      return;
    }
    const token = await axios.get(route("token"));
    form._token = token.data;
    form.post(route("sekunder.update"));
  } catch (error) {
    console.error(error);
  }
};
</script>

<style scoped>
.fixed-thead {
  position: sticky;
  width: 400px;
  left: 0;
  background-color: #175676;
  color: whitesmoke;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}
.table {
  table-layout: fixed;
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  font-size: smaller;
  /* Avoid extra spacing */
}
.input-fordone {
  padding: 5px 5px 5px 5px;
  text-align: right;
  /* font-size: smaller; */
}
</style>
