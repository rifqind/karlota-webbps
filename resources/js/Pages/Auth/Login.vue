<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";

const page = usePage();
defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
  error: {
    required: false,
    default: null,
    type: String,
  },
});

const form = useForm({
  name: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post(route("login"), {
    onFinish: () => form.reset("password"),
  });
};
const ssoLogin = () => {
  let root = null;
  if (import.meta.env.MODE == "development") {
    root = "http://localhost:8000";
  } else if (import.meta.env.MODE == "production") {
    root = "https://karlota.web.bps.go.id";
  }
  window.location.href = root + "/sso-login";
};
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>
    <form @submit.prevent="submit">
      <div>
        <InputLabel for="name" value="Username" />

        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.name"
          required
          autofocus
          autocomplete="username"
          placeholder="Isikan Username"
        />

        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" value="Password" />

        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          required
          placeholder="Isikan Password"
          autocomplete="current-password"
        />

        <InputError class="mt-2" :message="form.errors.password" />
        <InputError class="mt-2" :message="form.errors.credentials" />
      </div>
      <div v-if="page.props.flash.error" class="text-red-500">
        {{ page.props.flash.error }}
      </div>
      <div class="mt-4 flex items-center justify-end">
        <PrimaryButton
          class="ms-4"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Login
        </PrimaryButton>
        <PrimaryButton
          class="ms-4"
          @click.prevent="ssoLogin"
          :class="{ 'opacity-25': form.processing }"
        >
          SSO Login
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
