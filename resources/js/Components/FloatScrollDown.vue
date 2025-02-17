<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const showScrollButton = ref(false);
const isAtBottom = ref(false);

const handleScroll = () => {
  const scrollPosition = window.innerHeight + window.scrollY;
  const documentHeight = document.documentElement.scrollHeight;
  const atBottom = scrollPosition >= documentHeight;

  showScrollButton.value = !atBottom || window.scrollY > 200; // Show when scrolling
  isAtBottom.value = atBottom; // Check if at bottom
};

const scrollToTarget = () => {
  window.scrollTo({
    top: isAtBottom.value ? 0 : document.documentElement.scrollHeight,
    behavior: "smooth",
  });
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
  <button
    v-if="showScrollButton"
    @click="scrollToTarget"
    class="fixed bottom-5 right-5 bg-color text-white p-3 rounded-full shadow-lg transition-all"
  >
    <template v-if="isAtBottom">
      <font-awesome-icon icon="fa-solid fa-chevron-up" />
    </template>
    <template v-else>
      <font-awesome-icon icon="fa-solid fa-chevron-down" />
    </template>
  </button>
</template>
<style scoped>
.bg-color {
  background-color: #175676;
}

.bg-color:hover {
  background-color: #103e55;
}
</style>
