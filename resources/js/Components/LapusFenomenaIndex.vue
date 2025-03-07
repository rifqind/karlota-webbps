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
            <span class="badge badge-info">...</span>
          </td>
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
            <span class="badge badge-info">...</span>
          </td>
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
            <span class="badge badge-info">...</span>
          </td>
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
            <span class="badge badge-info">...</span>
          </td>
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
            <span class="badge badge-info">...</span>
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
    if (theIndex !== -1) {
      let text = thisData.value[theIndex][type];
      return text;
    }
  }
};
const hiddenText = (value) => {
  if (value.length > 200) {
    return value.substring(0, 200);
  } else return value;
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
  min-width: 400px;
  /* overflow: hidden; */
  /* text-overflow: ellipsis; */
  /* white-space: nowrap; */
}
tbody tr td {
  padding: 0.25rem;
}
</style>
