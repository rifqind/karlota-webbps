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
          <template
            v-for="yr in props.yearsToRender"
            :key="'sec-' + nodeSubsectors.sector.id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right">
                <!-- {{ getSumLvlTwo(nodeSubsectors.sector.id, node.label) }} -->
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
              <!-- {{ getSumRowCat(nodeSubsectors.sector.id) }} -->
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
                <!-- {{ getData(nodeSubsectors.id, node.label) }} -->
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
              <!-- {{ getSumTotalFromVal(nodeSubsectors.id) }} -->
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
                <!-- {{ getData(nodeSubsectors.id, node.label) }} -->
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
              <!-- {{ getSumTotalFromVal(nodeSubsectors.id) }} -->
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
            <!-- {{ getPDRB(node.label) }} -->
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
          <!-- {{ getSumPDRB("PDRB") }} -->
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
import { computed, onMounted, ref, watch } from "vue";

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
    default: "adhb_now",
  },
  quarterCap: {
    type: String,
    required: true,
  },
  dataByYears: {
    type: Object,
    required: true,
  },
  yearsToRender: {
    type: Array,
    required: true,
    default: () => [new Date().getFullYear()],
  },
  toFixed: {
    type: Number,
    required: false,
    default: 4,
  },
});
const dataHere = ref(props.dataByYears);
const isNow = computed(() => String(props.onDemandType || "").includes("_now"));
const pack = (yr) => dataHere.value[yr] ?? {};
const seriesOfData = (yr) => {
  const p = pack(yr);
  return isNow.value ? p.dataContents ?? [] : p.dataBefore ?? [];
};
const tableRef = ref(null);
watch(
  () => props.dataByYears,
  (value) => {
    dataHere.value = value;
  }
);
const emits = defineEmits(["update:updateDOD"]);
// const quarters = [{ label: "1" }, { label: "2" }, { label: "3" }, { label: "4" }];
// #region Section: GET_DATA

const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
// #endregion

// #region Section: CAPTURE_DATA
// #endregion

//
const idx = computed(() => {
  const out = {};
  for (const yr of props.yearsToRender) {
    const y = String(yr);
    out[y] = {};
    for (const row of seriesOfData(y)) {
      const sid = Number(row.subsector_id);
      const q = String(row.quarter);
      const val = row[props.type];
      (out[y][sid] ||= {})[q] = Number(val);
    }
  }
  return out;
});
const quarters = ["1", "2", "3", "4"];
const tableModel = computed(() => {
  const years = props.yearsToRender.map(String);

  // 1) mapping keanggotaan subsector per sector/category
  const subsectorsBySector = {};
  const allSubsectorIds = new Set();

  for (const s of props.subsectors) {
    if (s.id) allSubsectorIds.add(Number(s.id));

    if (s.sector_id && s.id) {
      (subsectorsBySector[s.sector_id] ||= []).push(Number(s.id));
    }
  }

  // helper: sum list subsector ids
  const sumIds = (y, ids) => {
    const q = quarters.map((qq) =>
      ids.reduce((acc, sid) => acc + (idx.value?.[y]?.[sid]?.[qq] ?? 0), 0)
    );
    return { q, total: q.reduce((a, b) => a + b, 0) };
  };

  const model = {
    // value per baris keyed
    rows: {}, // rows[rowKey][year] = {q,total}
    footer: {}, // footer[name][year] = {q,total}
  };

  for (const sid of allSubsectorIds) {
    const key = `sub-${sid}`;
    model.rows[key] = {};
    for (const y of years) {
      const q = quarters.map((qq) => idx.value?.[y]?.[sid]?.[qq] ?? 0);
      model.rows[key][y] = { q, total: q.reduce((a, b) => a + b, 0) };
    }
  }

  for (const [sectorId, ids] of Object.entries(subsectorsBySector)) {
    const key = `sec-${sectorId}`;
    model.rows[key] = {};
    for (const y of years) {
      if (sectorId == 54) {
        for (const y of years) {
          const q = quarters.map((qq) => {
            const qResult =
              (idx.value?.[y]?.[ids[0]]?.[qq] ?? 0) -
              (idx.value?.[y]?.[ids[1]]?.[qq] ?? 0);
            return qResult;
          });
          model.rows[key][y] = { q, total: q.reduce((a, b) => a + b, 0) };
        }
      } else model.rows[key][y] = sumIds(y, ids);
    }
  }

  const allIds = Array.from(allSubsectorIds).filter((id) => ![69].includes(Number(id)));
  model.footer["PDRB"] = {};
  for (const y of years) {
    const q = quarters.map((qq) => {
      const pdrbs = allIds.reduce(
        (acc, sid) => acc + (idx.value?.[y]?.[sid]?.[qq] ?? 0),
        0
      );
      const imports = idx.value?.[y]?.[69]?.[qq] ?? 0;
      const qResult = pdrbs - imports;
      return qResult;
    });
    model.footer["PDRB"][y] = { q, total: q.reduce((a, b) => a + b, 0) };
  }
  return model;
});
watch(
  () => tableModel.value,
  (value) => {
    emits("update:updateDOD", { data: value, type: props.onDemandType });
  },
  { immediate: true }
);
</script>

<style scoped>
.fixed-column {
  position: sticky;
  min-width: 250px;
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
  width: 250px;
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
  /* white-space: nowrap; */
}

tbody tr {
  height: 50px;
}

.not-fixed {
  min-width: 250px;
}
</style>
