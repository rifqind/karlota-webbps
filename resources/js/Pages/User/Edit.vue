<template>
  <Head title="Edit Akun" />
  <GeneralLayout>
    <FlashFetch :notifications="notifications" />
    <div class="container px-[7.5px] mr-auto ml-auto">
      <div class="bg-white shadow-md mb-2 rounded-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between py-3 px-4 border-b card-header">
          <label class="text-xl">Edit Profil Akun</label>
        </div>
        <div class="p-5">
          <div class="mb-3 space-y-2">
            <label for="pdrb">Username<span class="text-danger">*</span></label>
            <input
              v-model="form.name"
              placeholder="Isikan Username Akun"
              class="input-fordone w-full"
              type="text"
            />
          </div>
          <div v-if="form.errors.name" class="mb-3 text-red-500">
            {{ form.errors.name }}
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb">Email<span class="text-danger">*</span></label>
            <input
              v-model="form.email"
              placeholder="Isikan Email"
              class="input-fordone w-full"
              type="email"
            />
          </div>
          <div v-if="form.errors.email" class="mb-3 text-red-500">
            {{ form.errors.email }}
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb">Password<span class="text-danger">*</span></label>
            <input
              type="password"
              placeholder="Boleh dikosongkan jika tidak ingin ganti password"
              v-model="form.password"
              class="input-fordone w-full"
            />
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb"
              >Konfirmasi Password<span class="text-danger">*</span></label
            >
            <input
              type="password"
              placeholder="Boleh dikosongkan jika tidak ingin ganti password"
              v-model="form.password_confirmation"
              class="input-fordone w-full"
            />
          </div>
          <div class="flex items-center space-x-2 justify-end">
            <div
              class="btn-success-fordone ml-auto w-[110px] text-center"
              @click.prevent="submit"
            >
              <font-awesome-icon icon="fa-solid fa-save" />
              Simpan
            </div>
          </div>
        </div>
      </div>
    </div>
  </GeneralLayout>
</template>

<script setup>
import FlashFetch from "@/Components/FlashFetch.vue";
import GeneralLayout from "@/Layouts/GeneralLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const page = usePage();
const form = useForm({
  _token: null,
  id: page.props.user.id,
  name: page.props.user.name,
  email: page.props.user.email,
  password: null,
  password_confirmation: null,
});
const notifications = ref([]);
const showNotification = (notification) => {
  notifications.value = notification;
  notifications.value.forEach((_, index) => {
    setTimeout(() => {
      notifications.value.shift(); // Remove the first notification
    }, (index + 1) * 1200); // Delay based on index
  });
};
const submit = async () => {
  const response = await axios.get(route("token"));
  form._token = response.data;
  if (form.processing) return;
  form.post("/user/edit", {
    onSuccess: (response) => {
      showNotification(response.props.notification);
    },
  });
};
</script>

<style scoped></style>
