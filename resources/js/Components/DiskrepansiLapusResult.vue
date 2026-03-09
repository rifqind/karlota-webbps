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
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="
                classCalculate(
                  resultDatas?.rows?.['cat-' + nodeSubsectors.sector.category_id]?.diff ??
                    0
                )
              "
            >
              {{
                resultDatas?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.note ??
                ""
              }}
              {{
                formatNumberGerman(
                  resultDatas?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.diff ??
                    0,
                  0,
                  props.toFixed
                )
              }}
            </td>
            <td v-else class="text-right font-bold">
              {{
                formatNumberGerman(
                  pickQuarter(
                    tableModel?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.[
                      node.value
                    ],
                    props.quarter
                  ),
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
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="
                classCalculate(
                  resultDatas?.rows?.['sec-' + nodeSubsectors.sector_id]?.diff ?? 0
                )
              "
            >
              {{ resultDatas?.rows?.["sec-" + nodeSubsectors.sector_id]?.note ?? "" }}
              {{
                formatNumberGerman(
                  resultDatas?.rows?.["sec-" + nodeSubsectors.sector_id]?.diff ?? 0,
                  0,
                  props.toFixed
                )
              }}
            </td>
            <td v-else class="text-right pr-2">
              {{
                formatNumberGerman(
                  pickQuarter(
                    tableModel?.rows?.["sec-" + nodeSubsectors.sector_id]?.[node.value],
                    props.quarter
                  ),
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
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="
                classCalculate(resultDatas?.rows?.['sub-' + nodeSubsectors.id]?.diff ?? 0)
              "
            >
              {{ resultDatas?.rows?.["sub-" + nodeSubsectors.id]?.note ?? "" }}
              {{
                formatNumberGerman(
                  resultDatas?.rows?.["sub-" + nodeSubsectors.id]?.diff ?? 0,
                  0,
                  props.toFixed
                )
              }}
            </td>
            <td v-else>
              {{
                formatNumberGerman(
                  pickQuarter(
                    tableModel?.rows?.["sub-" + nodeSubsectors.id]?.[node.value],
                    props.quarter
                  ),
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
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="
                classCalculate(resultDatas?.rows?.['sub-' + nodeSubsectors.id]?.diff ?? 0)
              "
            >
              {{ resultDatas?.rows?.["sub-" + nodeSubsectors.id]?.note ?? "" }}
              {{
                formatNumberGerman(
                  resultDatas?.rows?.["sub-" + nodeSubsectors.id]?.diff ?? 0,
                  0,
                  props.toFixed
                )
              }}
            </td>
            <td v-else>
              {{
                formatNumberGerman(
                  pickQuarter(
                    tableModel?.rows?.["sub-" + nodeSubsectors.id]?.[node.value],
                    props.quarter
                  ),
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
            <td
              v-if="node.value == 'calculate'"
              class="text-right font-bold"
              :class="
                classCalculate(
                  resultDatas?.rows?.['cat-' + nodeSubsectors.sector.category_id]?.diff ??
                    0
                )
              "
            >
              {{
                resultDatas?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.note ??
                ""
              }}
              {{
                formatNumberGerman(
                  resultDatas?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.diff ??
                    0,
                  0,
                  props.toFixed
                )
              }}
            </td>
            <td v-else class="font-bold">
              {{
                formatNumberGerman(
                  pickQuarter(
                    tableModel?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.[
                      node.value
                    ],
                    props.quarter
                  ),
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
      <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
        <td
          v-if="node.value == 'calculate'"
          class="total-cell"
          :class="classCalculate(resultDatas?.footer?.['PDRB']?.diff ?? 0)"
        >
          {{ resultDatas?.footer?.["PDRB"]?.note ?? "" }}
          {{
            formatNumberGerman(resultDatas?.footer?.["PDRB"]?.diff ?? 0, 0, props.toFixed)
          }}
        </td>
        <td v-else :id="'adhb_total-' + node.value" class="total-cell">
          {{
            formatNumberGerman(
              pickQuarter(tableModel?.footer?.["PDRB"]?.[node.value], props.quarter),
              0,
              props.toFixed
            )
          }}
        </td>
      </template>
    </tr>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB Nonmigas</p>
      </td>
      <template v-for="(node, indRegion) in tableColumn" :key="indRegion">
        <td
          v-if="node.value == 'calculate'"
          class="total-cell"
          :class="classCalculate(resultDatas?.footer?.['PDRB-NonMigas']?.diff ?? 0)"
        >
          {{ resultDatas?.footer?.["PDRB-NonMigas"]?.note ?? "" }}
          {{
            formatNumberGerman(
              resultDatas?.footer?.["PDRB-NonMigas"]?.diff ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
        <td v-else :id="'adhb_total-nonmigas-' + node.label" class="total-cell">
          {{
            formatNumberGerman(
              pickQuarter(
                tableModel?.footer?.["PDRB-NonMigas"]?.[node.value],
                props.quarter
              ),
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
import { ref, watch, computed } from "vue";

const props = defineProps({
  subsectors: {
    type: Object,
    required: true,
  },
  tableColumn: {
    type: Object,
    required: true,
  },
  computedData: {
    type: Object,
    required: true,
  },
  quarter: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    required: true,
    default: "distribusi",
  },
});
const tableModel = ref(props.computedData);
const quarters = ref(props.quarter);
watch(
  () => props.quarter,
  (value) => {
    quarters.value = value;
  }
);
watch(
  () => props.computedData,
  (value) => {
    tableModel.value = value;
  }
);
const classCalculate = (colors) => {
  if (Math.abs(colors) > 5) {
    return "text-red-500";
  }
  if (Math.abs(colors) > 2) {
    return "text-yellow-500";
  }
  if (colors) {
    return "text-black";
  }
};
const pickQuarter = (cell, quarter) => {
  if (!cell) return 0;
  return quarter === "t" ? cell.total ?? 0 : cell.q?.[Number(quarter) - 1] ?? 0;
};
const resultDatas = computed(() => {
  const out = {
    rows: {},
    footer: {},
  };
  const bedaArah = (p, t) => p * t < 0;
  for (const [rK, rr] of Object.entries(tableModel.value?.rows ?? {})) {
    const prov = pickQuarter(rr?.[1], props.quarter);
    const total = pickQuarter(rr?.["total"], props.quarter);

    const diff = Math.abs(prov - total);
    const disk = diff != 0 && prov != 0 ? (diff / prov) * 100 : 0;
    const note = bedaArah(prov, total);
    out.rows[rK] = {
      prov,
      total,
      diff,
      disk,
      note: note ? "(beda arah)" : "",
    };
  }
  for (const [fK, rr] of Object.entries(tableModel.value?.footer ?? {})) {
    const prov = pickQuarter(rr?.[1], props.quarter);
    const total = pickQuarter(rr?.["total"], props.quarter);

    const diff = Math.abs(prov - total);
    const disk = diff != 0 && prov != 0 ? (diff / prov) * 100 : 0;
    const note = bedaArah(prov, total);
    out.footer[fK] = {
      prov,
      total,
      diff,
      disk,
      note: note ? "(beda arah)" : "",
    };
  }
  return out;
});
const emits = defineEmits(["update:updateDOD"]);
watch(
  () => resultDatas.value,
  (value) => {
    emits("update:updateDOD", { data: value, type: "computed_diff" });
  }
);
const formatNumberGerman = (num, min = 2, max = 5) => {
  const threshold = 0.0001;

  if (Math.abs(num) < threshold) {
    let numb = "~0";
    if (num > 0) return numb;
    if (num < 0) return "~(-0)";
  }
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

tbody tr td:not(:nth-child(1)) {
  text-align: right;
}
tbody tr td {
  padding: 0.25rem;
}
</style>
