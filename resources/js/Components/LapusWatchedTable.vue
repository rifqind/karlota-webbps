<template>
  <tbody ref="tableRef">
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
            <td class="text-right font-bold">
              {{ getSumLvlTwo(nodeSubsectors.sector.category_id, node.label) }}
            </td>
          </template>
          <td class="text-right font-bold">
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
            <p class="pl-5 pr-4" :for="nodeSubsectors.code + '_' + nodeSubsectors.name">
              {{ nodeSubsectors.code + ". " + nodeSubsectors.name }}
            </p>
          </td>
          <template v-for="(node, index) in quarters">
            <td class="text-right">{{ getData(nodeSubsectors.id, node.label) }}</td>
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
            <td class="text-right">{{ getData(nodeSubsectors.id, node.label) }}</td>
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
              :for="nodeSubsectors.sector.category.code + '_' + nodeSubsectors.name"
            >
              {{ nodeSubsectors.sector.category.code + ". " + nodeSubsectors.name }}
            </label>
          </td>
          <template v-for="(node, index) in quarters">
            <td class="text-right">{{ getData(nodeSubsectors.id, node.label) }}</td>
          </template>
          <td class="text-right font-bold">
            {{ getSumTotalFromVal(nodeSubsectors.id) }}
          </td>
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
</template>

<script setup>
import { onMounted, ref, watch } from "vue";

const props = defineProps({
  subsectors: {
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
    default: "adhk_watched",
  },
});
const dataHere = ref(props.dataContents);
const tableRef = ref(null);
const quarters = [{ label: "1" }, { label: "2" }, { label: "3" }, { label: "4" }];
const emits = defineEmits(["update:updateDOD"]);
watch(
  () => props.dataContents,
  (value) => {
    dataHere.value = value;
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
// #region Section: GET_DATA
const getData = (subsectors, quarter) => {
  const theData = dataHere.value.find((x) => {
    return x.quarter == quarter && x.subsector_id == subsectors;
  });
  if (theData) {
    let formattedResult;
    if (theData[props.type] == "" || theData[props.type] == null) {
      formattedResult = null;
    } else {
      let number;
      if (theData[props.type] == "-") number = 0;
      else number = Number(theData[props.type]);
      formattedResult = formatNumberGerman(number, 0, 9);
    }
    return formattedResult;
  }
};
const lvlOne = ref({});
const getSumLvlOne = (value, quarter) => {
  // Get all subsector IDs related to the given sector_id (value)
  let subsectorIds = props.subsectors
    .filter((x) => x.sector_id == value)
    .map((x) => x.id);
  // Get all matching data where quarter matches and subsector_id is in the subsector list
  const filteredData = dataHere.value.filter(
    (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  );
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlOne.value[value]) lvlOne.value[value] = {};
  lvlOne.value[value][quarter] = result;

  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const lvlTwo = ref({});
const getSumLvlTwo = (value, quarter) => {
  let subsectorIds = props.subsectors
    .filter((x) => x.sector.category_id == value)
    .map((x) => x.id);
  const filteredData = dataHere.value.filter(
    (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  );
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlTwo.value[value]) lvlTwo.value[value] = {};
  lvlTwo.value[value][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumTotalFromVal = (value) => {
  const filteredData = dataHere.value.filter((x) => x.subsector_id == value);
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
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
  const filteredData = dataHere.value.filter((x) => x.quarter == quarter);
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB"]) lvlPDRB.value["PDRB"] = {};
  lvlPDRB.value["PDRB"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getPDRBNonMigas = (quarter) => {
  const filteredData = dataHere.value.filter(
    (x) => x.quarter == quarter && ![10, 15].includes(Number(x.subsector_id))
  );
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
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

const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
// #endregion

// #region Section: CAPTURE_DATA
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
// #endregion
</script>
