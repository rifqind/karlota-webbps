<script setup>
import { onUpdated, ref } from "vue";

const props = defineProps({
  toggleFlash: {
    type: Boolean,
    default: false,
  },
  flashObject: {
    type: Object,
    required: false,
  },
  types: {
    type: String,
    default: "bg-green-500",
  },
});
const emit = defineEmits(["close"]);
const message = ref(null);
const typesFlash = ref("bg-green-500");
let timer = null;
const hideFlashMessage = function () {
  clearTimeout(timer);
  timer = setTimeout(() => {
    emit("close");
  }, 3000);
};
onUpdated(() => {
  if (props.toggleFlash == true) {
    if (props.flashObject.error) {
      typesFlash.value = "bg-red-700";
      message.value = props.flashObject.error;
    } else {
      message.value = props.flashObject.message;
      typesFlash.value = "bg-green-700";
    }
    hideFlashMessage();
  }
});
</script>
<template>
  <div
    class="p-4 rounded shadow-md mb-4 text-white left-[50%] top-[20px] max-w-[75%] fixed z-[1399]"
    :class="typesFlash"
    v-if="toggleFlash"
    role="alert"
  >
    {{ message }}
  </div>
</template>
<style scoped>
div {
  transform: translateX(-50%);
}
</style>
