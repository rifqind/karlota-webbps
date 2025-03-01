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
          <td v-for="node in fenomenaValue">
            <textarea
              class="w-full input-fordone"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node
                )
              "
            />
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
          <td v-for="node in fenomenaValue">
            <textarea
              class="w-full input-fordone"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node
                )
              "
            />
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
          <td v-for="node in fenomenaValue">
            <textarea
              class="w-full input-fordone"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node
                )
              "
            />
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
          <td v-for="node in fenomenaValue">
            <textarea
              class="w-full input-fordone"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node
                )
              "
            />
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
          <td v-for="node in fenomenaValue">
            <input
              type="text"
              class="w-full input-fordone font-bold"
              @input="
                (event) => {
                  handleInput(
                    event,
                    nodeSubsectors.sector.category_id,
                    nodeSubsectors.sector.id,
                    nodeSubsectors.id,
                    node
                  );
                }
              "
              :value="
                getData(
                  nodeSubsectors.sector.category_id,
                  nodeSubsectors.sector.id,
                  nodeSubsectors.id,
                  node
                )
              "
            />
          </td>
        </tr>
      </template>
    </template>
  </tbody>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";

const props = defineProps({
  subsectors: {
    type: Object,
    required: true,
  },
  dataContents: {
    type: Object,
    required: true,
  },
  keyData: {
    type: Object,
    required: true,
  },
});
const fenomenaValue = ref(["qtoq", "yony", "implisit"]);
const dataHere = ref(props.dataContents);
const dataContentHere = ref({});
watch(
  () => props.dataContents,
  (value) => {
    dataHere.value = value;
    dataContentHere.value.forEach((element) => {
      const theIndex = dataHere.value.findIndex((x) => {
        return (
          x.category_id == element.category_id &&
          x.sector_id == element.sector_id &&
          x.subsector_id == element.subsector_id
        );
      });
      if (theIndex !== -1) {
        element.qtoq = dataHere.value[theIndex].qtoq ?? null;
        element.yony = dataHere.value[theIndex].yony ?? null;
        element.implisit = dataHere.value[theIndex].implisit ?? null;
      }
    });
  }
);
onMounted(() => {
  let tempData = [];
  props.keyData.category.forEach((category) => {
    props.keyData.sector.forEach((sector) => {
      props.keyData.subsector.forEach((subsector) => {
        let data = {
          category_id: category,
          sector_id: sector,
          subsector_id: subsector,
          qtoq: null,
          yony: null,
          implisit: null,
        };
        tempData.push(data);
      });
    });
  });
  dataContentHere.value = tempData;
});
const getData = (category_id, sector_id, subsector_id, type) => {
  if (Object.keys(dataContentHere.value).length > 0) {
    const theData = dataContentHere.value.find((x) => {
      return (
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id
      );
    });
    if (theData) {
      return (
        theData[type] + category_id + "-" + sector_id + "-" + subsector_id + "-" + type
      );
    }
  }
  return category_id + "-" + sector_id + "-" + subsector_id + "-" + type;
};
const handleInput = (event, category_id, sector_id, subsector_id, type) => {
  let value = event.target.value;
  if (Object.keys(dataHere.value).length > 0) {
    const theIndex = dataHere.value.findIndex((x) => {
      return (
        x.category_id == category_id &&
        x.sector_id == sector_id &&
        x.subsector_id == subsector_id
      );
    });
    if (theIndex !== -1) dataHere.value[theIndex][type] = value;
  }
  const hereIndex = dataContentHere.value.findIndex((x) => {
    return (
      x.category_id == category_id &&
      x.sector_id == sector_id &&
      x.subsector_id == subsector_id
    );
  });
  if (hereIndex !== -1) dataContentHere.value[hereIndex][type] = value;
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

.input-fordone {
  text-align: left;
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
