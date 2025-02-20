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
                :value="getData(key, nodeRegion.region)"
                @input="
                  (event) => {
                    debounceHandleInput(event, key, nodeRegion.region);
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
    default: "4",
  },
});
const emits = defineEmits(["update:updateDataOnDemand"]);
const adjustmentVal = ref([]);
const dataHere = ref(props.dataContents);
watch(
  () => props.dataContents,
  (value) => {
    dataHere.value = value;
  }
);
watch(adjustmentVal.value, (value) => {
  emits("update:updateDataOnDemand", { data: value, quarter: props.quarterCap });
});
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
const handleInput = (event, key, region) => {
  let value = event.target.value;
  value = String(value).replaceAll(".", "").replace(",", ".");
  const theIndex = adjustmentVal.value.findIndex((x) => x.region == region);
  if (theIndex !== -1) adjustmentVal.value[theIndex].adjVal[key] = Number(value);
};
const debounceHandleInput = debounce((event, key, region) => {
  handleInput(event, key, region);
}, 700);
const setAdjustmentVal = (region, type, result) => {
  const theIndex = adjustmentVal.value.findIndex((x) => {
    return x.region == region;
  });
  if (theIndex !== -1) adjustmentVal.value[theIndex].adjVal[type] = result;
};
// #endregion
const formatNumberGerman = (num, min = 2, max = 5) => {
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

tbody tr td:not(:nth-child(1)) {
  text-align: right;
}
input {
  text-align: right;
}
</style>
