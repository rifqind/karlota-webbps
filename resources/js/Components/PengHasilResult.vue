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
          <template
            v-for="yr in props.yearsToRender"
            :key="'sec-' + nodeSubsectors.sector.id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right">
                {{
                  formatNumberGerman(
                    tableModel.rows?.["sec-" + nodeSubsectors.sector.id]?.[String(yr)]
                      ?.q?.[Number(node - 1)] ?? 0,
                    0,
                    props.toFixed
                  )
                }}
              </td>
            </template>
            <td class="text-right">
              {{
                formatNumberGerman(
                  tableModel.rows?.["sec-" + nodeSubsectors.sector.id]?.[String(yr)]
                    ?.total ?? 0,
                  0,
                  props.toFixed
                )
              }}
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
          <template
            v-for="yr in props.yearsToRender"
            :key="'sub-' + nodeSubsectors.id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right">
                {{
                  formatNumberGerman(
                    tableModel.rows["sub-" + nodeSubsectors.id]?.[String(yr)]?.q?.[
                      Number(node) - 1
                    ] ?? 0,
                    0,
                    props.toFixed
                  )
                }}
              </td>
            </template>
            <td class="text-right">
              {{
                formatNumberGerman(
                  tableModel.rows?.["sub-" + nodeSubsectors.id]?.[String(yr)]?.total ?? 0,
                  0,
                  props.toFixed
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
          nodeSubsectors.sector.category.type == 'Pengeluaran'
        "
      >
        <tr>
          <td class="desc-col fixed-column">
            <label :for="nodeSubsectors.sector.code + '_' + nodeSubsectors.sector.name">
              {{ nodeSubsectors.sector.code + ". " + nodeSubsectors.sector.name }}
            </label>
          </td>
          <template
            v-for="yr in props.yearsToRender"
            :key="'sub-' + nodeSubsectors.id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right">
                {{
                  formatNumberGerman(
                    tableModel.rows?.["sub-" + nodeSubsectors.id]?.[String(yr)]?.q?.[
                      Number(node) - 1
                    ] ?? 0,
                    0,
                    props.toFixed
                  )
                }}
              </td>
            </template>
            <td class="text-right">
              {{
                formatNumberGerman(
                  tableModel.rows?.["sub-" + nodeSubsectors.id]?.[String(yr)]?.total ?? 0,
                  0,
                  props.toFixed
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
      <template v-for="yr in props.yearsToRender" :key="'q-' + yr">
        <template v-for="(node, index) in quarters">
          <td :id="'adhb_total-' + node.label" class="total-cell">
            {{
              formatNumberGerman(
                tableModel.footer["PDRB"]?.[String(yr)]?.q?.[Number(node) - 1] ?? 0,
                0,
                props.toFixed
              )
            }}
          </td>
        </template>
        <td class="total-cell">
          {{
            formatNumberGerman(
              tableModel.footer["PDRB"]?.[String(yr)]?.total ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
      </template>
    </tr>
  </tbody>
</template>

<script setup>
import { ref, watch } from "vue";

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
  type: {
    type: String,
    required: true,
    default: "distribusi",
  },
  toFixed: {
    type: Number,
    requried: false,
    default: 4,
  },
  yearsToRender: {
    type: Array,
    required: true,
    default: () => [new Date().getFullYear()],
  },
});
const quarters = ["1", "2", "3", "4"];
const tableModel = ref(props.computedData);
const tableRef = ref(null);
watch(
  () => props.computedData,
  (value) => {
    tableModel.value = value;
  }
);
const formatNumberGerman = (num, min = 2, max = 5) => {
  if (num == "qtoq" || num == "ctoc") return "";
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
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
