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
          <td v-for="(node, idx) in regions" :key="idx">
            {{
              getData(
                node.id,
                quarter,
                nodeSubsectors.sector.category_id,
                null,
                null,
                type
              )
            }}
            <span
              v-if="
                isHidden(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  null,
                  null,
                  type
                )
              "
              @click="
                showText(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  null,
                  null,
                  type
                )
              "
              class="badge badge-info"
            >
              <font-awesome-icon icon="fa-solid fa-chevron-down"
            /></span>
            <span
              v-if="
                !isHidden(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  null,
                  null,
                  type
                )
              "
              @click="
                showText(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  null,
                  null,
                  type
                )
              "
              class="badge badge-info"
            >
              <font-awesome-icon icon="fa-solid fa-chevron-up"
            /></span>
          </td>
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
          <td v-for="(node, idx) in regions" :key="idx">
            {{
              getData(
                node.id,
                quarter,
                nodeSubsectors.sector.category_id,
                nodeSubsectors.sector_id,
                null,
                type
              )
            }}
            <span
              v-if="
                isHidden(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  null,
                  type
                )
              "
              @click="
                showText(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  null,
                  type
                )
              "
              class="badge badge-info"
            >
              <font-awesome-icon icon="fa-solid fa-chevron-down"
            /></span>
            <span
              v-if="
                !isHidden(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  null,
                  type
                )
              "
              @click="
                showText(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  null,
                  type
                )
              "
              class="badge badge-info"
            >
              <font-awesome-icon icon="fa-solid fa-chevron-up"
            /></span>
          </td>
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
          <td v-for="(node, idx) in regions" :key="idx">
            {{
              getData(
                node.id,
                quarter,
                nodeSubsectors.sector.category_id,
                nodeSubsectors.sector_id,
                nodeSubsectors.id,
                type
              )
            }}
            <span
              v-if="
                isHidden(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  nodeSubsectors.id,
                  type
                )
              "
              @click="
                showText(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  nodeSubsectors.id,
                  type
                )
              "
              class="badge badge-info"
            >
              <font-awesome-icon icon="fa-solid fa-chevron-down"
            /></span>
            <span
              v-if="
                !isHidden(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  nodeSubsectors.id,
                  type
                )
              "
              @click="
                showText(
                  node.id,
                  quarter,
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector_id,
                  nodeSubsectors.id,
                  type
                )
              "
              class="badge badge-info"
            >
              <font-awesome-icon icon="fa-solid fa-chevron-up"
            /></span>
          </td>
        </tr>
      </template>
    </template>
  </tbody>
</template>
<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  subsectors: {
    type: Object,
    required: true,
  },
  regions: {
    type: Object,
    required: true,
  },
  dataContents: {
    type: Array,
    required: false,
    default: [],
  },
  quarter: {
    type: String,
    required: false,
    default: "4",
  },
  type: {
    type: String,
    required: false,
    default: "yony",
  },
});
const thisData = ref(props.dataContents);
const shortData = ref(props.dataContents);
watch(
  () => props.dataContents,
  (value) => {
    // Deep copy to avoid modifying the original `props.dataContents`
    thisData.value = JSON.parse(JSON.stringify(value));
    shortData.value = JSON.parse(JSON.stringify(value));

    shortData.value.map((element, index) => ({
      ...element,
      short_qtoq: null,
      short_yony: null,
      short_implisit: null,
      qtoq: element.qtoq ? hiddenText(element.qtoq, index, "qtoq") : element.qtoq,
      yony: element.yony ? hiddenText(element.yony, index, "yony") : element.yony,
      implisit: element.implisit
        ? hiddenText(element.implisit, index, "implisit")
        : element.implisit,
    }));
  }
);
const getData = (id, quarter, category_id, sector_id, subsector_id, type) => {
  if (shortData.value.length > 0) {
    const theIndex = shortData.value.findIndex((x) => {
      return (
        x.region_id == id &&
        x.quarter == quarter &&
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id
      );
    });
    if (theIndex != -1) {
      let text = shortData.value[theIndex][type];
      return text;
    }
  }
};
const isHidden = (region_id, quarter, category_id, sector_id, subsector_id, type) => {
  if (shortData.value.length > 0) {
    const theIndex = shortData.value.findIndex((x) => {
      return (
        x.region_id == region_id &&
        x.quarter == quarter &&
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id
      );
    });
    if (theIndex != -1) {
      return shortData.value[theIndex]["short_" + type];
    }
  }
};
const showText = (region_id, quarter, category_id, sector_id, subsector_id, type) => {
  const theIndex = thisData.value.findIndex((x) => {
    return (
      x.region_id == region_id &&
      x.quarter == quarter &&
      x.category_id == category_id &&
      x.sector_id == sector_id &&
      x.subsector_id == subsector_id
    );
  });
  if (theIndex != -1) {
    if (shortData.value[theIndex]["short_" + type] == true) {
      shortData.value[theIndex][type] = thisData.value[theIndex][type];
      shortData.value[theIndex]["short_" + type] = false;
    } else if (shortData.value[theIndex]["short_" + type] == false) {
      shortData.value[theIndex][type] = hiddenText(
        shortData.value[theIndex][type],
        theIndex
      );
      shortData.value[theIndex]["short_" + type] = true;
    }
  }
};
const hiddenText = (value, index, type) => {
  let isShort = value.length > 200;
  let text = value.substring(0, 200);
  shortData.value[index]["short_" + type] = isShort;
  shortData.value[index][type] = isShort ? text : value;

  return isShort ? text : value;
};
const prepareDownload = () => {
  shortData.value.forEach((element, index) => {
    element.qtoq = thisData.value[index].qtoq;
    element.yony = thisData.value[index].yony;
    element.implisit = thisData.value[index].implisit;
    element.short_qtoq = true;
    element.short_yony = true;
    element.short_implisit = true;
  });
};
defineExpose({
  prepareDownload,
});
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
  text-align: justify;
  min-width: 300px;
}
tbody tr td {
  padding: 0.25rem;
}
</style>
