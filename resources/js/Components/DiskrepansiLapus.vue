<template>
  <tbody ref="tableRef">
    <template v-for="(nodeSubsectors, index) in subsectors" :key="index">
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
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td class="text-right font-bold">
              {{ getSumLvlTwo(nodeSubsectors.sector.category_id, node.value) }}
            </td>
          </template>
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
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td class="text-right pr-2">
              {{ getSumLvlOne(nodeSubsectors.sector_id, node.value) }}
            </td>
          </template>
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
            <p class="pl-5 pr-4" :for="nodeSubsectors.code + '_' + nodeSubsectors.name">
              {{ nodeSubsectors.code + ". " + nodeSubsectors.name }}
            </p>
          </td>
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td>
              {{ getData(nodeSubsectors.id, node.value) }}
            </td>
          </template>
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
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td>
              {{ getData(nodeSubsectors.id, node.value) }}
            </td>
          </template>
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
              :for="nodeSubsectors.sector.category.code + '_' + nodeSubsectors.name"
            >
              {{ nodeSubsectors.sector.category.code + ". " + nodeSubsectors.name }}
            </label>
          </td>
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td class="font-bold">
              {{ getData(nodeSubsectors.id, node.value) }}
            </td>
          </template>
        </tr>
      </template>
    </template>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB</p>
      </td>
      <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
        <td :id="'adhb_total-' + node.value" class="total-cell">
          {{ getPDRB(node.value) }}
        </td>
      </template>
    </tr>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB Nonmigas</p>
      </td>
      <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
        <td :id="'adhb_total-nonmigas-' + node.label" class="total-cell">
          {{ getPDRBNonMigas(node.value) }}
        </td>
      </template>
    </tr>
  </tbody>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";

const props = defineProps({
  subsectors: {
    type: Object,
    required: true,
  },
  tableColumn: {
    type: Object,
    required: true,
  },
  dataContents: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    required: true,
    default: "adhb",
  },
  onDemandType: {
    type: String,
    required: true,
    default: "adhb_now",
  },
  quarter: {
    type: String || Number,
    required: true,
  },
});
const dataHere = ref(props.dataContents);
const tableRef = ref(null);
const quarters = ref(props.quarter);
watch(
  () => props.dataContents,
  (value) => {
    dataHere.value = value;
  }
);
watch(
  () => props.quarter,
  (value) => {
    quarters.value = value;
  }
);
var observer = null;
onMounted(() => {
  setTimeout(() => {
    if (tableRef.value) {
      observer = new MutationObserver((mutations) => {
        captureTableData(props.onDemandType);
      });
    }
    observer.observe(tableRef.value, {
      childList: true,
      subtree: true,
      characterData: true,
    });
  }, 100);
});
const emits = defineEmits(["update:updateDOD"]);

const getData = (subsectors, regions) => {
  if (quarters.value != "t") {
    if (!isNaN(regions)) {
      const theData = dataHere.value.find((x) => {
        return (
          x.region_id == regions &&
          x.subsector_id == subsectors &&
          x.quarter == quarters.value
        );
      });
      if (theData) {
        let formattedResult;
        formattedResult =
          theData[props.type] == "" || theData[props.type] == null
            ? null
            : formatNumberGerman(Number(theData[props.type]), 0, 9);
        return formattedResult;
      }
    }
    if (isNaN(regions)) {
      const filteredData = dataHere.value.filter(
        (x) =>
          x.region_id !== 1 && x.subsector_id == subsectors && x.quarter == quarters.value
      );
      let result;
      if (filteredData)
        result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
      return formatNumberGerman(result, 0, 9);
    }
  } else {
    let filteredData;
    filteredData = dataHere.value.filter(
      (x) =>
        (isNaN(regions) ? x.region_id != 1 : x.region_id == regions) &&
        x.subsector_id == subsectors
    );
    let result;
    if (filteredData)
      result = filteredData.reduce((sum, item) => sum + Number(item[props.type] || 0), 0);
    return formatNumberGerman(result, 0, 9);
  }
};
const lvlOne = ref({});
const getSumLvlOne = (value, region_id) => {
  // Get all subsector IDs related to the given sector_id (value)
  let subsectorIds = props.subsectors
    .filter((x) => x.sector_id == value)
    .map((x) => x.id);
  // Get all matching data where region_id matches and subsector_id is in the subsector list
  let filteredData;
  filteredData = dataHere.value.filter(
    (x) =>
      (isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id) &&
      subsectorIds.includes(x.subsector_id) &&
      x.quarter == quarters.value
  );
  if (quarters.value == "t") {
    filteredData = dataHere.value.filter(
      (x) =>
        (isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id) &&
        subsectorIds.includes(x.subsector_id)
    );
  }
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlOne.value[value]) lvlOne.value[value] = {};
  lvlOne.value[value][region_id] = result;

  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const lvlTwo = ref({});
const getSumLvlTwo = (value, region_id) => {
  let subsectorIds = props.subsectors
    .filter((x) => x.sector.category_id == value)
    .map((x) => x.id);
  let filteredData;
  filteredData = dataHere.value.filter(
    (x) =>
      (isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id) &&
      subsectorIds.includes(x.subsector_id) &&
      x.quarter == quarters.value
  );
  if (quarters.value == "t") {
    filteredData = dataHere.value.filter(
      (x) =>
        (isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id) &&
        subsectorIds.includes(x.subsector_id)
    );
  }
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlTwo.value[value]) lvlTwo.value[value] = {};
  lvlTwo.value[value][region_id] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const lvlPDRB = ref({});
const getPDRB = (region_id) => {
  let filteredData;
  filteredData = dataHere.value.filter(
    (x) =>
      (isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id) &&
      x.quarter == quarters.value
  );
  if (quarters.value == "t") {
    filteredData = dataHere.value.filter((x) =>
      isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id
    );
  }
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB"]) lvlPDRB.value["PDRB"] = {};
  lvlPDRB.value["PDRB"][region_id] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const getPDRBNonMigas = (region_id) => {
  let filteredData;
  filteredData = dataHere.value.filter(
    (x) =>
      (isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id) &&
      ![10, 15].includes(x.subsector_id) &&
      x.quarter == quarters.value
  );
  if (quarters.value == "t") {
    filteredData = dataHere.value.filter(
      (x) =>
        (isNaN(region_id) ? x.region_id != 1 : x.region_id == region_id) &&
        ![10, 15].includes(x.subsector_id)
    );
  }
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB-NonMigas"]) lvlPDRB.value["PDRB-NonMigas"] = {};
  lvlPDRB.value["PDRB-NonMigas"][region_id] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const captureTableData = (type) => {
  //   const tbody = tableRef.value.querySelector("tbody");
  const rows = tableRef.value.querySelectorAll("tr");
  let tempData = {};
  rows.forEach((row) => {
    const cells = row.querySelectorAll("td");
    let rowData = [];
    cells.forEach((cell, index) => {
      const input = cell.querySelector("input");
      if (input) {
        rowData[index] = input.value.trim(); // Get input value
      } else {
        rowData[index] = cell.innerText.trim(); // Get text content
      }
    });
    if (rowData.length > 1) tempData[rowData[0]] = rowData.slice(1);
  });
  //   dataOnDemand.value = tempData;
  emits("update:updateDOD", { data: tempData, type: type });
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

tbody tr td:not(:nth-child(1)) {
  text-align: right;
}
tbody tr td {
  padding: 0.25rem;
}
</style>
