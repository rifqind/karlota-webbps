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
        <tr>
          <td class="desc-col fixed-column">
            <label class=""
              >{{ nodeSubsectors.sector.code }}. {{ nodeSubsectors.sector.name }}</label
            >
          </td>
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="
                classCalculate(
                  nodeSubsectors.sector.code + '.' + nodeSubsectors.sector.name
                )
              "
            >
              {{
                getCalculate(
                  nodeSubsectors.sector.code + "." + nodeSubsectors.sector.name
                )
              }}
            </td>
            <td v-else class="text-right font-bold">
              {{ getSumLvlTwo(nodeSubsectors.sector.id, node.value) }}
            </td>
          </template>
        </tr>
      </template>
      <template
        v-if="
          nodeSubsectors.code != null &&
          nodeSubsectors.sector.category.type == 'Pengeluaran'
        "
      >
        <tr>
          <td class="desc-col fixed-column">
            <p class="pl-5 pr-4" :for="nodeSubsectors.code + '_' + nodeSubsectors.name">
              {{ nodeSubsectors.code + ". " + nodeSubsectors.name }}
            </p>
          </td>
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="classCalculate(nodeSubsectors.code + '.' + nodeSubsectors.name)"
            >
              {{ getCalculate(nodeSubsectors.code + ". " + nodeSubsectors.name) }}
            </td>
            <td v-else>
              {{ getData(nodeSubsectors.id, node.value) }}
            </td>
          </template>
        </tr>
      </template>
      <template
        v-else-if="
          nodeSubsectors.code == null &&
          nodeSubsectors.sector.code != null &&
          nodeSubsectors.sector.category.type == 'Pengeluaran'
        "
      >
        <tr>
          <td class="desc-col fixed-column">
            <label :for="nodeSubsectors.sector.code + '_' + nodeSubsectors.sector.name">
              {{ nodeSubsectors.sector.code + ". " + nodeSubsectors.sector.name }}
            </label>
          </td>
          <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="
                classCalculate(
                  nodeSubsectors.sector.code + '.' + nodeSubsectors.sector.name
                )
              "
            >
              {{
                getCalculate(
                  nodeSubsectors.sector.code + ". " + nodeSubsectors.sector.name
                )
              }}
            </td>
            <td v-else class="font-bold text-right">
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
        <td v-if="node.value == 'calculate'" class="total-cell">
          {{ getCalculate("PDRB") }}
        </td>
        <td v-else :id="'adhb_total-' + node.value" class="total-cell">
          {{ getPDRB(node.value) }}
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
  calculate: {
    type: Object || Array,
    required: false,
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
    if (regions == "total") {
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
        (regions == "total" ? x.region_id != 1 : x.region_id == regions) &&
        x.subsector_id == subsectors
    );
    let result;
    if (filteredData)
      result = filteredData.reduce((sum, item) => sum + Number(item[props.type] || 0), 0);
    return formatNumberGerman(result, 0, 9);
  }
};
const getSumLvlTwo = (value, region_id) => {
  let subsectorIds = props.subsectors
    .filter((x) => x.sector.id == value)
    .map((x) => x.id);
  let filteredData, exportFilter, importFilter, result;
  if (value == 54) {
    if (quarters.value == "t") {
      exportFilter = dataHere.value.filter(
        (x) =>
          (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
          subsectorIds[0] == x.subsector_id
      );
      importFilter = dataHere.value.filter(
        (x) =>
          (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
          subsectorIds[1] == x.subsector_id
      );
    } else {
      exportFilter = dataHere.value.filter(
        (x) =>
          (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
          subsectorIds[0] == x.subsector_id &&
          x.quarter == quarters.value
      );
      importFilter = dataHere.value.filter(
        (x) =>
          (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
          subsectorIds[1] == x.subsector_id &&
          x.quarter == quarters.value
      );
    }
    let exportValue = exportFilter.reduce(
      (sum, item) => sum + Number(item[props.type]),
      0
    );
    let importValue = importFilter.reduce(
      (sum, item) => sum + Number(item[props.type]),
      0
    );
    result = exportValue - importValue;
  } else {
    filteredData = dataHere.value.filter(
      (x) =>
        (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
        subsectorIds.includes(x.subsector_id) &&
        x.quarter == quarters.value
    );
    if (quarters.value == "t") {
      filteredData = dataHere.value.filter(
        (x) =>
          (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
          subsectorIds.includes(x.subsector_id)
      );
    }
    result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  }

  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const getPDRB = (region_id) => {
  let filteredData, importData;

  if (quarters.value == "t") {
    filteredData = dataHere.value.filter(
      (x) =>
        (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
        ![69].includes(x.subsector_id)
    );
    importData = dataHere.value.filter(
      (x) =>
        (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
        x.subsector_id == 69
    );
  } else {
    filteredData = dataHere.value.filter(
      (x) =>
        (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
        x.quarter == quarters.value &&
        ![69].includes(x.subsector_id)
    );
    importData = dataHere.value.filter(
      (x) =>
        (region_id == "total" ? x.region_id != 1 : x.region_id == region_id) &&
        x.quarter == quarters.value &&
        x.subsector_id == 69
    );
  }
  let result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  let importValue = importData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  result -= importValue;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};
const getCalculate = (keys) => {
  let trimmedKeys = keys.trim().replace(/\s+/g, "");
  if (props.calculate) {
    let objectLength;
    objectLength = Object.entries(props.calculate).length;
    if (objectLength > 0) {
      let data = Object.entries(props.calculate).find(
        ([key, value]) => key == trimmedKeys
      );
      return data[1][1];
    }
  }
};
const classCalculate = (keys) => {
  let trimmedKeys = keys.trim().replace(/\s+/g, "");
  const parseNumber = (value) =>
    value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
  if (props.calculate) {
    let objectLength;
    objectLength = Object.entries(props.calculate).length;
    if (objectLength > 0) {
      let data = Object.entries(props.calculate).find(
        ([key, value]) => key == trimmedKeys
      );
      let colors = parseNumber(data[1][1]);
      if (Math.abs(colors) > 5) {
        return "text-red-500";
      }
      if (Math.abs(colors) > 2) {
        return "text-yellow-500";
      }
      if (colors) {
        return "text-black";
      }
    }
  }
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
