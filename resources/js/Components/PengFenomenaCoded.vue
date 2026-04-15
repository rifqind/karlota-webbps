<template>
  <tbody ref="tableRef">
    <template v-for="(nodeSubsectors, index) in subsectors">
      <template
        v-if="
          nodeSubsectors.code != null &&
          nodeSubsectors.code == 'a' &&
          nodeSubsectors.sector.category.type == 'Pengeluaran'
        "
      >
        <tr v-for="(node, index) in fenomenaValue">
          <template v-if="index == 0">
            <td :rowspan="fenomenaValue.length" class="desc-col fixed-column">
              <label class=""
                >{{ nodeSubsectors.sector.code }}. {{ nodeSubsectors.sector.name }}</label
              >
            </td>
          </template>
          <td class="text-right">{{ node == "implisit" ? "ctoc" : node }}</td>
          <td class="text-center font-bold">
            {{
              formatNumberGerman(
                tableModel?.growth?.[node]?.["sec-" + nodeSubsectors.sector.id] ?? 0,
                0,
                4
              )
            }}
          </td>
          <td>
            <textarea
              rows="8"
              spellcheck="false"
              :disabled="setDisabled()"
              :id="
                nodeSubsectors.sector.category_id +
                '-' +
                nodeSubsectors.sector.id +
                '-' +
                null +
                '-' +
                node
              "
              class="w-full input-fordone"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    null,
                    node
                  );
                }
              "
              @paste="
                (event) => {
                  handlePaste(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    null,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  null,
                  node
                )
              "
            />
          </td>
        </tr>
      </template>
      <template
        v-if="
          nodeSubsectors.code != null &&
          nodeSubsectors.sector.category.type == 'Pengeluaran'
        "
      >
        <tr v-for="(node, index) in fenomenaValue">
          <template v-if="index == 0">
            <td :rowspan="fenomenaValue.length" class="desc-col fixed-column">
              <p class="pl-5 pr-4" :for="nodeSubsectors.code + '_' + nodeSubsectors.name">
                {{ nodeSubsectors.code + ". " + nodeSubsectors.name }}
              </p>
            </td>
          </template>
          <td class="text-right">{{ node == "implisit" ? "ctoc" : node }}</td>
          <td class="text-center font-bold">
            {{
              formatNumberGerman(
                tableModel?.growth?.[node]?.["sub-" + nodeSubsectors.id] ?? 0,
                0,
                4
              )
            }}
          </td>
          <td>
            <textarea
              rows="8"
              spellcheck="false"
              :disabled="setDisabled()"
              :id="
                nodeSubsectors.sector.category_id +
                '-' +
                nodeSubsectors.sector.id +
                '-' +
                nodeSubsectors.id +
                '-' +
                node
              "
              class="w-full input-fordone"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              @paste="
                (event) => {
                  handlePaste(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node
                )
              "
            />
          </td>
        </tr>
      </template>
      <template
        v-else-if="
          nodeSubsectors.code == null &&
          nodeSubsectors.sector.code != null &&
          nodeSubsectors.sector.category.type == 'Pengeluaran'
        "
      >
        <tr v-for="(node, index) in fenomenaValue">
          <template v-if="index == 0">
            <td :rowspan="fenomenaValue.length" class="desc-col fixed-column">
              <label :for="nodeSubsectors.sector.code + '_' + nodeSubsectors.sector.name">
                {{ nodeSubsectors.sector.code + ". " + nodeSubsectors.sector.name }}
              </label>
            </td>
          </template>
          <td class="text-right">{{ node == "implisit" ? "ctoc" : node }}</td>
          <td class="text-center font-bold">
            {{
              formatNumberGerman(
                tableModel?.growth?.[node]?.["sub-" + nodeSubsectors.id] ?? 0,
                0,
                4
              )
            }}
          </td>
          <td>
            <textarea
              rows="8"
              spellcheck="false"
              :disabled="setDisabled()"
              :id="
                nodeSubsectors.sector.category_id +
                '-' +
                nodeSubsectors.sector.id +
                '-' +
                nodeSubsectors.id +
                '-' +
                node
              "
              class="w-full input-fordone"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              @paste="
                (event) => {
                  handlePaste(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node
                )
              "
            />
          </td>
        </tr>
      </template>
    </template>
  </tbody>
</template>

<script setup>
import { onMounted, ref, watch, computed } from "vue";

const props = defineProps({
  subsectors: {
    type: Object,
    required: true,
  },
  dataContents: {
    type: Object,
    required: true,
  },
  fenomenaStatus: {
    type: String,
    required: false,
    default: "Entry",
  },
  isYear: {
    type: Boolean,
    required: false,
    default: false,
  },
  isImplisit: {
    type: Boolean,
    required: false,
    default: true,
  },
  tableModel: {
    type: Object,
    required: false,
    default: {},
  },
});
const fenomenaValue = computed(() => {
  const result = ["qtoq", "yony", "implisit"];

  if (props.isYear) {
    return ["yony", "implisit"];
  }

  return props.isImplisit ? result : result.filter((x) => x !== "implisit");
});
const defaultData = ref([]);
const dataHere = ref(props.dataContents);
const emits = defineEmits([
  "update:updateDataContents",
  "update:handleInput",
  "update:handlePaste",
  "update:setDefaultData",
  "update:updateFenomSpecDev",
]);
watch(
  fenomenaValue,
  (value) => {
    emits("update:updateFenomSpecDev", value);
  },
  { immediate: true }
);
watch(
  () => props.dataContents,
  (value) => {
    dataHere.value = value;
  }
);
watch(defaultData, (value) => {
  emits("update:setDefaultData", value);
});
const setDisabled = () => {
  if (props.fenomenaStatus == "Entry") return false;
  return true;
};
onMounted(() => {
  defaultData.value = [];
  let tempData = [];
  props.subsectors.forEach((element) => {
    let data;
    if (
      (element.code != null &&
        element.code == "a" &&
        element.sector.code == "1" &&
        element.sector.category.type == "Lapangan Usaha") ||
      (element.code == null &&
        element.sector.code == "1" &&
        element.sector.category.type == "Lapangan Usaha")
    ) {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: null,
        subsector_id: null,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    }
    if (
      element.code != null &&
      element.code == "a" &&
      element.sector.category.type == "Lapangan Usaha"
    ) {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: element.sector.id,
        subsector_id: null,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    }
    if (element.code != null && element.sector.category.type == "Lapangan Usaha") {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: element.sector.id,
        subsector_id: element.id,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    } else if (
      element.code == null &&
      element.sector.code != null &&
      element.sector.category.type == "Lapangan Usaha"
    ) {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: element.sector.id,
        subsector_id: element.id,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    } else if (
      element.code == null &&
      element.sector.code == null &&
      element.sector.category.type == "Lapangan Usaha"
    ) {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: element.sector.id,
        subsector_id: element.id,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    }
    if (
      element.code != null &&
      element.code == "a" &&
      element.sector.category.type == "Pengeluaran"
    ) {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: element.sector.id,
        subsector_id: null,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    }
    if (element.code != null && element.sector.category.type == "Pengeluaran") {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: element.sector.id,
        subsector_id: element.id,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    } else if (
      element.code == null &&
      element.sector.code != null &&
      element.sector.category.type == "Pengeluaran"
    ) {
      data = {
        id: null,
        fenomena_sets: null,
        category_id: element.sector.category_id,
        sector_id: element.sector.id,
        subsector_id: element.id,
        qtoq: null,
        yony: null,
        implisit: null,
      };
      tempData.push(data);
    }
  });
  dataHere.value = tempData;
  defaultData.value = tempData;
  emits("update:updateDataContents", tempData);
});
const getData = (category_id, sector_id, subsector_id, type) => {
  if (Object.keys(dataHere.value).length > 0) {
    const theData = dataHere.value.find((x) => {
      return (
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id
      );
    });
    if (theData) {
      return theData[type];
    }
  }
};
const handleInput = (event, category_id, sector_id, subsector_id, type) => {
  let value = event.target.value;
  const theIndex = dataHere.value.findIndex((x) => {
    return (
      x.category_id == category_id &&
      x.sector_id == sector_id &&
      x.subsector_id == subsector_id
    );
  });
  if (theIndex != -1) {
    dataHere.value[theIndex][type] = value;
    emits("update:handleInput", { theIndex: theIndex, type: type, value: value });
  }
};
const handlePaste = (event) => {
  const items = event.clipboardData.items;
  for (let i = 0; i < items.length; i++) {
    if (items[i].type == "text/plain") {
      items[i].getAsString((text) => {
        const columnIndex = event.target.closest("td").cellIndex;
        const rowIndex = event.target.closest("tr").rowIndex;
        // const lines = text.trim().split("\n");
        const parsedRows = parseTSVWithQuotes(text);
        // lines.forEach((line, index) => {
        parsedRows.forEach((cells, index) => {
          // const cells = line.trim().split("\t");
          cells.forEach((cell, subIndex) => {
            const row = rowIndex + index;
            const col = columnIndex + subIndex;
            const table = event.target.closest("table");
            const tableRow = table.rows[row];
            if (tableRow) {
              const tableCell = tableRow.cells[col];
              if (tableCell) {
                let input = tableCell.querySelector("textarea");
                if (input) {
                  let category = input.id.split("-")[0];
                  let sector = input.id.split("-")[1];
                  let subsector = input.id.split("-")[2];
                  const type = input.id.split("-")[3];
                  input = cell;
                  sector = sector == "null" ? null : sector;
                  subsector = subsector == "null" ? null : subsector;
                  const theIndex = dataHere.value.findIndex((x) => {
                    return (
                      x.category_id == category &&
                      x.sector_id == sector &&
                      x.subsector_id == subsector
                    );
                  });
                  if (theIndex != -1) {
                    dataHere.value[theIndex][type] = input;
                    emits("update:handlePaste", {
                      theIndex: theIndex,
                      type: type,
                      value: input,
                    });
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
const parseTSVWithQuotes = (text) => {
  const rows = [];
  let currentRow = [];
  let currentCell = "";
  let inQuotes = false;

  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const nextChar = text[i + 1];

    if (char == '"') {
      if (inQuotes && nextChar == '"') {
        // Escaped quote
        currentCell += '"';
        i++; // skip next quote
      } else {
        inQuotes = !inQuotes; // toggle quotes
      }
    } else if (char == "\t" && !inQuotes) {
      currentRow.push(currentCell);
      currentCell = "";
    } else if ((char == "\n" || char == "\r") && !inQuotes) {
      if (currentCell || currentCell == "") {
        currentRow.push(currentCell);
        currentCell = "";
      }
      if (currentRow.length > 0) {
        rows.push(currentRow);
        currentRow = [];
      }
      if (char == "\r" && nextChar == "\n") i++; // Windows \r\n
    } else {
      currentCell += char;
    }
  }

  // Add last cell and row
  if (currentCell || currentCell == "") currentRow.push(currentCell);
  if (currentRow.length > 0) rows.push(currentRow);

  return rows;
};
const formatNumberGerman = (num, min = 2, max = 5) => {
  if (num == 0) return "";
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
</script>

<style scoped>
.fixed-column {
  position: sticky;
  min-width: 400px;
  left: 0;
  background-color: white;
  color: black;
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
  text-align: left;
}

tbody td {
  font-size: smaller;
  padding: 0.25rem;
  height: 50px;
  /* Set a fixed height */
  line-height: 1.2;
  /* Adjust line height */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
textarea {
  font-size: smaller;
  line-height: 1.5;
  text-align: justify;
}
tbody tr {
  height: 50px;
}

.not-fixed {
  min-width: 250px;
}
</style>
