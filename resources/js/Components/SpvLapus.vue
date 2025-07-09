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
          <template v-for="(node, indRegion) in regions" :key="indRegion">
            <td class="text-right font-bold">
              {{ getData(nodeSubsectors.sector.category.id, null, null, node, true) }}
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
          <template v-for="(node, indRegion) in regions" :key="indRegion">
            <td class="text-right pr-2">
              {{
                getData(
                  nodeSubsectors.sector.category.id,
                  nodeSubsectors.sector.id,
                  null,
                  node,
                  true
                )
              }}
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
          <template v-for="(node, indRegion) in regions" :key="indRegion">
            <td>
              {{
                getData(
                  nodeSubsectors.sector.category.id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node,
                  true
                )
              }}
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
          <template v-for="(node, indRegion) in regions" :key="indRegion">
            <td>
              {{
                getData(
                  nodeSubsectors.sector.category.id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node,
                  true
                )
              }}
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
          <template v-for="(node, indRegion) in regions" :key="indRegion">
            <td class="font-bold">
              {{
                getData(
                  nodeSubsectors.sector.category.id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node,
                  true
                )
              }}
            </td>
          </template>
        </tr>
      </template>
    </template>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB</p>
      </td>
      <template v-for="(node, indRegion) in regions" :key="indRegion">
        <td class="total-cell">
          {{ getData(98, 98, 98, node) }}
        </td>
      </template>
    </tr>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB Nonmigas</p>
      </td>
      <template v-for="(node, indRegion) in regions" :key="indRegion">
        <td class="total-cell">{{ getPDRBNonMigas(98, 98, 98, node) }}</td>
      </template>
    </tr>
  </tbody>
</template>
<script setup>
import { ref } from "vue";

const props = defineProps({
  subsectors: {
    type: Array,
    required: true,
  },
  regions: {
    type: Array,
    required: true,
  },
  data: {
    type: Array,
    required: true,
  },
  tab: {
    type: String,
    required: false,
  },
});
const tableRef = ref(null);
const getData = (category_id, sector_id, subsector_id, region, formatted = true) => {
  let type = props.tab;
  let result;
  if (region.value != "total" && region.value != "calculate") {
    const theData = props.data.find((x) => {
      return (
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id &&
        x.region_id == region.id
      );
    });
    result = formatted ? formatNumberGerman(theData[type], 2, 2) : theData[type];
  } else if (region.value == "total") {
    const theData = props.data.find((x) => {
      return (
        x.region_id == 17 &&
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id
      );
    });
    result = formatted ? formatNumberGerman(theData[type], 2, 2) : theData[type];
  } else if (region.value == "calculate") {
    const provData = props.data.find((x) => {
      return (
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id &&
        x.region_id == 1
      );
    });
    const filteredData = props.data.find((x) => {
      return (
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id &&
        x.region_id == 17
      );
    });
    if (type == "adhb" || type == "adhk") {
      if (filteredData) {
        let totalKabkot = Number(filteredData[type]);
        let prov = Number(provData[type]);
        let selisih = prov - totalKabkot;
        let disk = selisih != 0 && prov != 0 ? (selisih / prov) * 100 : 0;
        return formatted ? formatNumberGerman(disk, 2, 4) : disk;
      }
    } else {
      if (filteredData) {
        let totalKabkot = Number(filteredData[type]);
        let prov = Number(provData[type]);
        let selisih = prov - totalKabkot;
        return formatted ? formatNumberGerman(selisih, 2, 4) : selisih;
      }
    }
  }
  return result;
};
const getPDRBNonMigas = (category_id, sector_id, subsector_id, region) => {
  let total = getData(category_id, sector_id, subsector_id, region, false);
  let subTen = getData(2, 4, 10, region, false);
  let subFifteen = getData(3, 8, 15, region, false);
  let nonmigas = total - subTen - subFifteen;
  return formatNumberGerman(nonmigas, 2, 4);
};
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const changeColor = () => {
  const rows = tableRef.value.querySelectorAll("tr");
  rows.forEach((row) => {
    const parseNumber = (value) =>
      value ? Number(value.replaceAll(".", "").replaceAll(",", ".")) : 0;
    const targets = row.querySelector("td:nth-child(2)");
    const prov = row.querySelector("td:nth-child(3)");
    const total = row.querySelector("td:nth-child(4)");
    if (targets) {
      let cek = parseNumber(targets.textContent);
      const selisihCell = row.querySelector("td:nth-child(2)");
      selisihCell.classList.remove("text-red-500", "text-yellow-500", "text-black");
      if (Math.abs(cek) > 5) selisihCell.classList.add("text-red-500");
      else if (Math.abs(cek) > 2) selisihCell.classList.add("text-yellow-500");
      else selisihCell.classList.add("text-black");

      if (
        (parseNumber(prov.textContent) > 0 && parseNumber(total.textContent) < 0) ||
        (parseNumber(prov.textContent) < 0 && parseNumber(total.textContent) > 0)
      ) {
        selisihCell.textContent =
          "(Beda Arah) " + formatNumberGerman(cek.toFixed(4), 2, 4);
      } else selisihCell.textContent = formatNumberGerman(cek.toFixed(4), 2, 4);
    }
  });
};
defineExpose({
  changeColor,
});
</script>
<style lang="css" scoped>
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
