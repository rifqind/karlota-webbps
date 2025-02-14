<template>
  <Head title="Entri PDRB" />
  <SpinnerBorder v-if="triggerSpinner" />
  <GeneralLayout>
    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Entri PDRB</label>
        </div>
        <div class="p-5">
          <div class="mb-3 space-y-2">
            <label for="type">Pilih PDRB<span class="text-danger">*</span></label>
            <Multiselect v-model="form.type" :placeholder="form.type" disabled />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Tahun<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.year"
              :options="yearDrop"
              :searchable="true"
              placeholder="-- Pilih Tahun --"
              @change="fetchQuarter"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Triwulan<span class="text-danger">*</span></label>
            <Multiselect
              v-model="form.quarter"
              :options="quarterDrop"
              :searchable="true"
              placeholder="-- Pilih Triwulan --"
              @change="fetchPeriod"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year"
              >Pilih Periode Putaran<span class="text-danger">*</span></label
            >
            <Multiselect
              v-model="form.description"
              :options="descDrop"
              :searchable="true"
              placeholder="-- Pilih Periode Putaran --"
              @change="fetchYearBefore"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="year">Pilih Data Tahun Sebelumnya</label>
            <Multiselect
              v-model="form.dataBefore"
              :options="dataBeforeDrop"
              :searchable="true"
              placeholder="-- Pilih Data Tahun Sebelumnya (opsional, jika tidak dipilih maka akan memilih data tahun paling terbaru/terakhir) --"
            />
            <div class="text-danger text-left" v-if="true" id="error-dinas"></div>
          </div>
          <div class="btn-info-fordone ml-auto w-[120px]">
            <font-awesome-icon icon="fa fa-save" /> Entri Data
          </div>
        </div>
      </div>
      <div class="overflow-x-scroll mb-2">
        <table class="table shadow-md w-full mb-2" id="tabel-entry">
          <thead>
            <tr>
              <th class="fixed-thead">Komponen</th>
              <th>Triwulan I</th>
              <th>Triwulan II</th>
              <th>Triwulan III</th>
              <th>Triwulan IV</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(nodeSubsectors, index) in subsectors">
              <template
                v-if="
                  (nodeSubsectors.code != null &&
                    nodeSubsectors.code == 'a' &&
                    nodeSubsectors.sector.code == '1' &&
                    nodeSubsectors.sector.category.type == 'Lapangan Usaha') ||
                  (nodeSubsectors.code == null &&
                    nodeSubsectors.sector.code == '1' &&
                    nodeSubsectors.sector.category.type == 'Lapangan Usaha')
                "
              >
                <tr>
                  <td class="desc-col fixed-column">
                    <label class=""
                      >{{ nodeSubsectors.sector.category.code }}.
                      {{ nodeSubsectors.sector.category.name }}</label
                    >
                  </td>
                  <template v-for="(node, index) in quarters">
                    <td class="text-right">
                      {{ getSumLvlTwo(nodeSubsectors.sector.category_id, node.label) }}
                    </td>
                  </template>
                  <td class="text-right">
                    {{ getSumRowCat(nodeSubsectors.sector.category_id) }}
                  </td>
                </tr>
              </template>
              <template
                v-if="
                  nodeSubsectors.code != null &&
                  nodeSubsectors.code == 'a' &&
                  nodeSubsectors.sector.category.type == 'Lapangan Usaha'
                "
              >
                <tr>
                  <td class="desc-col fixed-column">
                    <p class="pl-4">
                      {{ nodeSubsectors.sector.code }}. {{ nodeSubsectors.sector.name }}
                    </p>
                  </td>
                  <template v-for="(node, index) in quarters">
                    <td class="text-right pr-2">
                      {{ getSumLvlOne(nodeSubsectors.sector_id, node.label) }}
                    </td>
                  </template>
                  <td class="text-right">
                    {{ getSumRowSector(nodeSubsectors.sector.id) }}
                  </td>
                </tr>
              </template>
              <template
                v-if="
                  nodeSubsectors.code != null &&
                  nodeSubsectors.sector.category.type == 'Lapangan Usaha'
                "
              >
                <tr>
                  <td class="desc-col fixed-column">
                    <p
                      class="pl-5 pr-4"
                      :for="nodeSubsectors.code + '_' + nodeSubsectors.name"
                    >
                      {{ nodeSubsectors.code + ". " + nodeSubsectors.name }}
                    </p>
                  </td>
                  <template v-for="(node, index) in quarters">
                    <td>
                      <input
                        type="text"
                        :id="'cell-' + nodeSubsectors.id + '-' + node.label"
                        :value="getData(nodeSubsectors.id, node.label)"
                        @input="
                          (event) => {
                            debounceHandleInput(event, nodeSubsectors.id, node.label);
                          }
                        "
                        @paste="
                          (event) => {
                            handlePaste(event, nodeSubsectors.id, node.label);
                          }
                        "
                        class="w-full input-fordone"
                      />
                    </td>
                  </template>
                  <td class="text-right">{{ getSumTotalFromVal(nodeSubsectors.id) }}</td>
                </tr>
              </template>
              <template
                v-else-if="
                  nodeSubsectors.code == null &&
                  nodeSubsectors.sector.code != null &&
                  nodeSubsectors.sector.category.type == 'Lapangan Usaha'
                "
              >
                <tr>
                  <td class="desc-col fixed-column">
                    <p
                      class="pl-4 pr-4"
                      :for="nodeSubsectors.sector.code + '_' + nodeSubsectors.sector.name"
                    >
                      {{ nodeSubsectors.sector.code + ". " + nodeSubsectors.sector.name }}
                    </p>
                  </td>
                  <template v-for="(node, index) in quarters">
                    <td>
                      <input
                        type="text"
                        :id="'cell-' + nodeSubsectors.id + '-' + node.label"
                        :value="getData(nodeSubsectors.id, node.label)"
                        @input="
                          (event) => {
                            debounceHandleInput(event, nodeSubsectors.id, node.label);
                          }
                        "
                        @paste="
                          (event) => {
                            handlePaste(event, nodeSubsectors.id, node.label);
                          }
                        "
                        class="w-full input-fordone"
                      />
                    </td>
                  </template>
                  <td class="text-right">{{ getSumTotalFromVal(nodeSubsectors.id) }}</td>
                </tr>
              </template>
              <template
                v-else-if="
                  nodeSubsectors.code == null &&
                  nodeSubsectors.sector.code == null &&
                  nodeSubsectors.sector.category.type == 'Lapangan Usaha'
                "
              >
                <tr>
                  <td class="desc-col fixed-column">
                    <label
                      class="col"
                      :for="
                        nodeSubsectors.sector.category.code + '_' + nodeSubsectors.name
                      "
                    >
                      {{
                        nodeSubsectors.sector.category.code + ". " + nodeSubsectors.name
                      }}
                    </label>
                  </td>
                  <template v-for="(node, index) in quarters">
                    <td>
                      <input
                        type="text"
                        :id="'cell-' + nodeSubsectors.id + '-' + node.label"
                        :value="getData(nodeSubsectors.id, node.label)"
                        @input="
                          (event) => {
                            debounceHandleInput(event, nodeSubsectors.id, node.label);
                          }
                        "
                        @paste="
                          (event) => {
                            handlePaste(event, nodeSubsectors.id, node.label);
                          }
                        "
                        class="w-full input-fordone"
                      />
                    </td>
                  </template>
                  <td class="text-right">{{ getSumTotalFromVal(nodeSubsectors.id) }}</td>
                </tr>
              </template>
            </template>
            <tr class="PDRB-footer text-center">
              <td class="desc-col footer-column">
                <p class="mt-1 mb-1">PDRB</p>
              </td>
              <template v-for="(node, index) in quarters">
                <td :id="'adhb_total-' + node.label" class="total-cell">
                  {{ getPDRB(node.label) }}
                </td>
              </template>
              <td class="total-cell">{{ getSumPDRB("PDRB") }}</td>
            </tr>
            <tr class="PDRB-footer text-center">
              <td class="desc-col footer-column">
                <p class="mt-1 mb-1">PDRB Nonmigas</p>
              </td>
              <template v-for="(node, index) in quarters">
                <td :id="'adhb_total-nonmigas-' + node.label" class="total-cell">
                  {{ getPDRBNonMigas(node.label) }}
                </td>
              </template>
              <td class="total-cell">{{ getSumPDRB("PDRB-NonMigas") }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </GeneralLayout>
</template>
<script setup>
import SpinnerBorder from "@/Components/SpinnerBorder.vue";
import { debounce } from "@/debounce";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Multiselect from "@vueform/multiselect";
import { onMounted, ref, watch } from "vue";

const page = usePage();
const subsectors = ref(page.props.subsectors);
const dataContents = ref(page.props.dataContents);
const quarters = [{ label: "1" }, { label: "2" }, { label: "3" }, { label: "4" }];
const form = useForm({
  dataContents: null,
  type: page.props.type,
  year: null,
  quarter: null,
  description: null,
  dataBefore: null,
});
const yearDrop = ref([]);
const quarterDrop = ref([]);
const descDrop = ref([]);
const dataBeforeDrop = ref([]);
onMounted(() => {
  fetchYear();
});

// #region Section: FETCH
const fetchYear = async (value) => {
  try {
    const response = await axios.get(route("period.fetchYear"), {
      params: {
        type: page.props.type,
      },
    });
    let result = response.data;
    yearDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const fetchQuarter = async (value) => {
  try {
    const response = await axios.get(route("period.fetchQuarter"), {
      params: {
        type: page.props.type,
        year: value,
      },
    });
    let result = response.data;
    quarterDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const fetchPeriod = async (value) => {
  try {
    const response = await axios.get(route("period.fetchPeriod"), {
      params: {
        type: page.props.type,
        year: form.year,
        quarter: value,
      },
    });
    let result = response.data;
    descDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
const fetchYearBefore = async (value) => {
  try {
    const response = await axios.get(route("period.fetchYearBefore"), {
      params: {
        type: page.props.type,
        year: form.year,
      },
    });
    let result = response.data;
    dataBeforeDrop.value = result;
  } catch (error) {
    console.error(error);
  }
};
// #endregion

const triggerSpinner = ref(false);
watch(
  () => dataContents.value,
  (value) => {}
);

// #region Section: GET_DATA
const getData = (subsectors, quarter) => {
  const theData = dataContents.value.find((x) => {
    return x.quarter == quarter && x.subsector_id == subsectors;
  });
  let formattedResult;
  formattedResult =
    theData.adhb == "" || theData.adhb == null
      ? null
      : formatNumberGerman(Number(theData.adhb), 0, 9);
  return formattedResult;
  // return theData.adhb;
};
const lvlOne = ref({});
const getSumLvlOne = (value, quarter) => {
  // Get all subsector IDs related to the given sector_id (value)
  let subsectorIds = subsectors.value
    .filter((x) => x.sector_id == value)
    .map((x) => x.id);
  // Get all matching data where quarter matches and subsector_id is in the subsector list
  const filteredData = dataContents.value.filter(
    (x) => x.quarter == quarter && subsectorIds.includes(x.subsector_id)
  );
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item.adhb), 0);
  if (!lvlOne.value[value]) lvlOne.value[value] = {};
  lvlOne.value[value][quarter] = result;

  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const lvlTwo = ref({});
const getSumLvlTwo = (value, quarter) => {
  let subsectorIds = subsectors.value
    .filter((x) => x.sector.category_id == value)
    .map((x) => x.id);
  const filteredData = dataContents.value.filter(
    (x) => x.quarter == quarter && subsectorIds.includes(x.subsector_id)
  );
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item.adhb), 0);
  if (!lvlTwo.value[value]) lvlTwo.value[value] = {};
  lvlTwo.value[value][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumTotalFromVal = (value) => {
  const filteredData = dataContents.value.filter((x) => x.subsector_id == value);
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item.adhb), 0);
  // console.log(result);
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumRowCat = (value) => {
  if (!lvlTwo.value[value]) return 0; // If no data, return 0

  // Get all quarter sums for this category
  let totalSum = Object.values(lvlTwo.value[value]).reduce(
    (sum, quarterSum) => sum + quarterSum,
    0
  );

  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};

const getSumRowSector = (value) => {
  if (!lvlOne.value[value]) return 0; // If no data, return 0

  // Get all quarter sums for this category
  let totalSum = Object.values(lvlOne.value[value]).reduce(
    (sum, quarterSum) => sum + quarterSum,
    0
  );

  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};

const lvlPDRB = ref({});
const getPDRB = (quarter) => {
  const filteredData = dataContents.value.filter((x) => x.quarter == quarter);
  const result = filteredData.reduce((sum, item) => sum + Number(item.adhb), 0);
  if (!lvlPDRB.value["PDRB"]) lvlPDRB.value["PDRB"] = {};
  lvlPDRB.value["PDRB"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getPDRBNonMigas = (quarter) => {
  const filteredData = dataContents.value.filter(
    (x) => x.quarter == quarter && ![10, 15].includes(x.subsector_id)
  );
  const result = filteredData.reduce((sum, item) => sum + Number(item.adhb), 0);
  if (!lvlPDRB.value["PDRB-NonMigas"]) lvlPDRB.value["PDRB-NonMigas"] = {};
  lvlPDRB.value["PDRB-NonMigas"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumPDRB = (pdrb) => {
  if (!lvlPDRB.value[pdrb]) return 0;

  let totalSum = Object.values(lvlPDRB.value[pdrb]).reduce(
    (sum, pdrbSum) => sum + pdrbSum,
    0
  );
  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};

const formatNumberGerman = (num, min = 2, max = 2) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};

// #endregion

// #region Section: HANDLE_FUNCTION
const handleInput = (event, subsector, quarter) => {
  let value = event.target.value;
  value = String(value).replaceAll(".", "").replace(",", ".");
  const theIndex = dataContents.value.findIndex((x) => {
    return x.quarter == quarter && x.subsector_id == subsector;
  });
  if (theIndex !== -1) dataContents.value[theIndex].adhb = value;
};
const debounceHandleInput = debounce((event, subsector, quarter) => {
  handleInput(event, subsector, quarter);
}, 700);
const handlePaste = (event, subsector, quarter) => {
  const items = event.clipboardData.items;
  for (let i = 0; i < items.length; i++) {
    if (items[i].type === "text/plain") {
      items[i].getAsString((text) => {
        const columnIndex = event.target.closest("td").cellIndex;
        const rowIndex = event.target.closest("tr").rowIndex;
        const lines = text.trim().split("\n");
        lines.forEach((line, index) => {
          const cells = line.trim().split("\t");
          cells.forEach((cell, subIndex) => {
            const row = rowIndex + index;
            const col = columnIndex + subIndex;
            const table = event.target.closest("table");
            const tableRow = table.rows[row];
            if (tableRow) {
              const tableCell = tableRow.cells[col];
              if (tableCell) {
                let input = tableCell.querySelector('input:not([type="hidden"])');
                if (input) {
                  const subsector = input.id.split("-")[1];
                  const quarter = input.id.split("-")[2];
                  input = cell;
                  let formatCell = String(cell).replaceAll(".", "").replace(",", ".");
                  const theIndex = dataContents.value.findIndex((x) => {
                    return x.quarter == quarter && x.subsector_id == subsector;
                  });
                  if (theIndex !== -1) {
                    dataContents.value[theIndex].adhb = formatCell;
                  }
                }
              }
            }
          });
        });
      });
    }
  }
};
const debounceHandlePaste = debounce((event, subsector, quarter) => {
  handlePaste(event, subsector, quarter);
}, 700);
// #endregion
</script>

<style scoped>
.fixed-column {
  position: sticky;
  width: 400px;
  left: 0;
  background-color: white;
  color: black;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

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

.total-cell {
  background-color: #175676;
  color: whitesmoke;
}

.footer-column {
  font-weight: bold;
  position: sticky;
  width: 400px;
  background-color: #175676;
  color: whitesmoke;
  left: 0;
  z-index: 1;
  box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.2);
  border-right: 1px solid #ccc;
  border-left: 1px solid #ccc;
}

.input-fordone {
  text-align: right;
}

tbody td {
  padding: 0.25rem;
  height: 50px;
  /* Set a fixed height */
  line-height: 1.2;
  /* Adjust line height */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

tbody tr {
  height: 50px;
}

.table {
  table-layout: fixed;
  /* Ensures consistent column width */
  width: 100%;
  border-collapse: collapse;
  /* Avoid extra spacing */
}

.not-fixed {
  min-width: 250px;
}
</style>
