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
                :key="key + nodeRegion.region + 't'"
                disabled
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
const emits = defineEmits(["update:updateDataOnDemand"]);
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
    emits("update:updateDataOnDemand", { data: value, quarter: "t" });
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
  const theData = dataHere.value.filter((x) => x.region_id == region);
  if (type == "adhb_initial") {
    if (region == "Total Kabupaten/Kota") {
      let formattedResult = getTotalKabkot("adhb", region, type);
      return formattedResult;
    }
    if (region == "Selisih") return getSelisih(type);
    if (region == "Diskrepansi") return getDiskrepansi(type);
    if (theData) {
      let formattedResult;
      formattedResult = theData.reduce((sum, item) => sum + Number(item?.adhb) ?? 0, 0);
      setAdjustmentVal(region, type, formattedResult);
      return formatNumberGerman(formattedResult, 0, 9);
    }
  } else if (type == "adhk_initial") {
    if (region == "Total Kabupaten/Kota") return getTotalKabkot("adhk", region, type);
    if (region == "Selisih") return getSelisih(type);
    if (region == "Diskrepansi") return getDiskrepansi(type);
    if (theData) {
      let formattedResult;
      formattedResult = theData.reduce((sum, item) => sum + Number(item?.adhk) ?? 0, 0);
      setAdjustmentVal(region, type, formattedResult);
      return formatNumberGerman(formattedResult, 0, 9);
    }
  } else if (type == "adhb_berjalan" || type == "adhk_berjalan") {
    if (!isNaN(Number(region))) return getBerjalan(region, type);
    if (region == "Total Kabupaten/Kota") return getTotalKabkotBerjalan(type);
    if (region == "Selisih") return getSelisih(type);
    if (region == "Diskrepansi") return getDiskrepansi(type);
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
  const filteredData = dataHere.value.filter((x) => x.region_id !== 1);
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
  let adhb = 0,
    adhk = 0;
  for (let index = 1; index <= 4; index++) {
    const data = props.dataOnDemand[index].find((x) => x.region == region);
    if (type === "adhb_berjalan") {
      adhb += Number(data?.adjVal?.[type]) || 0; // Use || instead of ??
    } else if (type === "adhk_berjalan") {
      adhk += Number(data?.adjVal?.[type]) || 0;
    }
  }
  if (type == "adhb_berjalan") {
    setAdjustmentVal(region, type, adhb);
    return formatNumberGerman(adhb, 0, 9);
  } else if (type == "adhk_berjalan") {
    setAdjustmentVal(region, type, adhk);
    return formatNumberGerman(adhk, 0, 9);
  }
};
const setAdjustmentVal = (region, type, result) => {
  const theIndex = adjustmentVal.value.findIndex((x) => x.region == region);
  if (theIndex !== -1) {
    adjustmentVal.value[theIndex].adjVal[type] = result;
  }
};
// #endregion
const formatNumberGerman = (num, min = 2, max = 5) => {
  return new Intl.NumberFormat("de-DE", {
    minimumFractionDigits: min,
    maximumFractionDigits: max,
  }).format(num);
};
// #region Section: Calculate
const gYonY = (region, type, typeAdjust) => {
  let current = null,
    previous = null,
    dividend = 0,
    divisor = 0;
  for (let index = 1; index <= 4; index++) {
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
