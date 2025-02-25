<template>
  <tbody ref="tableRef">
    <tr>
      <td class="fixed-column">
        <label>Konsumsi Akhir Non Publik</label>
      </td>
      <td v-for="(node, index) in quarters" class="text-right">
        {{ getData("kanp", node.label) }}
      </td>
      <td class="text-right">{{ getSumTotalFromVal("kanp") }}</td>
    </tr>
    <tr>
      <td class="fixed-column">
        <label>Konsumsi Akhir Publik</label>
      </td>
      <td v-for="(node, index) in quarters" class="text-right">
        {{ getData("kap", node.label) }}
      </td>
      <td class="text-right">{{ getSumTotalFromVal("kap") }}</td>
    </tr>
    <tr>
      <td class="fixed-column">
        <label>Permintaan Akhir Institusi</label>
      </td>
      <td v-for="(node, index) in quarters" class="text-right">
        {{ getData("pai", node.label) }}
      </td>
      <td class="text-right">{{ getSumTotalFromVal("pai") }}</td>
    </tr>
    <tr>
      <td class="fixed-column">
        <label>Lainnya</label>
      </td>
      <td v-for="(node, index) in quarters" class="text-right">
        {{ getData("net_export_import", node.label) }}
      </td>
      <td class="text-right">{{ getSumTotalFromVal("net_export_import") }}</td>
    </tr>
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
  </tbody>
</template>
<script setup>
import { onMounted, ref, watch } from "vue";
const props = defineProps({
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
  quarterCap: {
    type: String,
    required: true,
  },
});
const dataHere = ref(props.dataContents);
const tableRef = ref(null);
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
const emits = defineEmits(["update:updateDOD"]);
const quarters = [{ label: "1" }, { label: "2" }, { label: "3" }, { label: "4" }];
const getData = (type, quarter) => {
  const theData = dataHere.value.find((x) => {
    return x.quarter == quarter && x.setdata == type;
  });
  if (theData) {
    let formattedResult;
    formattedResult =
      theData[props.type] == "" || theData[props.type] == null
        ? null
        : formatNumberGerman(Number(theData[props.type]), 0, 9);
    return formattedResult;
  }
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
const getSumPDRB = (pdrb) => {
  if (!lvlPDRB.value[pdrb]) return 0;

  let totalSum = Object.values(lvlPDRB.value[pdrb]).reduce(
    (sum, pdrbSum) => sum + pdrbSum,
    0
  );
  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};
const getSumTotalFromVal = (value) => {
  const filteredData = dataHere.value.filter((x) => x.setdata == value);
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  // console.log(result);
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
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
const formatNumberGerman = (num, min = 2, max = 5) => {
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

.not-fixed {
  min-width: 250px;
}
</style>
