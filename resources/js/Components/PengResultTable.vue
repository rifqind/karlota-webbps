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
            <label
              >{{ nodeSubsectors.sector.code }}. {{ nodeSubsectors.sector.name }}</label
            >
          </td>
          <template v-for="(node, index) in quarters">
            <td class="text-right"></td>
          </template>
          <td class="text-right"></td>
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
          <template v-for="(node, index) in quarters">
            <td class="text-right"></td>
          </template>
          <td class="text-right"></td>
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
          <template v-for="(node, index) in quarters">
            <td class="text-right"></td>
          </template>
          <td class="text-right"></td>
        </tr>
      </template>
    </template>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB</p>
      </td>
      <template v-for="(node, index) in quarters">
        <td :id="'adhb_total-' + node.label" class="total-cell"></td>
      </template>
      <td class="total-cell"></td>
    </tr>
  </tbody>
</template>

<script setup>
import { debounce } from "@/debounce";
import { onMounted, ref, watch } from "vue";

const props = defineProps({
  subsectors: {
    type: Object,
    required: true,
  },
  computedData: {
    type: Object,
    required: true,
  },
  quarterCap: {
    type: String,
    required: true,
  },
  //   dataContents: {
  //     type: Object,
  //     required: true,
  //   },
  type: {
    type: String,
    required: true,
    default: "distribusi",
  },
});
const quarters = [{ label: "1" }, { label: "2" }, { label: "3" }, { label: "4" }];
const thisData = ref(props.computedData);
const tableRef = ref(null);
watch(
  () => props.computedData,
  (value) => {
    thisData.value = value;
    if (tableRef.value) {
      const rows = tableRef.value.querySelectorAll("tr");

      rows.forEach((row) => {
        const firstCell = row.querySelector("td:first-child"); // Get the first column (key)
        if (!firstCell) return;
        const key = firstCell.textContent.trim().replace(/\s+/g, ""); // Key from first column
        if (thisData.value[key]) {
          const cells = row.querySelectorAll("td:not(:first-child)"); // Get other columns
          // Update the row with the computed values
          cells.forEach((cell, index) => {
            cell.textContent = thisData.value[key][index]; // Format and insert value
          });
        }
      });
    }
  }
);
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
