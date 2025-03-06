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
watch(
  () => props.dataContents,
  (value) => {
    thisData.value = value;
  }
);
const getData = (id, quarter, category_id, sector_id, subsector_id, type) => {
  if (thisData.value.length > 0) {
    const theIndex = thisData.value.findIndex((x) => {
      return (
        x.region_id == id &&
        x.quarter == quarter &&
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id
      );
    });
    if (theIndex !== -1) return thisData.value[theIndex][type];
  }
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
  text-align: justify;
  min-width: 300px;
}
tbody tr td {
  padding: 0.25rem;
}
</style>
