<script setup lang="ts">
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { LoaderCircle, Lock, Mail } from '@lucide/vue'

import AuthLayout from '@/components/auth/AuthLayout.vue'
import FormError from '@/components/auth/FormError.vue'
import TextField from '@/components/auth/TextField.vue'
import { Button } from '@/components/ui/button'
import { toast } from '@/components/ui/sonner'
import { useForm } from '@/composables/useForm'
import { parseApiError } from '@/lib/api-error'
import { email as emailRule, required } from '@/lib/validators'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const { values, errors, touched, isSubmitting, formError, handleBlur, submit } = useForm({
  initialValues: { email: '', password: '' },
  rules: {
    email: [required(), emailRule()],
    password: [required()],
  },
  onSubmit: async (input) => {
    await auth.login(input)
    toast.success(t('auth.toast.welcomeBack', { name: auth.user?.name ?? '' }))
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/profile'
    await router.push(redirect)
  },
})

async function onSubmit(): Promise<void> {
  try {
    await submit()
  } catch (error) {
    const parsed = parseApiError(error)
    if (parsed.status === 422) {
      formError.value = t('auth.toast.invalidCredentials')
      toast.error(t('auth.toast.invalidCredentials'))
    } else if (parsed.status === 429) {
      formError.value = t('auth.toast.tooManyAttempts')
      toast.error(t('auth.toast.tooManyAttempts'))
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
    <template #title>{{ t('auth.login.title') }}</template>
    <template #description>{{ t('auth.login.subtitle') }}</template>

    <form class="grid gap-5" novalidate @submit.prevent="onSubmit">
      <Transition name="field-error">
        <FormError v-if="formError" :message="formError" />
      </Transition>

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
        autocomplete="current-password"
        :placeholder="t('auth.placeholders.password')"
        :error="touched.password ? errors.password : undefined"
        required
        @blur="handleBlur('password')"
      />

      <Button type="submit" class="mt-1 w-full" :disabled="isSubmitting">
        <LoaderCircle v-if="isSubmitting" class="size-4 animate-spin" />
        {{ isSubmitting ? t('auth.login.submitting') : t('auth.login.submit') }}
      </Button>
    </form>

    <template #footer>
      <p class="text-center text-sm text-muted-foreground">
        {{ t('auth.login.noAccount') }}
        <RouterLink
          to="/register"
          class="font-medium text-foreground underline-offset-4 transition-colors hover:underline"
        >
          {{ t('auth.login.createOne') }}
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
