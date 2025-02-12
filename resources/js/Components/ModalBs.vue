<script setup>
import { computed } from "vue";
import { ref } from "vue";

const props = defineProps({
  title: {
    type: String,
  },
  modalSize: {
    type: String,
    default: null,
  },
  ModalStatus: {
    type: Boolean,
    default: false,
  },
  inputFunction: {
    type: Function,
    default: null,
  },
  functionLabel: {
    type: String,
    default: "Simpan",
  },
  inputClass: {
    type: String,
    default: "btn-success-fordone",
  },
  modalPosition: {
    type: String,
    default: "items-center",
  },
  // toggleModalClose: {
  //     type: Function,
  //     require: true,
  // }
});
</script>
<template>
  <div
    v-if="ModalStatus"
    class="modalbs fixed inset-0 z-[1300] bg-black bg-opacity-50 flex justify-center transition-opacity"
    :class="modalPosition"
    tabindex="-1"
  >
    <div :class="['bg-white rounded-lg shadow-lg w-full sm:w-auto']" role="document">
      <div class="modal-content">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
          <h5 class="font-black text-lg" id="modalBsTitle">{{ title }}</h5>
          <button
            type="button"
            class="text-gray-500 hover:text-gray-700"
            @click="$emit('close')"
            aria-label="Close"
          >
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div :class="['p-4', modalSize]" id="thisModalContent">
          <slot name="modalBody" />
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-end space-x-2">
          <button
            type="button"
            class="bg-gray-500 text-white px-3 py-1 rounded text-sm"
            @click="$emit('close')"
          >
            Tutup
          </button>
          <slot name="modalFunction" />
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(1.1);
}
</style>
