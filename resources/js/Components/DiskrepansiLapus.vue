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
                  diskreData?.rows?.['cat-' + nodeSubsectors.sector.category_id]?.disk ??
                    0
                )
              "
            >
              {{
                formatNumberGerman(
                  diskreData?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.disk ??
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
                  diskreData?.rows?.['sec-' + nodeSubsectors.sector_id]?.disk ?? 0
                )
              "
            >
              {{
                formatNumberGerman(
                  diskreData?.rows?.["sec-" + nodeSubsectors.sector_id]?.disk ?? 0,
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
                classCalculate(diskreData?.rows?.['sub-' + nodeSubsectors.id]?.disk ?? 0)
              "
            >
              {{
                formatNumberGerman(
                  diskreData?.rows?.["sub-" + nodeSubsectors.id]?.disk ?? 0,
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
                classCalculate(diskreData?.rows?.['sub-' + nodeSubsectors.id]?.disk ?? 0)
              "
            >
              {{
                formatNumberGerman(
                  diskreData?.rows?.["sub-" + nodeSubsectors.id]?.disk ?? 0,
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
                  diskreData?.rows?.['cat-' + nodeSubsectors.sector.category_id]?.disk ??
                    0
                )
              "
            >
              {{
                formatNumberGerman(
                  diskreData?.rows?.["cat-" + nodeSubsectors.sector.category_id]?.disk ??
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
          :class="classCalculate(diskreData?.footer?.['PDRB']?.disk ?? 0)"
        >
          {{
            formatNumberGerman(diskreData?.footer?.["PDRB"]?.disk ?? 0, 0, props.toFixed)
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
          :class="classCalculate(diskreData?.footer?.['PDRB-NonMigas']?.disk ?? 0)"
        >
          {{
            formatNumberGerman(
              diskreData?.footer?.["PDRB-NonMigas"]?.disk ?? 0,
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
import { computed, onMounted, ref, watch } from "vue";

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
    type: Array,
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
  regions: {
    type: Array,
    required: false,
    default: [],
  },
  toFixed: {
    type: Number,
    required: false,
    default: 4,
  },
});
const dataHere = ref(props.dataContents);
const quarters = ref(props.quarter);
const dataRegion = (r) => {
  const result = dataHere.value.filter((x) => x.region_id == r);
  return result;
};
const isNow = computed(() => String(props.onDemandType || "").includes("_now"));
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
const emits = defineEmits(["update:updateDOD"]);
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
const formatNumberGerman = (num, min = 2, max = 5) => {
  const threshold = 0.0001;

  if (Math.abs(num) < threshold) {
    let numb = "~0";
    if (num > 0) return numb;
    if (num < 0) return "~(-0)";
  }
  let result = new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
  return result;
};
//
const idx = computed(() => {
  const out = {};
  const regionKey = props.regions.map((r) => r.value);
  for (const rr of regionKey) out[rr] = {};
  out["total"] = {};

  for (const rr of regionKey) {
    for (const row of dataRegion(rr)) {
      const sid = Number(row.subsector_id);
      const q = String(row.quarter);
      const val = row[props.type];

      (out[rr][sid] ||= {})[q] = Number(val);
      if (Number(rr) != 1) {
        (out["total"][sid] ||= {})[q] = (out["total"][sid]?.[q] ?? 0) + Number(val);
      }
    }
  }
  return out;
});
const quartz = ["1", "2", "3", "4"];
const tableModel = computed(() => {
  const region = props.regions.map((x) => x.value);
  region.push("total");
  const subsectorsBySector = {};
  const subsectorsByCategory = {};
  const allSubsectorIds = new Set();
  for (const s of props.subsectors) {
    if (s.id) allSubsectorIds.add(Number(s.id));

    if (s.sector_id && s.id) {
      (subsectorsBySector[s.sector_id] ||= []).push(Number(s.id));
    }

    const catId = s?.sector?.category_id;
    if (catId && s.id) {
      (subsectorsByCategory[catId] ||= []).push(Number(s.id));
    }
  }
  const sumIds = (rr, ids) => {
    const q = quartz.map((qq) =>
      ids.reduce((acc, sid) => acc + (idx.value?.[rr]?.[sid]?.[qq] ?? 0), 0)
    );
    return { q, total: q.reduce((a, b) => a + b, 0) };
  };
  const model = {
    rows: {},
    footer: {},
  };
  for (const sid of allSubsectorIds) {
    const key = `sub-${sid}`;
    model.rows[key] = {};
    for (const rr of region) {
      const q = quartz.map((qq) => idx.value?.[rr]?.[sid]?.[qq] ?? 0);
      model.rows[key][rr] = { q, total: q.reduce((a, b) => a + b, 0) };
    }
  }
  for (const [sectorId, ids] of Object.entries(subsectorsBySector)) {
    const key = `sec-${sectorId}`;
    model.rows[key] = {};
    for (const rr of region) model.rows[key][rr] = sumIds(rr, ids);
  }
  for (const [catId, ids] of Object.entries(subsectorsByCategory)) {
    const key = `cat-${catId}`;
    model.rows[key] = {};
    for (const rr of region) model.rows[key][rr] = sumIds(rr, ids);
  }
  const allIds = Array.from(allSubsectorIds);
  model.footer["PDRB"] = {};
  for (const rr of region) model.footer["PDRB"][rr] = sumIds(rr, allIds);

  const nonmigasIds = allIds.filter((id) => ![10, 15].includes(Number(id)));
  model.footer["PDRB-NonMigas"] = {};
  for (const rr of region) model.footer["PDRB-NonMigas"][rr] = sumIds(rr, nonmigasIds);

  return model;
});
const pickQuarter = (cell, quarter) => {
  if (!cell) return 0;
  return quarter === "t" ? cell.total ?? 0 : cell.q?.[Number(quarter) - 1] ?? 0;
};
watch(
  () => tableModel.value,
  (value) => {
    emits("update:updateDOD", { data: value, type: props.onDemandType });
  }
);
const diskreData = computed(() => {
  const out = {
    rows: {},
    footer: {},
  };
  for (const [rK, rr] of Object.entries(tableModel.value?.rows ?? {})) {
    const prov = pickQuarter(rr?.[1], props.quarter);
    const total = pickQuarter(rr?.["total"], props.quarter);

    const diff = prov - total;
    const disk = diff != 0 && prov != 0 ? (diff / prov) * 100 : 0;
    out.rows[rK] = {
      prov,
      total,
      diff,
      disk,
    };
  }
  for (const [fK, rr] of Object.entries(tableModel.value?.footer ?? {})) {
    const prov = pickQuarter(rr?.[1], props.quarter);
    const total = pickQuarter(rr?.["total"], props.quarter);

    const diff = prov - total;
    const disk = diff != 0 && prov != 0 ? (diff / prov) * 100 : 0;
    out.footer[fK] = {
      prov,
      total,
      diff,
      disk,
    };
  }
  return out;
});
watch(
  () => diskreData.value,
  (value) => {
    if (isNow) {
      const types = props.onDemandType + "_disk";
      emits("update:updateDOD", { data: value, type: types });
    }
  }
);
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
