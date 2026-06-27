<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { LoaderCircle, Lock, Mail, User } from '@lucide/vue'

import AuthLayout from '@/components/auth/AuthLayout.vue'
import FormError from '@/components/auth/FormError.vue'
import PasswordStrengthMeter from '@/components/auth/PasswordStrengthMeter.vue'
import TextField from '@/components/auth/TextField.vue'
import { Button } from '@/components/ui/button'
import { toast } from '@/components/ui/sonner'
import { useForm } from '@/composables/useForm'
import { parseApiError } from '@/lib/api-error'
import { email as emailRule, matches, maxLength, minLength, required } from '@/lib/validators'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

const {
  values,
  errors,
  touched,
  isSubmitting,
  formError,
  handleBlur,
  setFieldError,
  setServerErrors,
  submit,
} = useForm({
  initialValues: { name: '', email: '', password: '', password_confirmation: '' },
  rules: {
    name: [required(), maxLength(255)],
    email: [required(), emailRule()],
    password: [required(), minLength(8)],
    password_confirmation: [required(), matches('password')],
  },
  onSubmit: async (input) => {
    await auth.register(input)
    toast.success(t('auth.toast.accountCreated'))
    await router.push('/profile')
  },
})

async function onSubmit(): Promise<void> {
  try {
    await submit()
  } catch (error) {
    const parsed = parseApiError(error)
    if (parsed.status === 422 && parsed.fieldErrors.email) {
      setFieldError('email', t('auth.validation.emailTaken'))
    } else if (parsed.status === 422 && Object.keys(parsed.fieldErrors).length > 0) {
      setServerErrors(parsed.fieldErrors)
    } else {
      const message = parsed.isNetwork ? t('auth.toast.networkError') : t('auth.toast.genericError')
      formError.value = message
      toast.error(message)
    }
  }
}
</script>

<template>
  <AuthLayout>
    <template #title>{{ t('auth.register.title') }}</template>
    <template #description>{{ t('auth.register.subtitle') }}</template>

    <form class="grid gap-5" novalidate @submit.prevent="onSubmit">
      <Transition name="field-error">
        <FormError v-if="formError" :message="formError" />
      </Transition>

      <TextField
        v-model="values.name"
        :label="t('auth.fields.name')"
        type="text"
        :icon="User"
        autocomplete="name"
        :placeholder="t('auth.placeholders.name')"
        :error="touched.name ? errors.name : undefined"
        required
        @blur="handleBlur('name')"
      />

      <TextField
        v-model="values.email"
        :label="t('auth.fields.email')"
        type="email"
        :icon="Mail"
        autocomplete="email"
        :placeholder="t('auth.placeholders.email')"
        :error="touched.email ? errors.email : undefined"
        required
        @blur="handleBlur('email')"
      />

      <TextField
        v-model="values.password"
        :label="t('auth.fields.password')"
        type="password"
        :icon="Lock"
        autocomplete="new-password"
        :placeholder="t('auth.placeholders.password')"
        :error="touched.password ? errors.password : undefined"
        :hint="t('auth.register.passwordHint')"
        required
        @blur="handleBlur('password')"
      >
        <template #meta>
          <PasswordStrengthMeter :password="values.password" />
        </template>
      </TextField>

      <TextField
        v-model="values.password_confirmation"
        :label="t('auth.fields.confirmPassword')"
        type="password"
        :icon="Lock"
        autocomplete="new-password"
        :placeholder="t('auth.placeholders.confirmPassword')"
        :error="touched.password_confirmation ? errors.password_confirmation : undefined"
        required
        @blur="handleBlur('password_confirmation')"
      />

      <Button type="submit" class="mt-1 w-full" :disabled="isSubmitting">
        <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" />
        {{ isSubmitting ? t('auth.register.submitting') : t('auth.register.submit') }}
      </Button>
    </form>

    <template #footer>
      <p class="text-center text-sm text-muted-foreground">
        {{ t('auth.register.haveAccount') }}
        <RouterLink
          to="/login"
          class="font-medium text-foreground underline-offset-4 transition-colors hover:underline"
        >
          {{ t('auth.register.signIn') }}
        </RouterLink>
      </p>
    </template>
  </AuthLayout>
</template>

<style scoped>
.field-error-enter-active,
.field-error-leave-active {
  transition:
    opacity 0.18s ease,
    transform 0.18s ease;
}
.field-error-enter-from,
.field-error-leave-to {
  opacity: 0;
  transform: translateY(-2px);
}
</style>
