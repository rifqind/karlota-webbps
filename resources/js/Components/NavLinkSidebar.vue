<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
  navIcon: {
    type: String,
  },
  role: {
    type: Boolean,
    default: true,
  },
  href: {
    type: String,
    default: "#",
  },
  currentRoute: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <li v-if="role" class="nav-item mb-1">
    <Link
      :href="href"
      class="nav-link w-full relative flex items-center rounded"
      :class="{ active: currentRoute }"
    >
      <font-awesome-icon class="nav-icon w-1/12" :icon="navIcon" />
      <p class="ml-2">
        <slot />
      </p>
    </Link>
  </li>
</template>
<style scoped>
.nav-link {
  padding: 0.5rem 1rem;
}
.nav-link.active {
  background-color: #175676;
  color: #fff;
}
.sidebar-dark-success .nav-sidebar > .nav-item > .nav-link.active,
.sidebar-light-success .nav-sidebar > .nav-item > .nav-link.active {
  background-color: #175676;
  color: #fff;
}
</style>
