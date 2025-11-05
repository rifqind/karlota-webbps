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
            <label for="pdrb">NIP Lama (9 digit)</label>
            <input
              v-model="form.nip_lama"
              placeholder="Isi NIP lama dengan benar untuk bisa login dengan SSO"
              class="input-fordone w-full"
              type="text"
            />
          </div>
          <div v-if="form.errors.email" class="mb-3 text-red-500">
            {{ form.errors.email }}
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb">Password<span class="text-danger">*</span></label>
            <div class="relative">
              <input
                :type="isShowPassword['password'] ? 'text' : 'password'"
                placeholder="Boleh dikosongkan jika tidak ingin ganti password"
                v-model="form.password"
                class="input-fordone w-full pr-10"
              />
              <button
                type="button"
                @click="showPassword('password')"
                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                tabindex="-1"
              >
                <font-awesome-icon
                  v-if="!isShowPassword['password']"
                  icon="fa-solid fa-eye"
                />
                <font-awesome-icon
                  v-if="isShowPassword['password']"
                  icon="fa-solid fa-eye-slash"
                />
              </button>
            </div>
          </div>
          <div class="mb-3 space-y-2">
            <label for="pdrb"
              >Konfirmasi Password<span class="text-danger">*</span></label
            >
            <div class="relative">
              <input
                :type="isShowPassword['konfirmasi'] ? 'text' : 'password'"
                placeholder="Boleh dikosongkan jika tidak ingin ganti password"
                v-model="form.password_confirmation"
                class="input-fordone w-full pr-10"
              />
              <button
                type="button"
                @click="showPassword('konfirmasi')"
                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                tabindex="-1"
              >
                <font-awesome-icon
                  v-if="!isShowPassword['konfirmasi']"
                  icon="fa-solid fa-eye"
                />
                <font-awesome-icon
                  v-if="isShowPassword['konfirmasi']"
                  icon="fa-solid fa-eye-slash"
                />
              </button>
            </div>
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
  nip_lama: page.props.user.nip_lama,
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
const isShowPassword = ref({
  password: false,
  konfirmasi: false,
});
const showPassword = (key) => {
  isShowPassword.value[key] = !isShowPassword.value[key];
};
</script>

<style scoped></style>
