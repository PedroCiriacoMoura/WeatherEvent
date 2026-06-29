<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue'
import { Check, ChevronDown } from '@lucide/vue'
import {
  SelectContent,
  SelectIcon,
  SelectItem,
  SelectItemIndicator,
  SelectItemText,
  SelectPortal,
  SelectRoot,
  SelectTrigger,
  SelectValue,
  SelectViewport,
} from 'reka-ui'
import { cn } from '@/lib/utils'
import type { SelectOption } from '.'

const props = withDefaults(
  defineProps<{
    options: SelectOption[]
    placeholder?: string
    disabled?: boolean
    id?: string
    ariaInvalid?: boolean
    class?: HTMLAttributes['class']
  }>(),
  { placeholder: '', disabled: false },
)

// reka-ui Select works with string values; we bridge to the option's original type.
const model = defineModel<string | number | null>({ default: null })

const stringModel = computed<string | undefined>({
  get: () => (model.value === null || model.value === undefined ? undefined : String(model.value)),
  set: (val) => {
    if (val === undefined) {
      model.value = null
      return
    }
    const match = props.options.find((option) => String(option.value) === val)
    model.value = match ? match.value : val
  },
})
</script>

<template>
  <SelectRoot v-model="stringModel" :disabled="disabled">
    <SelectTrigger
      :id="id"
      :aria-invalid="ariaInvalid ? 'true' : undefined"
      :class="
        cn(
          'flex h-10 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none data-[placeholder]:text-muted-foreground disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30',
          'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
          'aria-invalid:border-destructive aria-invalid:ring-[3px] aria-invalid:ring-destructive/20',
          props.class,
        )
      "
    >
      <SelectValue :placeholder="placeholder" />
      <SelectIcon as-child>
        <ChevronDown class="size-4 shrink-0 text-muted-foreground" />
      </SelectIcon>
    </SelectTrigger>

    <SelectPortal>
      <SelectContent
        position="popper"
        :side-offset="6"
        class="relative z-50 max-h-72 min-w-[var(--reka-select-trigger-width)] overflow-hidden rounded-lg border bg-popover p-1 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95 data-[state=closed]:zoom-out-95"
      >
        <SelectViewport class="p-0">
          <SelectItem
            v-for="option in options"
            :key="String(option.value)"
            :value="String(option.value)"
            class="relative flex w-full cursor-pointer items-center rounded-md py-1.5 pr-8 pl-2 text-sm outline-none transition-colors select-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[state=checked]:font-medium"
          >
            <SelectItemText>{{ option.label }}</SelectItemText>
            <SelectItemIndicator class="absolute right-2 inline-flex items-center">
              <Check class="size-4" />
            </SelectItemIndicator>
          </SelectItem>
        </SelectViewport>
      </SelectContent>
    </SelectPortal>
  </SelectRoot>
</template>
