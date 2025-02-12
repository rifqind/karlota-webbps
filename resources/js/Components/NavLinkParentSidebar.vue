<script setup>
import { ref } from "vue";

const props = defineProps({
  navIcon: {
    type: String,
  },
  role: {
    type: Boolean,
    default: true,
  },
  menuOpen: {
    type: Boolean,
    default: false,
  },
  toggleMenuOpen: {
    type: Function,
    required: true,
  },
  params: {
    type: String,
    default: null,
  },
});
</script>

<template>
  <li v-if="role" class="nav-item mb-1">
    <div
      class="nav-link w-full relative flex items-center rounded"
      :class="{ 'parent-active': menuOpen }"
      @click="
        () => {
          toggleMenuOpen(params);
        }
      "
    >
      <font-awesome-icon class="nav-icon w-1/12" :icon="navIcon" />
      <p class="ml-2">
        <slot name="label" />
      </p>
      <font-awesome-icon v-if="!menuOpen" icon="fas fa-angle-left" class="ml-auto" />
      <font-awesome-icon v-if="menuOpen" icon="fas fa-angle-down" class="ml-auto" />
    </div>
  </li>
  <Transition name="dropdown">
    <ul class="nav text-gray-700 ml-2 border-l-2 border-gray-100" v-if="menuOpen">
      <slot name="content" />
    </ul>
  </Transition>
</template>

<style scoped>
.nav-link {
  cursor: pointer;
  padding: 0.5rem 1rem;
}
.nav-link.active {
  background-color: #3d3b8e;
  color: #fff;
}
.nav-link.parent-active {
  background-color: rgb(236, 236, 236);
  color: black;
}
.sidebar-dark-success .nav-sidebar > .nav-item > .nav-link.active,
.sidebar-light-success .nav-sidebar > .nav-item > .nav-link.active {
  background-color: #3d3b8e;
  color: #fff;
}
</style>
