<template>
  <nav class="bg-white flex h-[56px] border-b justify-between items-center p-4">
    <!-- Left navbar links -->
    <div class="flex items-center">
      <a @click="triggerSidebar" class="nav-link" href="#"
        ><font-awesome-icon icon="fas fa-bars"
      /></a>
    </div>

    <!-- Right navbar links -->
    <div class="relative ml-auto">
      <!-- <button class="nav-item dropdown"> -->
      <div class="nav-link cursor-pointer" @click="toggleDropdown">
        {{ page.props.auth.user.name }}
      </div>
      <div
        class="absolute right-0 mt-2 z-[1400] w-48 bg-white border border-gray-200 rounded shadow-lg z-10"
        v-if="logoutDropdown"
      >
        <!-- <div @click="editProfile" class="dropdown-item px-2 py-2 cursor-pointer">
          <div class="flex items-center justify-start">
            <font-awesome class="w-1/12" icon="fa-user" />
            <span class="ml-2"> Profile </span>
          </div>
        </div> -->
        <div @click.prevent="submit" class="dropdown-item px-2 py-2 cursor-pointer">
          <div class="flex items-center justify-start">
            <font-awesome-icon class="w-1/12" icon="fa-sign-out-alt" />
            <span class="ml-2"> Logout </span>
          </div>
        </div>
      </div>
      <!-- </button> -->
    </div>
  </nav>
</template>

<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";

const page = usePage();
const logoutDropdown = ref(false);
const toggleDropdown = function () {
  logoutDropdown.value = !logoutDropdown.value;
};
const form = useForm({ _token: "" });
const submit = async function () {
  const response = await axios.get(route("token"));
  form._token = response.data;
  form.post(route("logout"));
};
const props = defineProps({
  isSidebarVisible: {
    type: Boolean,
    required: true,
    default: true,
  },
});
const emit = defineEmits(["update:triggerSidebar"]);
const triggerSidebar = () => {
  emit("update:triggerSidebar", !props.isSidebarVisible);
};
</script>

<style scoped>
.dropdown-item:hover {
  background-color: #3d3b8e;
  color: whitesmoke;
}
</style>
