<template>
  <tbody ref="tableRef">
    <template v-for="(nodeRegion, regIndex) in adjustmentVal">
      <tr>
        <td :class="regIndex < 4 ? 'font-bold' : ''" class="fixed-column">
          {{ showLabel(nodeRegion.region) }}
        </td>
        <template v-for="(key, index) in Object.keys(nodeRegion.adjVal)" :key="index">
          <template v-if="key == 'adhb_adjust' || key == 'adhk_adjust'">
            <td>
              <input
                type="text"
                :id="key + nodeRegion.region + quarterCap"
                :key="key + nodeRegion.region + quarterCap"
                :value="getData(key, nodeRegion.region)"
                @input="
                  (event) => {
                    debounceHandleInput(event, key, nodeRegion.region, quarterCap);
                  }
                "
                v-if="regIndex >= 4"
                class="input-fordone w-full"
              />
            </td>
          </template>
          <template v-else>
            <td>{{ showThisVal(nodeRegion.region, key) }}</td>
          </template>
        </template>
      </tr>
    </template>
  </tbody>
</template>

<script setup>
import { debounce } from "@/debounce";
import { onMounted, ref, watch } from "vue";
const props = defineProps({
  regions: {
    type: Array,
    required: true,
  },
  dataContents: {
    type: Object,
    required: true,
  },
  quarterCap: {
    type: String,
    required: true,
  },
  dataAdjustment: {
    type: Object,
    required: true,
  },
  dataOnDemand: {
    type: Object,
    required: true,
  },
  dataBefore: {
    type: Object,
    required: true,
  },
});
const emits = defineEmits(["update:updateDataOnDemand", "update:updateDataContents"]);
const adjustmentVal = ref(props.dataAdjustment);
const dataHere = ref(props.dataContents);
const dataHereBefore = ref(props.dataBefore);
watch(
  () => props.dataContents,
  (value) => {
    dataHere.value = value;
  }
);
watch(
  () => props.dataBefore,
  (value) => {
    dataHereBefore.value = value;
  }
);
watch(
  () => props.dataAdjustment,
  (value) => {
    adjustmentVal.value = value;
  },
  { deep: true }
);
watch(
  adjustmentVal,
  (value) => {
    emits("update:updateDataOnDemand", { data: value, quarter: props.quarterCap });
  },
  { deep: true }
);
const createAdjVal = (region) => ({
  region,
  adjVal: {
    adhb_initial: null,
    adhb_adjust: null,
    adhb_berjalan: null,
    adhk_initial: null,
    adhk_adjust: null,
    adhk_berjalan: null,
    qtoq_initial: null,
    qtoq_berjalan: null,
    yony_initial: null,
    yony_berjalan: null,
    ctoc_initial: null,
    ctoc_berjalan: null,
    lajuimpqtoq_initial: null,
    lajuimpqtoq_berjalan: null,
    sogyony_initial: null,
    sogyony_berjalan: null,
    kontribusi_initial: null,
    kontribusi_berjalan: null,
  },
});

onMounted(() => {
  let arrayVal = [];
  props.regions.forEach((element, index) => {
    if (index == 0) {
      arrayVal = [
        createAdjVal(element.value),
        createAdjVal("Total Kabupaten/Kota"),
        createAdjVal("Selisih"),
        createAdjVal("Diskrepansi"),
      ];
    } else {
      arrayVal.push(createAdjVal(element.value));
    }
  });
  adjustmentVal.value = arrayVal; // Assign to reactive state
});

// #region Section: Show The Value
const showLabel = (region) => {
  let regionId = Number(region);
  if (isNaN(regionId)) {
    return region;
  } else {
    const region = props.regions.find((x) => {
      return x.value == regionId;
    });
    return region.label;
  }
};
const showThisVal = (region, type) => {
  const theData = dataHere.value.find((x) => {
    return x.region_id == region && x.quarter == props.quarterCap;
  });
  if (type == "adhb_initial") {
    if (region == "Total Kabupaten/Kota") {
      let formattedResult = getTotalKabkot("adhb", region, type);
      return formattedResult;
    }
    if (region == "Selisih") return getSelisih(type);
    if (region == "Diskrepansi") return getDiskrepansi(type);
    if (theData) {
      let formattedResult;
      formattedResult =
        theData["adhb"] == "" || theData["adhb"] == null ? null : theData["adhb"];
      setAdjustmentVal(region, type, Number(formattedResult));
      return formatNumberGerman(Number(formattedResult), 0, 9);
    }
  } else if (type == "adhk_initial") {
    if (region == "Total Kabupaten/Kota") return getTotalKabkot("adhk", region, type);
    if (region == "Selisih") return getSelisih(type);
    if (region == "Diskrepansi") return getDiskrepansi(type);
    if (theData) {
      let formattedResult;
      formattedResult =
        theData["adhk"] == "" || theData["adhk"] == null ? null : theData["adhk"];
      setAdjustmentVal(region, type, Number(formattedResult));
      return formatNumberGerman(Number(formattedResult), 0, 9);
    }
  } else if (type == "adhb_berjalan" || type == "adhk_berjalan") {
    if (!isNaN(Number(region))) return getBerjalan(region, type);
    if (region == "Total Kabupaten/Kota") return getTotalKabkotBerjalan(type);
    if (region == "Selisih") return getSelisih(type);
    if (region == "Diskrepansi") return getDiskrepansi(type);
  } else if (type == "qtoq_initial") {
    if (!isNaN(Number(region))) return gQtoQ(region, "adhk_initial", type);
    if (region == "Total Kabupaten/Kota") return gQtoQ(region, "adhk_initial", type);
  } else if (type == "qtoq_berjalan") {
    if (!isNaN(Number(region))) return gQtoQ(region, "adhk_berjalan", type);
    if (region == "Total Kabupaten/Kota") return gQtoQ(region, "adhk_berjalan", type);
  } else if (type == "yony_initial") {
    if (!isNaN(Number(region))) return gYonY(region, "adhk_initial", type);
    if (region == "Total Kabupaten/Kota") return gYonY(region, "adhk_initial", type);
  } else if (type == "yony_berjalan") {
    if (!isNaN(Number(region))) return gYonY(region, "adhk_berjalan", type);
    if (region == "Total Kabupaten/Kota") return gYonY(region, "adhk_berjalan", type);
  } else if (type == "ctoc_initial") {
    if (!isNaN(Number(region))) return gCtoC(region, "adhk_initial", type);
    if (region == "Total Kabupaten/Kota") return gCtoC(region, "adhk_initial", type);
  } else if (type == "ctoc_berjalan") {
    if (!isNaN(Number(region))) return gCtoC(region, "adhk_berjalan", type);
    if (region == "Total Kabupaten/Kota") return gCtoC(region, "adhk_berjalan", type);
  } else if (type == "lajuimpqtoq_initial") {
    if (!isNaN(Number(region)))
      return gIQtoQ(region, "adhb_initial", "adhk_initial", type);
    if (region == "Total Kabupaten/Kota")
      return gIQtoQ(region, "adhb_initial", "adhk_initial", type);
  } else if (type == "lajuimpqtoq_berjalan") {
    if (!isNaN(Number(region)))
      return gIQtoQ(region, "adhb_berjalan", "adhk_berjalan", type);
    if (region == "Total Kabupaten/Kota")
      return gIQtoQ(region, "adhb_berjalan", "adhk_berjalan", type);
  } else if (type == "kontribusi_initial") {
    if (!isNaN(Number(region))) return getKontribusi("adhb_initial", region, type);
    if (region == "Total Kabupaten/Kota")
      return getKontribusi("adhb_initial", region, type);
  } else if (type == "kontribusi_berjalan") {
    if (!isNaN(Number(region))) return getKontribusi("adhb_berjalan", region, type);
    if (region == "Total Kabupaten/Kota")
      return getKontribusi("adhb_berjalan", region, type);
  }
};
const getTotalKabkot = (typeOfData, region, type) => {
  const filteredData = dataHere.value.filter(
    (x) => x.quarter == props.quarterCap && x.region_id !== 1
  );
  const result = filteredData.reduce((sum, item) => sum + Number(item[typeOfData]), 0);
  const theIndex = adjustmentVal.value.findIndex((x) => {
    return x.region == region;
  });
  if (theIndex !== -1) adjustmentVal.value[theIndex].adjVal[type] = result;
  return formatNumberGerman(result, 0, 9);
};
const getTotalKabkotBerjalan = (type) => {
  const filteredData = adjustmentVal.value.filter(
    (x) => x.region !== 1 && !isNaN(Number(x.region))
  );
  const result = filteredData.reduce((sum, item) => sum + Number(item.adjVal[type]), 0);
  setAdjustmentVal("Total Kabupaten/Kota", type, result);
  return formatNumberGerman(result, 0, 9);
};
const getSelisih = (type) => {
  const provVal = adjustmentVal.value.find((x) => {
    return x.region == 1;
  });
  const totalKabkot = adjustmentVal.value.find((x) => {
    return x.region == "Total Kabupaten/Kota";
  });
  let selisih = provVal.adjVal[type] - totalKabkot.adjVal[type];
  return formatNumberGerman(selisih, 0, 9);
};
const getDiskrepansi = (type) => {
  const provVal = adjustmentVal.value.find((x) => {
    return x.region == 1;
  });
  const selisih = String(getSelisih(type)).replaceAll(".", "").replaceAll(",", ".");
  let diskrepansi = (Number(selisih) / provVal.adjVal[type]) * 100;
  return formatNumberGerman(diskrepansi, 2, 4);
};
const getBerjalan = (region, type) => {
  const data = adjustmentVal.value.find((x) => {
    return x.region == region;
  });
  if (type == "adhb_berjalan") {
    const berjalan = data.adjVal["adhb_initial"] + data.adjVal["adhb_adjust"];
    setAdjustmentVal(region, type, berjalan);
    return formatNumberGerman(berjalan, 0, 9);
  } else if (type == "adhk_berjalan") {
    const berjalan = data.adjVal["adhk_initial"] + data.adjVal["adhk_adjust"];
    setAdjustmentVal(region, type, berjalan);
    return formatNumberGerman(berjalan, 0, 9);
  }
};
const getData = (key, region) => {
  const theData = adjustmentVal.value.find((x) => {
    return x.region == region;
  });
  if (theData) {
    let formattedResult;
    formattedResult =
      theData.adjVal[key] == "" || theData.adjVal[key] == null
        ? null
        : formatNumberGerman(Number(theData.adjVal[key]), 0, 9);
    return formattedResult;
  }
};
const handleInput = (event, key, region, quarter) => {
  let value = event.target.value;
  value = String(value).replaceAll(".", "").replace(",", ".");
  const theIndex = adjustmentVal.value.findIndex((x) => x.region == region);
  const dataContentIndex = dataHere.value.findIndex(
    (x) => x.region_id == region && x.quarter == quarter
  );
  if (theIndex !== -1) adjustmentVal.value[theIndex].adjVal[key] = Number(value);
  let type;
  if (key == "adhb_adjust") type = "adj_adhb";
  else if (key == "adhk_adjust") type = "adj_adhk";
  if (dataContentIndex !== -1) {
    dataHere.value[dataContentIndex][type] = Number(value);
  }
};
const debounceHandleInput = debounce((event, key, region, quarter) => {
  handleInput(event, key, region, quarter);
}, 700);
const setAdjustmentVal = (region, type, result) => {
  const theIndex = adjustmentVal.value.findIndex((x) => {
    return x.region == region;
  });
  if (theIndex !== -1) adjustmentVal.value[theIndex].adjVal[type] = result;
};
watch(dataHere.value, (value) => {
  emits("update:updateDataContents", value);
});
// #endregion
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
// #region Section: Calculate
const gQtoQ = (region, type, typeAdjust) => {
  let quarter = Number(props.quarterCap);
  let current = null,
    previous = null,
    dividend = 0,
    divisor = 0;
  if (props.dataOnDemand[quarter] && Array.isArray(props.dataOnDemand[quarter])) {
    current = props.dataOnDemand[quarter].find((x) => x.region == region) ?? null;
  }
  // Get the dividend safely
  dividend = Number(current?.adjVal?.[type]) ?? 0;
  if (quarter - 1 !== 0) {
    if (
      props.dataOnDemand[quarter - 1] &&
      Array.isArray(props.dataOnDemand[quarter - 1])
    ) {
      previous = props.dataOnDemand[quarter - 1].find((x) => x.region == region) ?? null;
    }
    divisor = Number(previous?.adjVal?.[type]) ?? 0;
  } else {
    if (!isNaN(Number(region))) {
      previous =
        dataHereBefore.value.find((x) => x.quarter == "4" && x.region_id == region) ??
        null;
      divisor = Number(previous?.adhk) ?? 0;
    } else {
      const filteredData = dataHereBefore.value.filter(
        (x) => x.quarter == "4" && x.region_id !== 1
      );
      divisor = filteredData.reduce((sum, item) => sum + (Number(item?.adhk) ?? 0), 0);
    }
  }
  let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
  setAdjustmentVal(region, typeAdjust, growth);
  return formatNumberGerman(growth.toFixed(4), 2, 4);
};
const gYonY = (region, type, typeAdjust) => {
  let quarter = Number(props.quarterCap);
  let current = null,
    previous = null,
    dividend = 0,
    divisor = 0;
  if (props.dataOnDemand[quarter] && Array.isArray(props.dataOnDemand[quarter])) {
    current = props.dataOnDemand[quarter].find((x) => x.region == region) ?? null;
  }
  dividend = Number(current?.adjVal?.[type]) ?? 0;
  if (!isNaN(Number(region))) {
    previous =
      dataHereBefore.value.find(
        (x) => x.quarter == props.quarterCap && x.region_id == region
      ) ?? null;
    divisor = Number(previous?.adhk) ?? 0;
  } else {
    const filteredData = dataHereBefore.value.filter(
      (x) => x.quarter == props.quarterCap && x.region_id !== 1
    );
    divisor = filteredData.reduce((sum, item) => sum + (Number(item?.adhk) ?? 0), 0);
  }
  let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
  setAdjustmentVal(region, typeAdjust, growth);
  return formatNumberGerman(growth.toFixed(4), 2, 4);
};
const gCtoC = (region, type, typeAdjust) => {
  let quarter = Number(props.quarterCap);
  let current = null,
    previous = null,
    dividend = 0,
    divisor = 0;
  for (let index = 1; index <= quarter; index++) {
    if (props.dataOnDemand[index] && Array.isArray(props.dataOnDemand[index])) {
      current = props.dataOnDemand[index].find((x) => x.region == region) ?? null;
    }
    dividend += Number(current?.adjVal?.[type]) ?? 0;
    if (!isNaN(Number(region))) {
      previous =
        dataHereBefore.value.find(
          (x) => x.quarter == String(index) && x.region_id == region
        ) ?? null;
      divisor += Number(previous?.adhk) ?? 0;
    } else {
      const filteredData = dataHereBefore.value.filter(
        (x) => x.quarter == String(index) && x.region_id !== 1
      );
      divisor += filteredData.reduce((sum, item) => sum + (Number(item?.adhk) ?? 0), 0);
    }
  }
  let growth = divisor !== 0 && dividend !== 0 ? (dividend / divisor) * 100 - 100 : 0;
  setAdjustmentVal(region, typeAdjust, growth);
  return formatNumberGerman(growth.toFixed(4), 2, 4);
};
const gIQtoQ = (region, adhb, adhk, type) => {
  let quarter = Number(props.quarterCap);
  let current = null,
    previous = null,
    adhbCurrent = null,
    adhkCurrent = null,
    adhbPrevious = null,
    adhkPrevious = null,
    dividend = 0,
    divisor = 0,
    idxCurrent = 0,
    idxPrevious = 0;
  if (props.dataOnDemand[quarter] && Array.isArray(props.dataOnDemand[quarter])) {
    current = props.dataOnDemand[quarter].find((x) => x.region == region) ?? null;
  }
  adhbCurrent = Number(current?.adjVal?.[adhb]) ?? 0;
  adhkCurrent = Number(current?.adjVal?.[adhk]) ?? 0;
  idxCurrent =
    adhbCurrent !== 0 && adhkCurrent !== 0 ? (adhbCurrent / adhkCurrent) * 100 : 0;
  if (quarter - 1 !== 0) {
    if (
      props.dataOnDemand[quarter - 1] &&
      Array.isArray(props.dataOnDemand[quarter - 1])
    ) {
      previous = props.dataOnDemand[quarter - 1].find((x) => x.region == region) ?? null;
    }
    adhbPrevious = Number(previous?.adjVal?.[adhb]) ?? 0;
    adhkPrevious = Number(previous?.adjVal?.[adhk]) ?? 0;
    idxPrevious =
      adhbPrevious !== 0 && adhkPrevious !== 0 ? (adhbPrevious / adhkPrevious) * 100 : 0;
  } else {
    if (!isNaN(Number(region))) {
      previous =
        dataHereBefore.value.find((x) => x.quarter == "4" && x.region_id == region) ??
        null;
      adhbPrevious = Number(previous?.adhb) ?? 0;
      adhkPrevious = Number(previous?.adhk) ?? 0;
    } else {
      const filteredData = dataHereBefore.value.filter(
        (x) => x.quarter == "4" && x.region_id !== 1
      );
      adhbPrevious = filteredData.reduce(
        (sum, item) => sum + (Number(item?.adhb) ?? 0),
        0
      );
      adhkPrevious = filteredData.reduce(
        (sum, item) => sum + (Number(item?.adhk) ?? 0),
        0
      );
    }
    idxPrevious =
      adhbPrevious !== 0 && adhkPrevious !== 0 ? (adhbPrevious / adhkPrevious) * 100 : 0;
  }
  let growth =
    idxPrevious !== 0 && idxCurrent !== 0 ? (idxCurrent / idxPrevious) * 100 - 100 : 0;
  setAdjustmentVal(region, type, growth);
  return formatNumberGerman(growth.toFixed(4), 2, 4);
};
const getKontribusi = (type, region, typeAdjust) => {
  if (region == 1) return;
  const totalKabkot = adjustmentVal.value.find((x) => {
    return x.region == "Total Kabupaten/Kota";
  });
  const dividend = adjustmentVal.value.find((x) => {
    return x.region == region;
  });
  let kontribusi = (dividend.adjVal[type] / totalKabkot.adjVal[type]) * 100;
  setAdjustmentVal(region, typeAdjust, kontribusi);
  return formatNumberGerman(kontribusi, 2, 4);
};
// #endregion
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

tbody tr td:not(:nth-child(1)) {
  text-align: right;
}

input {
  text-align: right;
}
</style>
