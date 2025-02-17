<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  notifications: {
    type: Array,
    default: () => [],
  },
});
const emit = defineEmits(["remove"]);
</script>
<template>
  <div class="fixed top-5 right-5 z-[1399] space-y-2">
    <transition-group name="slide">
      <div
        v-for="(notif, index) in notifications"
        :key="index"
        class="p-2 rounded shadow-md text-white max-w-[500px]"
        :class="notif.type === 'error' ? 'bg-red-700' : 'bg-green-700'"
      >
        {{ notif.message }}
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.5s ease;
}
.slide-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}
.slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
