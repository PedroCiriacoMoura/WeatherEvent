<script setup lang="ts">
import { computed, ref, useId, type Component } from 'vue'
import { CircleAlert, Eye, EyeOff } from '@lucide/vue'
import { useI18n } from 'vue-i18n'

import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<{
    label: string
    type?: 'text' | 'email' | 'password'
    error?: string
    hint?: string
    icon?: Component
    autocomplete?: string
    placeholder?: string
    name?: string
    required?: boolean
    disabled?: boolean
  }>(),
  { type: 'text' },
)

const emit = defineEmits<{ blur: [] }>()
const model = defineModel<string>({ default: '' })

const { t } = useI18n()

const fieldId = useId()
const errorId = `${fieldId}-error`
const hintId = `${fieldId}-hint`

const showPassword = ref(false)
const isPassword = computed(() => props.type === 'password')
const inputType = computed(() =>
  isPassword.value ? (showPassword.value ? 'text' : 'password') : props.type,
)

const describedBy = computed(() => {
  const ids: string[] = []
  if (props.hint) ids.push(hintId)
  if (props.error) ids.push(errorId)
  return ids.length ? ids.join(' ') : undefined
})
</script>

<template>
  <div class="grid gap-2">
    <Label :for="fieldId">
      {{ label }}
      <span v-if="required" class="ml-0.5 text-destructive" aria-hidden="true">*</span>
    </Label>

    <div class="relative">
      <component
        :is="icon"
        v-if="icon"
        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
        aria-hidden="true"
      />
      <Input
        :id="fieldId"
        v-model="model"
        :type="inputType"
        :name="name"
        :autocomplete="autocomplete"
        :placeholder="placeholder"
        :disabled="disabled"
        :aria-invalid="error ? 'true' : undefined"
        :aria-describedby="describedBy"
        :class="cn(icon && 'pl-9', isPassword && 'pr-10')"
        @blur="emit('blur')"
      />
      <button
        v-if="isPassword"
        type="button"
        tabindex="-1"
        class="absolute top-0 right-0 flex h-full w-10 items-center justify-center rounded-r-md text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        :aria-label="showPassword ? t('auth.password.hide') : t('auth.password.show')"
        @click="showPassword = !showPassword"
      >
        <component :is="showPassword ? EyeOff : Eye" class="size-4" />
      </button>
    </div>

    <p v-if="hint && !error" :id="hintId" class="text-xs text-muted-foreground">{{ hint }}</p>

    <Transition name="field-error">
      <p
        v-if="error"
        :id="errorId"
        role="alert"
        class="flex items-center gap-1.5 text-xs font-medium text-destructive"
      >
        <CircleAlert class="size-3.5 shrink-0" />
        <span>{{ error }}</span>
      </p>
    </Transition>

    <slot name="meta" />
  </div>
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
