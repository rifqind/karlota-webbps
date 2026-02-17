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
          <template
            v-for="yr in props.yearsToRender"
            :key="'cat-' + nodeSubsectors.sector.category_id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right font-bold">
                <!-- {{ getSumLvlTwo(nodeSubsectors.sector.category_id, node.label, yr) }} -->
                {{
                  formatNumberGerman(
                    tableModel.rows["cat-" + nodeSubsectors.sector.category_id]?.[
                      String(yr)
                    ]?.q?.[node - 1] ?? 0,
                    0,
                    9
                  )
                }}
              </td>
            </template>
            <td class="text-right font-bold">
              <!-- {{ getSumRowCat(nodeSubsectors.sector.category_id, yr) }} -->
              {{
                formatNumberGerman(
                  tableModel.rows["cat-" + nodeSubsectors.sector.category_id]?.[
                    String(yr)
                  ]?.total ?? 0,
                  0,
                  9
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
          <template
            v-for="yr in props.yearsToRender"
            :key="'sec-' + nodeSubsectors.sector_id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right pr-2">
                <!-- {{ getSumLvlOne(nodeSubsectors.sector_id, node.label, yr) }} -->
                {{
                  formatNumberGerman(
                    tableModel.rows["sec-" + nodeSubsectors.sector_id]?.[String(yr)]?.q?.[
                      node - 1
                    ] ?? 0,
                    0,
                    9
                  )
                }}
              </td>
            </template>
            <td class="text-right">
              <!-- {{ getSumRowSector(nodeSubsectors.sector.id, yr) }} -->
              {{
                formatNumberGerman(
                  tableModel.rows["sec-" + nodeSubsectors.sector_id]?.[String(yr)]
                    ?.total ?? 0,
                  0,
                  9
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
          <template
            v-for="yr in props.yearsToRender"
            :key="'sub-' + nodeSubsectors.id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right">
                <!-- {{ getData(nodeSubsectors.id, node.label, yr) }} -->
                {{
                  formatNumberGerman(
                    tableModel.rows["sub-" + nodeSubsectors.id]?.[String(yr)]?.q?.[
                      node - 1
                    ] ?? 0,
                    0,
                    9
                  )
                }}
              </td>
            </template>
            <td class="text-right">
              {{
                formatNumberGerman(
                  tableModel.rows["sub-" + nodeSubsectors.id]?.[String(yr)]?.total ?? 0,
                  0,
                  9
                )
              }}
            </td>
            <!-- <td class="text-right">{{ getSumTotalFromVal(nodeSubsectors.id, yr) }}</td> -->
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
          <template
            v-for="yr in props.yearsToRender"
            :key="'sub-' + nodeSubsectors.id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right">
                {{
                  formatNumberGerman(
                    tableModel.rows["sub-" + nodeSubsectors.id]?.[String(yr)]?.q?.[
                      node - 1
                    ] ?? 0,
                    0,
                    9
                  )
                }}
                <!-- {{ getData(nodeSubsectors.id, node.label, yr) }} -->
              </td>
            </template>
            <td class="text-right">
              {{
                formatNumberGerman(
                  tableModel.rows["sub-" + nodeSubsectors.id]?.[String(yr)]?.total ?? 0,
                  0,
                  9
                )
              }}
            </td>
            <!-- <td class="text-right">{{ getSumTotalFromVal(nodeSubsectors.id, yr) }}</td> -->
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
          <template
            v-for="yr in props.yearsToRender"
            :key="'sub-' + nodeSubsectors.id + '-' + yr"
          >
            <template v-for="(node, index) in quarters">
              <td class="text-right font-bold">
                <!-- {{ getData(nodeSubsectors.id, node.label, yr) }} -->
                {{
                  formatNumberGerman(
                    tableModel.rows["sub-" + nodeSubsectors.id]?.[String(yr)]?.q?.[
                      node - 1
                    ] ?? 0,
                    0,
                    9
                  )
                }}
              </td>
            </template>
            <td class="text-right font-bold">
              {{
                formatNumberGerman(
                  tableModel.rows["sub-" + nodeSubsectors.id]?.[String(yr)]?.total ?? 0,
                  0,
                  9
                )
              }}
              <!-- {{ getSumTotalFromVal(nodeSubsectors.id, yr) }} -->
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
            <!-- {{ getPDRB(node.label, yr) }} -->
            {{
              formatNumberGerman(
                tableModel.footer["PDRB"]?.[String(yr)]?.q?.[node - 1] ?? 0,
                0,
                9
              )
            }}
          </td>
        </template>
        <!-- <td class="total-cell">{{ getSumPDRB("PDRB", yr) }}</td> -->
        <td class="total-cell">
          {{
            formatNumberGerman(tableModel.footer["PDRB"]?.[String(yr)]?.total ?? 0, 0, 9)
          }}
        </td>
      </template>
    </tr>
    <tr class="PDRB-footer text-center">
      <td class="desc-col footer-column">
        <p class="mt-1 mb-1">PDRB Nonmigas</p>
      </td>
      <template v-for="yr in props.yearsToRender" :key="'q-' + yr">
        <template v-for="(node, index) in quarters">
          <td :id="'adhb_total-nonmigas-' + node.label" class="total-cell">
            <!-- {{ getPDRBNonMigas(node.label, yr) }} -->
            {{
              formatNumberGerman(
                tableModel.footer["PDRB-NonMigas"]?.[String(yr)]?.q?.[node - 1] ?? 0,
                0,
                9
              )
            }}
          </td>
        </template>
        <!-- <td class="total-cell">{{ getSumPDRB("PDRB-NonMigas", yr) }}</td> -->
        <td class="total-cell">
          {{
            formatNumberGerman(
              tableModel.footer["PDRB-NonMigas"]?.[String(yr)]?.total ?? 0,
              0,
              9
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
    default: new Date().getFullYear(),
  },
});
// const dataHere = ref(props.dataContents);
const dataHere = ref(props.dataByYears);
const isNow = computed(() => String(props.onDemandType || "").includes("_now"));
const pack = (yr) => dataHere.value[yr] ?? {};
const seriesOfData = (yr) => {
  const p = pack(yr);
  return isNow.value ? p.dataContents ?? [] : p.dataBefore ?? [];
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
// const quarters = [{ label: "1" }, { label: "2" }, { label: "3" }, { label: "4" }];
// #region Section: GET_DATA
const getData = (subsectors, quarter, yr) => {
  // const theData = dataHere.value.find((x) => {
  //   return x.quarter == quarter && x.subsector_id == subsectors;
  // });
  const theData = seriesOfData(yr).find((x) => {
    return x.quarter == quarter && x.subsector_id == subsectors;
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
const lvlOne = ref({});
const getSumLvlOne = (value, quarter, yr) => {
  // Get all subsector IDs related to the given sector_id (value)
  let subsectorIds = props.subsectors
    .filter((x) => x.sector_id == value)
    .map((x) => x.id);
  // Get all matching data where quarter matches and subsector_id is in the subsector list

  // const filteredData = dataHere.value.filter(
  //   (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  // );
  const filteredData = seriesOfData(yr).filter(
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
const getSumLvlTwo = (value, quarter, yr) => {
  let subsectorIds = props.subsectors
    .filter((x) => x.sector.category_id == value)
    .map((x) => x.id);
  // const filteredData = dataHere.value.filter(
  //   (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  // );
  const filteredData = seriesOfData(yr).filter(
    (x) => x.quarter == quarter && subsectorIds.includes(Number(x.subsector_id))
  );
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlTwo.value[value]) lvlTwo.value[value] = {};
  lvlTwo.value[value][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumTotalFromVal = (value, yr) => {
  // const filteredData = dataHere.value.filter((x) => x.subsector_id == value);
  const filteredData = seriesOfData(yr).filter((x) => x.subsector_id == value);
  // Sum the values from the filtered data
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  // console.log(result);
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumRowCat = (value, yr) => {
  if (!lvlTwo.value[value]) return 0; // If no data, return 0

  // Get all quarter sums for this category
  let totalSum = Object.values(lvlTwo.value[value]).reduce(
    (sum, quarterSum) => sum + quarterSum,
    0
  );

  let formattedResult = formatNumberGerman(totalSum);
  return formattedResult;
};

const getSumRowSector = (value, yr) => {
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
const getPDRB = (quarter, yr) => {
  // const filteredData = dataHere.value.filter((x) => x.quarter == quarter);
  const filteredData = seriesOfData(yr).filter((x) => x.quarter == quarter);
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB"]) lvlPDRB.value["PDRB"] = {};
  lvlPDRB.value["PDRB"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getPDRBNonMigas = (quarter, yr) => {
  // const filteredData = dataHere.value.filter(
  //   (x) => x.quarter == quarter && ![10, 15].includes(Number(x.subsector_id))
  // );
  const filteredData = seriesOfData(yr).filter(
    (x) => x.quarter == quarter && ![10, 15].includes(Number(x.subsector_id))
  );
  const result = filteredData.reduce((sum, item) => sum + Number(item[props.type]), 0);
  if (!lvlPDRB.value["PDRB-NonMigas"]) lvlPDRB.value["PDRB-NonMigas"] = {};
  lvlPDRB.value["PDRB-NonMigas"][quarter] = result;
  let formattedResult = formatNumberGerman(result);
  return formattedResult;
};

const getSumPDRB = (pdrb, yr) => {
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

//dogger
// const toNum = (v) => {
//   if (v == null || v === "") return 0;
//   return Number(String(v).replaceAll(".", "").replaceAll(",", ".")) || 0;
// };

const idx = computed(() => {
  const out = {};
  for (const yr of props.yearsToRender) {
    const y = String(yr);
    out[y] = {};
    for (const row of seriesOfData(y)) {
      const sid = Number(row.subsector_id);
      const q = String(row.quarter);
      const val = row[props.type];
      (out[y][sid] ||= {})[q] = val;
    }
  }
  return out;
});
const quarters = ["1", "2", "3", "4"];

const tableModel = computed(() => {
  const years = props.yearsToRender.map(String);

  // 1) mapping keanggotaan subsector per sector/category
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

  // 2) isi baris subsector: rowKey = `sub-${id}`
  for (const sid of allSubsectorIds) {
    const key = `sub-${sid}`;
    model.rows[key] = {};
    for (const y of years) {
      const q = quarters.map((qq) => idx.value?.[y]?.[sid]?.[qq] ?? 0);
      model.rows[key][y] = { q, total: q.reduce((a, b) => a + b, 0) };
    }
  }

  // 3) baris sector: rowKey = `sec-${sectorId}`
  for (const [sectorId, ids] of Object.entries(subsectorsBySector)) {
    const key = `sec-${sectorId}`;
    model.rows[key] = {};
    for (const y of years) model.rows[key][y] = sumIds(y, ids);
  }

  // 4) baris category: rowKey = `cat-${catId}`
  for (const [catId, ids] of Object.entries(subsectorsByCategory)) {
    const key = `cat-${catId}`;
    model.rows[key] = {};
    for (const y of years) model.rows[key][y] = sumIds(y, ids);
  }

  // 5) footer PDRB (sum semua subsector)
  const allIds = Array.from(allSubsectorIds);
  model.footer["PDRB"] = {};
  for (const y of years) model.footer["PDRB"][y] = sumIds(y, allIds);

  // 6) footer PDRB Nonmigas (exclude subsector 10 & 15 seperti fungsi kamu)
  const nonmigasIds = allIds.filter((id) => ![10, 15].includes(Number(id)));
  model.footer["PDRB-NonMigas"] = {};
  for (const y of years) model.footer["PDRB-NonMigas"][y] = sumIds(y, nonmigasIds);

  return model;
});
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
