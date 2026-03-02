<template>
  <tbody ref="tableRef">
    <tr>
      <td class="fixed-column">
        <label>Primer</label>
      </td>
      <template v-for="yr in props.yearsToRender" :key="'primer-' + yr">
        <td v-for="(node, index) in quarters" class="text-right">
          <!-- {{ getData("primer", node.label) }} -->
          {{
            formatNumberGerman(
              tableModel.rows?.["primer"]?.[String(yr)]?.q?.[Number(node) - 1] ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
        <td class="text-right">
          <!-- {{ getSumTotalFromVal("primer") }} -->
          {{
            formatNumberGerman(
              tableModel.rows?.["primer"]?.[String(yr)]?.total ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
      </template>
    </tr>
    <tr>
      <td class="fixed-column">
        <label>Sekunder</label>
      </td>
      <template v-for="yr in props.yearsToRender" :key="'sekunder-' + yr">
        <td v-for="(node, index) in quarters" class="text-right">
          <!-- {{ getData("sekunder", node.label) }} -->
          {{
            formatNumberGerman(
              tableModel.rows?.["sekunder"]?.[String(yr)]?.q?.[Number(node) - 1] ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
        <td class="text-right">
          <!-- {{ getSumTotalFromVal("sekunder") }} -->
          {{
            formatNumberGerman(
              tableModel.rows?.["sekunder"]?.[String(yr)]?.total ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
      </template>
    </tr>
    <tr>
      <td class="fixed-column">
        <label>Tersier</label>
      </td>
      <template v-for="yr in props.yearsToRender" :key="'tersier-' + yr">
        <td v-for="(node, index) in quarters" class="text-right">
          <!-- {{ getData("tersier", node.label) }} -->
          {{
            formatNumberGerman(
              tableModel.rows?.["tersier"]?.[String(yr)]?.q?.[Number(node) - 1] ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
        <td class="text-right">
          <!-- {{ getSumTotalFromVal("tersier") }} -->
          {{
            formatNumberGerman(
              tableModel.rows?.["tersier"]?.[String(yr)]?.total ?? 0,
              0,
              props.toFixed
            )
          }}
        </td>
      </template>
    </tr>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB</p>
      </td>
      <template v-for="yr in props.yearsToRender" :key="'pdrb-' + yr">
        <template v-for="(node, index) in quarters">
          <td :id="'adhb_total-' + node.label" class="total-cell">
            <!-- {{ getPDRB(node.label) }} -->
            {{
              formatNumberGerman(
                tableModel.footer?.["PDRB"]?.[String(yr)]?.q?.[Number(node) - 1] ?? 0,
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
              tableModel.footer?.["PDRB"]?.[String(yr)]?.total ?? 0,
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
// const dataHere = ref(props.dataContents);
const dataHere = ref(props.dataByYears);
const isNow = computed(() => String(props.onDemandType || "").includes("_now"));
const pack = (yr) => dataHere.value[yr] ?? {};
const seriesOfData = (yr) => {
  const p = pack(yr);
  return isNow.value ? p.dataContentsSummary ?? [] : p.dataBeforeSummary ?? [];
};
const tableRef = ref(null);
// watch(
//   () => props.dataContents,
//   (value) => {
//     dataHere.value = value;
//   }
// );
watch(
  () => props.dataByYears,
  (value) => {
    dataHere.value = value;
  }
);
var observer = null;
const emits = defineEmits(["update:updateDOD"]);
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
const idx = computed(() => {
  const out = {};
  for (const yr of props.yearsToRender) {
    const y = String(yr);
    out[y] = {};
    for (const row of seriesOfData(y)) {
      const setdata = row.setdata;
      const q = String(row.quarter);
      const val = row[props.type];
      (out[y][setdata] ||= {})[q] = Number(val);
    }
  }
  return out;
});
const quarters = ["1", "2", "3", "4"];
const tableModel = computed(() => {
  const years = props.yearsToRender.map(String);
  const sums = (y, setdata) => {
    const q = quarters.map((qq) =>
      setdata.reduce((acc, sets) => acc + (idx.value?.[y]?.[sets]?.[qq] ?? 0), 0)
    );
    return { q, total: q.reduce((a, b) => a + b, 0) };
  };
  const model = {
    rows: {},
    footer: {},
  };
  for (const sets of ["primer", "sekunder", "tersier"]) {
    const key = `${sets}`;
    model.rows[key] = {};
    for (const y of years) {
      const q = quarters.map((qq) => idx.value[y]?.[sets]?.[qq] ?? 0);
      model.rows[key][y] = { q, total: q.reduce((a, b) => a + b, 0) };
    }
  }
  model.footer["PDRB"] = {};
  for (const y of years)
    model.footer["PDRB"][y] = sums(y, ["primer", "sekunder", "tersier"]);
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
